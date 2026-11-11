<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Log;
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

    /** Fallback when neither the citizen charter nor the category sets required_days. */
    private const DEFAULT_REQUIRED_DAYS = 20;

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

        /** Both relations are eager-loaded because the form prints `classification`. */
        $document = Document::with(['category', 'citizencharter'])->where('control_no', $control_no)->first();
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
            $documents = Document::with(['category', 'citizencharter', 'logs' => function ($query) {
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
                'category' => $document->classification,
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
        /** Inclusive of both selected days — see App\Livewire\Report\DocumentStatus. */
        $rangeStart = \Carbon\Carbon::parse($startDate)->startOfDay();
        $rangeEnd = \Carbon\Carbon::parse($endDate)->addDay()->startOfDay();

        // All four columns come from the custody-window walk: the rate is exactly
        // Completed / Received, and Pending and Overdue are the still-open
        // receipts split by deadline, so Completed + Pending + Overdue = Received
        // (less any document another office took over).
        $windows = $this->completionWindows($rangeStart, $rangeEnd);
        $receivedByOffice = $windows['started'];
        $completedByOffice = $windows['finished'];
        $pendingByOffice = $windows['pending'];
        $overdueByOffice = $windows['overdue'];

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
     * A window still open when the document's trail ends, with the document
     * still "On Process" with that same office today, is Overdue if its
     * deadline (created date + required days, weekdays only) has passed and
     * Pending otherwise, so Completed + Pending + Overdue = Received.
     *
     * Mirrors DocumentStatus::walkCompletionWindows(); keep the two in step.
     */
    private function completionWindows($rangeStart, $rangeEnd): array
    {
        // Charter's required days, else category's, else the default; non-positive counts as unset.
        $requiredDays = 'case'
            . ' when citizen_charters.required_days > 0 then citizen_charters.required_days'
            . ' when categories.required_days > 0 then categories.required_days'
            . ' else ' . self::DEFAULT_REQUIRED_DAYS
            . ' end';

        $logs = DB::table('logs')
            ->leftJoin('documents', 'documents.id', '=', 'logs.document_id')
            ->leftJoin('citizen_charters', 'citizen_charters.id', '=', 'documents.citizen_charter_id')
            ->leftJoin('categories', 'categories.id', '=', 'documents.category_id')
            ->whereIn('logs.action_id', array_merge([self::ACTION_RECEIVED], self::ACTIONS_COMPLETED))
            ->where('logs.created_at', '>=', $rangeStart)
            ->orderBy('logs.document_id')
            ->orderBy('logs.created_at')
            ->orderBy('logs.id')
            ->select(
                'logs.document_id',
                'logs.assigned_to',
                'logs.action_id',
                'logs.created_at',
                'documents.status as document_status',
                'documents.assigned_to as document_holder',
                DB::raw('date(documents.created_at) as document_created_date'),
                DB::raw($requiredDays . ' as required_days')
            )
            ->cursor();

        $endAt = $rangeEnd->toDateTimeString();
        $today = \Carbon\Carbon::today();

        $started = [];
        $finished = [];
        $pending = [];
        $overdue = [];
        $currentDoc = null;
        $openWindow = null;
        $document = null;
        $pastDeadline = [];

        // Files the previous document's still-open window under Pending or Overdue if that office still holds it.
        $settle = function () use (&$openWindow, &$document, &$pending, &$overdue, &$pastDeadline, $today) {
            if ($openWindow === null
                || !$openWindow['in']
                || $document === null
                || $document['status'] !== 'On Process'
                || $document['holder'] !== $openWindow['office']) {
                return;
            }

            $key = $document['created_date'] . '|' . $document['required_days'];

            if (!isset($pastDeadline[$key])) {
                $dueDate = \Carbon\Carbon::parse($document['created_date'])->startOfDay()->addWeekdays($document['required_days']);

                // Signed working days to the deadline: >=0 still on time, <0 overdue.
                $pastDeadline[$key] = (int) $today->diffInWeekdays($dueDate, false) < 0;
            }

            $office = $openWindow['office'];

            if ($pastDeadline[$key]) {
                $overdue[$office] = ($overdue[$office] ?? 0) + 1;
            } else {
                $pending[$office] = ($pending[$office] ?? 0) + 1;
            }
        };

        foreach ($logs as $log) {
            $documentId = (int) $log->document_id;

            if ($documentId !== $currentDoc) {
                $settle();

                $currentDoc = $documentId;
                $openWindow = null;
                $document = [
                    'status' => $log->document_status,
                    'holder' => $log->document_holder === null ? null : (int) $log->document_holder,
                    'created_date' => $log->document_created_date,
                    'required_days' => (int) $log->required_days,
                ];
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

        // The last document's trail has no following row to trigger it.
        $settle();

        return [
            'started' => collect($started),
            'finished' => collect($finished),
            'pending' => collect($pending),
            'overdue' => collect($overdue),
        ];
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

        /**
         * The selected range, inclusive of both days the user picked, and shared by
         * every query below so the summary cards and the per-office table cannot
         * disagree. This matches the on-screen report in
         * App\Livewire\Report\ExternalDocuments, which filters on the date part of
         * created_at — previously the printed copy shifted the whole window forward
         * a day and quietly reported different totals than the screen it was
         * printed from.
         */
        $rangeStart = \Carbon\Carbon::parse($startDate)->startOfDay();
        $rangeEnd = \Carbon\Carbon::parse($endDate)->addDay()->startOfDay();

        // Generate overall statistics for external documents
        $reportData['overall'] = [
            'incoming' => Document::where('source', 'external')
                ->whereIn('status', ['For Receiving', 'Returned'])
                ->whereBetween('created_at', [$rangeStart, $rangeEnd])->count(),
            'pending' => Document::where('source', 'external')
                ->whereIn('status', ['On Process'])
                ->whereBetween('created_at', [$rangeStart, $rangeEnd])->count(),
            'processed' => Document::where('source', 'external')
                ->whereNull('bundle_id')
                ->whereHas('logs', function ($query) {
                    $query->whereIn('action_id', [3, 5]);
                })
                ->whereBetween('created_at', [$rangeStart, $rangeEnd])->count(),
        ];

        // Generate office-wise data for external documents. Pre-aggregate the
        // counts in three grouped queries instead of 3 count queries per office.
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
