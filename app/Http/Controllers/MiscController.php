<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class MiscController extends Controller
{
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
        $officeResponse = Http::get('http://192.168.100.162:8081/public/get-offices');

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
            $this->responseOffices = Http::get('http://192.168.100.162:8081/public/get-offices')->json();
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
        $office = $user['office']['officeName'];
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

        return view('livewire.partials.transmittal-form', compact('user', 'office', 'destination', 'document', 'barcodeImg'));
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
            $selectedItems = explode(',', $selectedItemsParam);
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
        
        // Default to last 30 days if no dates provided
        if (!$startDate || !$endDate) {
            $startDate = now()->subMonth(1)->format('Y-m-d');
            $endDate = now()->format('Y-m-d');
        }

        // Load offices data
        $this->mount();
        
        // Generate overall statistics
        $reportData['overall'] = [
            'incoming' => Document::whereIn('status', ['For Receiving', 'Returned'])
                ->whereBetween('created_at', [
                    \Carbon\Carbon::parse($startDate)->addDay(1),
                    \Carbon\Carbon::parse($endDate)->addDay(1)
                ])->count(),
            'pending' => Document::whereIn('status', ['On Process'])
                ->whereBetween('created_at', [
                    \Carbon\Carbon::parse($startDate)->addDay(1),
                    \Carbon\Carbon::parse($endDate)->addDay(1)
                ])->count(),
            'processed' => Log::whereIn('action_id', [3, 5])
                ->whereBetween('created_at', [
                    \Carbon\Carbon::parse($startDate)->addDay(1),
                    \Carbon\Carbon::parse($endDate)->addDay(1)
                ])->count(),
        ];

        // Generate office-wise data
        $reportData['offices'] = [];
        foreach ($this->offices as $office) {
            $incoming = Document::where('assigned_to', $office['id'])
                ->whereIn('status', ['For Receiving', 'Returned'])
                ->whereBetween('created_at', [
                    \Carbon\Carbon::parse($startDate)->addDay(1),
                    \Carbon\Carbon::parse($endDate)->addDay(1)
                ])->count();
            
            $pending = Document::where('assigned_to', $office['id'])
                ->whereIn('status', ['On Process'])
                ->whereBetween('created_at', [
                    \Carbon\Carbon::parse($startDate)->addDay(1),
                    \Carbon\Carbon::parse($endDate)->addDay(1)
                ])->count();
            
            $processed = Log::where('assigned_to', $office['id'])
                ->whereIn('action_id', [3, 5])
                ->whereBetween('created_at', [
                    \Carbon\Carbon::parse($startDate)->addDay(1),
                    \Carbon\Carbon::parse($endDate)->addDay(1)
                ])->count();

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

        return view('reports.document-status-print', compact('reportData', 'startDate', 'endDate'));
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

        // Generate office-wise data for external documents
        $reportData['offices'] = [];
        foreach ($this->offices as $office) {
            $incoming = Document::where('source', 'external')
                ->where('assigned_to', $office['id'])
                ->whereIn('status', ['For Receiving', 'Returned'])
                ->whereBetween('created_at', [
                    \Carbon\Carbon::parse($startDate)->addDay(1),
                    \Carbon\Carbon::parse($endDate)->addDay(1)
                ])->count();
            
            $pending = Document::where('source', 'external')
                ->where('assigned_to', $office['id'])
                ->whereIn('status', ['On Process'])
                ->whereBetween('created_at', [
                    \Carbon\Carbon::parse($startDate)->addDay(1),
                    \Carbon\Carbon::parse($endDate)->addDay(1)
                ])->count();
            
            $processed = Document::where('source', 'external')
                ->where('assigned_to', $office['id'])
                ->whereHas('logs', function ($query) {
                    $query->whereIn('action_id', [3, 5]);
                })
                ->whereBetween('created_at', [
                    \Carbon\Carbon::parse($startDate)->addDay(1),
                    \Carbon\Carbon::parse($endDate)->addDay(1)
                ])->count();

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
