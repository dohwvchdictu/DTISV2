<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Log;
use App\Support\BusinessDays;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class MiscController extends Controller
{
    /**
     * Action ids for the printed Status-of-Documents report. Kept in step with
     * App\Livewire\Report\DocumentStatus so screen and paper agree.
     */
    private const ACTION_RECEIVED = 1;
    private const ACTION_FORWARDED = 3;
    private const ACTION_CLOSED = 5;
    private const ACTIONS_COMPLETED = [self::ACTION_FORWARDED, self::ACTION_CLOSED];

    /** A pending document is overdue once it has sat this long at one office. */
    private const OVERDUE_AFTER_DAYS = 3;

    public $user = [];
    public $id;
    public $assigned_to;
    public $office;
    public $destination;
    public $offices = [];
    public $responseOffices;
    public $selected_office;

    public function mount()
    {
        /** User Information */
        $this->user = session('user');
        $this->office = $this->user['office']['id'];
        /** End User Information */

        $this->checkApiConnection();
    }

    public function checkApiConnection()
    {
        /** API */
        $officeResponse = Http::get(config('services.api.base_url') . 'public/get-offices');

        if(!$officeResponse->ok())
        {
            $this->offices = [];

            $this->alert('error', 'No response from API server. Check connection and try again.', [
                'position' => 'center',
                'toast' => true,
                'timer' => null,
                'showConfirmButton' => true,
                'confirmButtonText' => 'OK',
                'confirmButtonColor' => '#dc2626',
            ]);
            
            return false;
        }

        $this->response = $officeResponse->json();

        $this->offices = collect($this->response['officeList'] ?? [])
            ->sortBy('officeName')
            ->values()
            ->all();

        return true;
    }

    public function lookUpOffice($assigned_to)
    {
        $this->selected_office = $this->assigned_to ?? $assigned_to;

        // Ensure responseOffices is loaded
        if (!$this->responseOffices) {
            $this->responseOffices = Http::get(config('services.api.base_url') . 'public/get-offices')->json();
        }

        $result = array_filter($this->responseOffices['officeList'], function ($office) {
            return $office['id'] == $this->selected_office;
        });

        $findOffice = $result[$this->selected_office - 1];
        return $findOffice['officeName'];
    }

    public function printTransmittalForm($control_no)
    {
        /** User Information */
        $user = session('user');
        $office = $user['office']['officeName'] ?? '';
        /** End User Information */

        $document = Document::where('control_no', $control_no)->first();
        $log = Log::where('document_id', $document->id)->where('action_id', 7)->first();
        $this->destination = $log->assigned_to ?? null;
        $destination = $this->lookUpOffice($this->destination);

        // Make Barcode object of Code128 encoding.
        $barcode = (new \Picqer\Barcode\Types\TypeCode128())->getBarcode($control_no);

        // Output the barcode as HTML in the browser with a HTML Renderer
        $renderer = new \Picqer\Barcode\Renderers\HtmlRenderer();
        $barcodeImg = $renderer->render($barcode);

        $qrCode = QrCode::size(110)->generate(url('/document/qr-receive/' . $control_no));

        return view('livewire.partials.transmittal-form', compact('user', 'office', 'destination', 'document', 'barcodeImg', 'qrCode'));
    }

    public function filterOffice($id)
    {
        if (!isset($id)) {
            return '';
        }

        $this->id = $id;

        $result = array_filter($this->offices, function ($office) {
            return $office['id'] == $this->id;
        });

        $result = array_values($result); // reindex array

        if (!isset($result[0])) {
            return '';
        }
        $findOffice = $result[0];
        return $findOffice['officeCode'] ?? '';
    }    

    public function generateLogbook(Request $request)
    {
        $selectedItemsParam = $request->query('selected_items', '');

        $selectedItems = [];
        if (!empty($selectedItemsParam)) {
            $selectedItems = array_values(array_filter(
                array_map('intval', explode(',', $selectedItemsParam)),
                fn($v) => $v > 0
            ));
        }

        $documentsData = [];
        $documentsArray = [];
        $offices = [];

        $documents = [];
        if (!empty($selectedItems)) {
            $documents = Document::with(['category', 'logs' => function ($query) {
                $query->with(['action', 'user'])->orderBy('created_at', 'asc');
            }])
                ->whereIn('id', $selectedItems)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Load offices data
        $this->mount();

        // Merge documents with office information into documentsData
        foreach ($documents as $document) {
            $officeName = $this->filterOffice($document->assigned_to);
            
            $documentsData[] = [
                'document' => $document,
                'office_name' => $officeName,
                'assigned_to' => $document->assigned_to,
                'control_no' => $document->control_no,
                'subject' => $document->subject,
                'category' => $document->category->name ?? 'N/A',
                'created_at' => $document->created_at,
                'status' => $document->status,
                'logs' => $document->logs
            ];
        }

        // Group documents by assigned_to after processing all documents
        $documentsArray = collect($documentsData)->groupBy('assigned_to');

        return view('livewire.partials.logbook', compact('documentsArray', 'offices'));
    }

    public function printDocumentStatusReport(Request $request)
    {
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');
        
        // Default to the current month if no dates provided, matching the
        // on-screen report in App\Livewire\Report\DocumentStatus.
        if (!$startDate || !$endDate) {
            $startDate = now()->startOfMonth()->format('Y-m-d');
            $endDate = now()->format('Y-m-d');
        }

        // Load offices data
        $this->mount();

        // Pre-aggregate the counts in grouped queries instead of running several
        // count queries per office (~150 queries). Mirrors the on-screen report
        // in App\Livewire\Report\DocumentStatus; keep the two in step.
        $rangeStart = \Carbon\Carbon::parse($startDate)->addDay(1);
        $rangeEnd = \Carbon\Carbon::parse($endDate)->addDay(1);

        $pendingByOffice = Document::where('status', 'On Process')
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->selectRaw('assigned_to, COUNT(*) as aggregate')
            ->groupBy('assigned_to')
            ->pluck('aggregate', 'assigned_to');

        // Received and Completed both come from the custody-window walk rather
        // than two independent COUNT(*)s, so Completed can never exceed Received
        // and the printed rate is exactly Completed / Received.
        $windows = $this->completionWindows($rangeStart, $rangeEnd);
        $receivedByOffice = $windows['started'];
        $completedByOffice = $windows['finished'];

        $overdueByOffice = $this->overdueDocumentsByOffice($rangeStart, $rangeEnd);

        // Generate overall statistics
        $overallReceived = $receivedByOffice->sum();
        $overallCompleted = $completedByOffice->sum();

        $reportData['overall'] = [
            'pending' => $pendingByOffice->sum(),
            'received' => $overallReceived,
            'completed' => $overallCompleted,
            'overdue' => $overdueByOffice->sum(),
            'rate' => $this->completionRate($overallReceived, $overallCompleted),
        ];

        $reportData['offices'] = [];
        foreach ($this->offices as $office) {
            // Report lists active offices only; $this->offices stays unfiltered
            // because filterOffice() must still resolve deactivated offices.
            if (!($office['status'] ?? true)) {
                continue;
            }

            $received = $receivedByOffice[$office['id']] ?? 0;
            $completed = $completedByOffice[$office['id']] ?? 0;

            $reportData['offices'][] = [
                'office' => $office,
                'pending' => $pendingByOffice[$office['id']] ?? 0,
                'received' => $received,
                'completed' => $completed,
                'overdue' => $overdueByOffice[$office['id']] ?? 0,
                'rate' => $this->completionRate($received, $completed),
            ];
        }

        // Sort offices by name
        $reportData['offices'] = collect($reportData['offices'])->sortBy(function ($item) {
            return $item['office']['officeName'];
        })->values()->toArray();

        return view('reports.document-status-print', compact('reportData', 'startDate', 'endDate'));
    }

    /**
     * Share of the documents an office took in that it also finished. Both terms
     * come from completionWindows(), where each receipt is followed to its own
     * outcome, so the result cannot exceed 100%. Null when nothing arrived.
     * Mirrors DocumentStatus::completionRate().
     */
    private function completionRate($started, $finished)
    {
        if (!$started) {
            return null;
        }

        return ($finished / $started) * 100;
    }

    /**
     * Per-office custody windows for the period: a window opens on "Received"
     * and closes on the next "Forwarded" or "Closed". Only windows that opened
     * inside the period count, and one still counts as finished if it closed
     * after the period ended. No upper bound on the query for that reason.
     *
     * Mirrors DocumentStatus::walkCompletionWindows(); keep the two in step.
     */
    private function completionWindows($rangeStart, $rangeEnd): array
    {
        $logs = DB::table('logs')
            ->whereIn('action_id', array_merge([self::ACTION_RECEIVED], self::ACTIONS_COMPLETED))
            ->where('created_at', '>=', $rangeStart)
            ->orderBy('document_id')
            ->orderBy('created_at')
            ->orderBy('id')
            ->select('document_id', 'assigned_to', 'action_id', 'created_at')
            ->cursor();

        $endAt = $rangeEnd->toDateTimeString();

        $started = [];
        $finished = [];
        $currentDoc = null;
        $openWindow = null;

        foreach ($logs as $log) {
            $documentId = (int) $log->document_id;

            if ($documentId !== $currentDoc) {
                $currentDoc = $documentId;
                $openWindow = null;
            }

            if ((int) $log->action_id === self::ACTION_RECEIVED) {
                $officeId = (int) $log->assigned_to;

                // Repeat receipts by the holding office are batch re-scans.
                if ($openWindow !== null && $openWindow['office'] === $officeId) {
                    continue;
                }

                $inPeriod = $log->created_at < $endAt;
                $openWindow = ['office' => $officeId, 'in' => $inPeriod];

                if ($inPeriod) {
                    $started[$officeId] = ($started[$officeId] ?? 0) + 1;
                }

                continue;
            }

            if ($openWindow !== null) {
                if ($openWindow['in']) {
                    $finished[$openWindow['office']] = ($finished[$openWindow['office']] ?? 0) + 1;
                }

                $openWindow = null;
            }
        }

        return ['started' => collect($started), 'finished' => collect($finished)];
    }

    /**
     * Pending documents that have sat more than OVERDUE_AFTER_DAYS business days
     * at the office now holding them. A strict subset of the Pending column.
     * Mirrors DocumentStatus::overdueByOffice().
     */
    private function overdueDocumentsByOffice($rangeStart, $rangeEnd)
    {
        $receipts = DB::table('logs')
            ->join('documents', 'documents.id', '=', 'logs.document_id')
            ->where('documents.status', 'On Process')
            ->whereBetween('documents.created_at', [$rangeStart, $rangeEnd])
            ->where('logs.action_id', self::ACTION_RECEIVED)
            /** Only receipts logged by the office currently holding the document. */
            ->whereColumn('logs.assigned_to', 'documents.assigned_to')
            ->groupBy('logs.document_id', 'documents.assigned_to')
            ->selectRaw('logs.document_id, documents.assigned_to, MAX(logs.created_at) as received_at')
            ->cursor();

        $now = \Carbon\Carbon::now();
        $overdue = [];

        foreach ($receipts as $receipt) {
            if (BusinessDays::between($receipt->received_at, $now) <= self::OVERDUE_AFTER_DAYS) {
                continue;
            }

            $officeId = $receipt->assigned_to;
            $overdue[$officeId] = ($overdue[$officeId] ?? 0) + 1;
        }

        return collect($overdue);
    }

    public function printExternalDocumentsReport(Request $request)
    {
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');
        
        // Default to last 30 days if no dates provided
        if (!$startDate || !$endDate) {
            $startDate = now()->subMonth(1)->format('Y-m-d');
            $endDate = now()->format('Y-m-d');
        }

        // Load offices data
        $this->mount();
        
        // Generate overall statistics for external documents
        $reportData['overall'] = [
            'incoming' => Document::where('source', 'external')
                ->whereIn('status', ['For Receiving', 'Returned'])
                ->whereBetween('created_at', [
                    \Carbon\Carbon::parse($startDate)->addDay(1),
                    \Carbon\Carbon::parse($endDate)->addDay(1)
                ])->count(),
            'pending' => Document::where('source', 'external')
                ->whereIn('status', ['On Process'])
                ->whereBetween('created_at', [
                    \Carbon\Carbon::parse($startDate)->addDay(1),
                    \Carbon\Carbon::parse($endDate)->addDay(1)
                ])->count(),
            'processed' => Document::where('source', 'external')
                ->whereNull('bundle_id')
                ->whereHas('logs', function ($query) {
                    $query->whereIn('action_id', [3, 5]);
                })
                ->whereBetween('created_at', [
                    \Carbon\Carbon::parse($startDate)->addDay(1),
                    \Carbon\Carbon::parse($endDate)->addDay(1)
                ])->count(),
        ];

        // Generate office-wise data for external documents. Pre-aggregate the
        // counts in three grouped queries instead of 3 count queries per office.
        $rangeStart = \Carbon\Carbon::parse($startDate)->addDay(1);
        $rangeEnd = \Carbon\Carbon::parse($endDate)->addDay(1);

        $incomingByOffice = Document::where('source', 'external')
            ->whereIn('status', ['For Receiving', 'Returned'])
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->selectRaw('assigned_to, COUNT(*) as aggregate')
            ->groupBy('assigned_to')
            ->pluck('aggregate', 'assigned_to');

        $pendingByOffice = Document::where('source', 'external')
            ->where('status', 'On Process')
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->selectRaw('assigned_to, COUNT(*) as aggregate')
            ->groupBy('assigned_to')
            ->pluck('aggregate', 'assigned_to');

        $processedByOffice = Document::where('source', 'external')
            ->whereHas('logs', function ($query) {
                $query->whereIn('action_id', [3, 5]);
            })
            ->whereBetween('created_at', [$rangeStart, $rangeEnd])
            ->selectRaw('assigned_to, COUNT(*) as aggregate')
            ->groupBy('assigned_to')
            ->pluck('aggregate', 'assigned_to');

        $reportData['offices'] = [];
        foreach ($this->offices as $office) {
            // Report lists active offices only; $this->offices stays unfiltered
            // because filterOffice() must still resolve deactivated offices.
            if (!($office['status'] ?? true)) {
                continue;
            }

            $incoming = $incomingByOffice[$office['id']] ?? 0;
            $pending = $pendingByOffice[$office['id']] ?? 0;
            $processed = $processedByOffice[$office['id']] ?? 0;

            $total = $incoming + $pending + $processed;
            $percentage = $processed && $total > 0 ? ($processed / $total) * 100 : 0;

            $reportData['offices'][] = [
                'office' => $office,
                'incoming' => $incoming,
                'pending' => $pending,
                'processed' => $processed,
                'percentage' => $percentage
            ];
        }

        // Sort offices by name
        $reportData['offices'] = collect($reportData['offices'])->sortBy(function ($item) {
            return $item['office']['officeName'];
        })->values()->toArray();

        return view('reports.external-documents-print', compact('reportData', 'startDate', 'endDate'));
    }    
}
