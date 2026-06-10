<?php

namespace App\Livewire\Status;

use App\Models\Action;
use App\Models\Category;
use App\Models\Document;
use App\Models\Log;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;

class Pending extends Component
{
    use WithPagination;
    use LivewireAlert;

    #[Title('Pending Documents | Document Tracking Information System')]

    /** Constant Variables */
    public $offices = [];
    public $user = [];
    public $office;

    public $phrase = '';
    public $passphrase = '';

    /** API Responses */
    public $responseEmployees;
    public $responseOffices;
    public $employees = [];
    public $subEmployees = [];
    public $endorsedID;

    public $statement = [
        'approved' => 'Document has been approved.',
        'signed' => 'Document has been signed.',
        'initialed' => 'Document has been initialed.',
        'checked' => 'Document has been checked.',
        'processed' => 'Document has been processed.'
    ];

    /** Search & Filter Variables*/
    public $search = '';
    public $selectFilter = [];

    /** Multiple Selection */
    public $selected_item = [];
    public $selectAll = false;
    public $assignedTo;
    public $endorsedToPersonnel;
    public $endorsedToOtherPersonnel;
    public $selected_office;
    public $filterOfficeEmployees = [];

    /** Forward Variables */
    public int $document_id;
    public $remarks;
    public $attachments;

    /** Add Document Variables */
    public $pendings;
    public $documents_attached;
    public $parent_bundle;

    /** Filter Date Variables */
    public $startDate;
    public $endDate;

    /** Listeners for Livewire Alerts */
    protected $listeners = [
        'close',
        'forward',
        'endorse',
        'closeModal'
    ];

    public function mount()
    {
        /** User Information */
        $this->user = session('user');
        $this->office = $this->user['office']['id'];
        /** End User Information */

        /** Filter Records last 1 Quarter */
        $this->startDate = Carbon::now()->subQuarter(1)->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');

        // Check API connection and load data
        $this->checkApiConnection();
    }

    /**
     * Check API server connection and fetch employee and office data
     * Returns true if successful, false otherwise
     */
    private function checkApiConnection()
    {
        $employeeResponse = Http::get(config('services.api.base_url') . 'public/get-employees');
        $officeResponse = Http::get(config('services.api.base_url') . 'public/get-offices');

        if (!$employeeResponse->ok() || !$officeResponse->ok()) {
            $this->employees = [];
            $this->offices = [];
            $this->filterOfficeEmployees = [];
            $this->responseEmployees = null;
            $this->responseOffices = null;
            
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

        $this->responseEmployees = $employeeResponse->json();
        $this->responseOffices = $officeResponse->json();

        $this->employees = collect($this->responseEmployees['employeesList'] ?? [])
            ->sortBy('lastName')
            ->values()
            ->all();

        $this->offices = collect($this->responseOffices['officeList'] ?? [])
            ->sortBy('officeName')
            ->values()
            ->all();

        $this->filterOfficeEmployees = array_filter($this->employees, function ($office) {
            return isset($office['office']['id']) && $office['office']['id'] == $this->user['office']['id'];
        });

        return true;
    }

    /** Multiple Receive */
    public function updatedSelectAll($value)
    {
        $this->selected_item = $value ? Document::whereNull('bundle_id')
            ->when($this->search, function ($query) {
                // Properly scope the OR conditions to avoid query issues
                $query->where(function ($q) {
                    $q->where('control_no', 'like', '%' . $this->search . '%')
                        ->orWhere('subject', 'like', '%' . $this->search . '%');
                });
            })
            ->where('assigned_to', $this->office)
            ->where('status', 'On Process')
            ->pluck('id')->toArray() : [];
    }

    public function updatedAssignedTo($value)
    {
        $this->selected_office = $value;

        $this->subEmployees = array_filter($this->employees, function ($office) {
            return isset($office['office']['id']) && $office['office']['id'] == $this->selected_office;
        });
    }

    public function lookUpOffice($assigned_to)
    {

        $this->assignedTo = $assigned_to;

        if ($this->responseOffices === null || !isset($this->responseOffices['officeList'])) {
            $this->alert('error', 'No response from API server. Check connection and try again.', [
                'position' => 'center',
                'toast' => true,
                'timer' => null,
                'showConfirmButton' => true,
                'confirmButtonText' => 'OK',
                'confirmButtonColor' => '#dc2626',
            ]);
            return;
        }

        $findOffice = array_map(
            function ($office) {
                return [
                    'id' => $office['id'],
                    'code' => $office['officeCode'],
                    'name' => $office['officeName']
                ];
            },
            array_filter($this->responseOffices['officeList'], function ($office) {
                return $office['id'] == $this->assignedTo;
            })
        );

        return $findOffice[$this->assignedTo - 1];
    }

    /** End of Multiple Selection */

    /** Close Document */
    public function modalCloseDocument()
    {
        $this->phrase = Str::random(8);
    }

    public function confirmCloseDocument()
    {
        $this->alert('warning', 'Close ' . count($this->selected_item) . ' Documents?', [
            'position' => 'center',
            'toast' => true,
            'timer' => null,
            'showConfirmButton' => true,
            'confirmButtonText' => 'Confirm',
            'onConfirmed' => 'close',
            'confirmButtonColor' => '#dc2626',
            'showCancelButton' => true,
            'cancelButtonText' => 'Cancel',
            'onDismissed' => 'closeModal'
        ]);
    }

    public function close()
    {
        $this->checkApiConnection();

        $data = $this->validate([
            'phrase' => 'required',
            'passphrase' => 'required'
        ]);

        if ($data['phrase'] === $data['passphrase']) {

            Arr::map($this->selected_item, function ($item) {
                $data = $this->validate([
                    'remarks' => 'required'
                ]);

                DB::transaction(function () use ($item, $data) {
                    Document::find($item)->update([
                        'status' => 'Closed'
                    ]);

                    $document = Document::find($item);
                    $doc_type = is_object($document) && $document->is_bundle ? 'Bundle' : 'Document';
                    $lookUpOffice = $this->lookUpOffice($document->assigned_to);

                    Log::create([
                        'action_id' => Action::firstWhere('name', $document->status)->id,
                        'document_id' => $document->id,
                        'user_id' => $this->user['id'],
                        'office_id' => $this->office,
                        'assigned_to' => $this->office,
                        'description' => $doc_type . " (" . $document->control_no . ") has been acted upon and closed by " . $lookUpOffice['name'],
                        'remarks' => $data['remarks']
                    ]);

                    $this->attachments = Document::where('assigned_to', $this->office)->where('status', 'On Process')->where('bundle_id', $item)->orderBy('created_at', 'DESC')->get();

                    /** Calculate Turn Around Time */
                    $turnaroundTime = $this->calculateTurnaroundTime($document->id);

                    Document::find($document->id)->update([
                        'turnaroundtime' => $turnaroundTime
                    ]);

                    foreach ($this->attachments as $attachment) {

                        Document::find($attachment->id)->update([
                            'status' => 'Closed'
                        ]);

                        //Closed Log
                        Log::create([
                            'action_id' => Action::firstWhere('name', 'Closed')->id,
                            'document_id' => $attachment->id,
                            'bundle_id' => $document->id,
                            'user_id' => $this->user['id'],
                            'office_id' => $this->office,
                            'assigned_to' => $attachment->assigned_to,
                            'description' => "Bundle (" . $document->control_no . ") has been acted upon and closed by " . $lookUpOffice['name'] . ".",
                            'remarks' => $data['remarks']
                        ]);

                        /** Calculate Turn Around Time */
                        $turnaroundTime = $this->calculateTurnaroundTime($attachment->id);

                        Document::find($attachment->id)->update([
                            'turnaroundtime' => $turnaroundTime
                        ]);
                    }
                });

                $this->redirect(Pending::class);
            });

            $this->showAlert($type = 'success', $message = 'successfully closed!');
        } else {

            $this->showAlert($type = 'error', $message = 'unsuccessfully closed, please enter the characters correctly!');
            return $this->redirect(Pending::class);
        }
    }
    /** End of Document */

    /** Endorsement Document */
    public function endorse()
    {
        $this->checkApiConnection();

        Arr::map($this->selected_item, function ($item) {

            $data = $this->validate([
                'endorsedToPersonnel' => 'required',
                'remarks' => 'required'
            ]);
            $document = Document::find($item);

            if ($document->endorsed_to != $data['endorsedToPersonnel']) {

                //Update Document
                Document::find($item)->update([
                    'endorsed_to' => $data['endorsedToPersonnel'],
                    'status' => 'On Process'
                ]);

                $lookUpPersonnel = $this->filterUser($data['endorsedToPersonnel']);
                $doc_type = $document->is_bundle ? 'Bundle' : 'Document';

                //Endorse Log
                Log::create([
                    'action_id' => Action::firstWhere('name', 'Endorsed')->id,
                    'document_id' => $document->id,
                    'user_id' => $this->user['id'],
                    'office_id' => $this->office,
                    'assigned_to' => $this->office,
                    'endorsed_to' => $data['endorsedToPersonnel'],
                    'description' => $doc_type . " endorsed to " . $lookUpPersonnel . " for appropriate action.",
                    'remarks' => $data['remarks']
                ]);

                //Add log for Documents endorsed together with this bundle
                $this->attachments = Document::where('assigned_to', $this->office)->where('status', 'On Process')->where('bundle_id', $item)->orderBy('created_at', 'DESC')->get();
                foreach ($this->attachments as $attachment) {

                    Document::find($attachment->id)->update([
                        'endorsed_to' => $data['endorsedToPersonnel'],
                        'status' => 'On Process'
                    ]);

                    //Forward Log
                    Log::create([
                        'action_id' => Action::firstWhere('name', 'Endorsed')->id,
                        'document_id' => $attachment->id,
                        'bundle_id' => $document->id,
                        'user_id' => $this->user['id'],
                        'office_id' => $this->office,
                        'assigned_to' => $this->office,
                        'endorsed_to' => $data['endorsedToPersonnel'],
                        'description' => "Bundle (" . $document->control_no . ") endorsed to " . $lookUpPersonnel . " for appropriate action.",
                        'remarks' => $data['remarks']
                    ]);
                }
            }
        });

        $this->dispatch('close-modal', class: '.document-modal');
        $this->showAlert($type = 'success', $message = 'successfully endorsed!');
        return redirect()->to('/status-pending');
    }
    /** End of Endorsement Document */

    /** Forward Documents */
    public function forward()
    {
        $this->checkApiConnection();

        Arr::map($this->selected_item, function ($item) {
            $data = $this->validate([
                'assignedTo' => 'required',
                'endorsedToOtherPersonnel' => '',
                'remarks' => ''
            ]);

            DB::transaction(function () use ($item, $data) {
                Document::find($item)->update([
                    'assigned_to' => $data['assignedTo'],
                    'endorsed_to' => $data['endorsedToOtherPersonnel'],
                    'status' => 'For Receiving'
                ]);

                $document = Document::find($item);
                $doc_type = is_object($document) && $document->is_bundle ? 'Bundle' : 'Document';
                $lookUpOffice = $this->lookUpOffice($document->assigned_to);

                //Forwarded Log by the current office
                Log::create([
                    'action_id' => Action::firstWhere('name', 'Forwarded')->id,
                    'document_id' => $document->id,
                    'user_id' => $this->user['id'],
                    'office_id' => $this->office,
                    'assigned_to' => $this->office,
                    'endorsed_to' => $data['endorsedToOtherPersonnel'],
                    'description' => $doc_type . " forwarded to " . $lookUpOffice['name'] . " for appropriate action.",
                    'remarks' => $data['remarks']
                ]);

                // For Receiving Log for the next office
                Log::create([
                    'action_id' => Action::firstWhere('name', 'For Receiving')->id,
                    'document_id' => $document->id,
                    'user_id' => $this->user['id'],
                    'office_id' => $this->office,
                    'assigned_to' => $data['assignedTo'],
                    'endorsed_to' => $data['endorsedToOtherPersonnel'],
                    'description' => $doc_type . " has been transferred and is to be received by " . $lookUpOffice['name'],
                    'remarks' => $data['remarks']
                ]);

                //Add log for Documents forwarded together with this bundle
                $this->attachments = Document::where('assigned_to', $this->office)->where('status', 'On Process')->where('bundle_id', $item)->orderBy('created_at', 'DESC')->get();
                foreach ($this->attachments as $attachment) {

                    Document::find($attachment->id)->update([
                        'assigned_to' => $data['assignedTo'],
                        'endorsed_to' => $data['endorsedToOtherPersonnel'],
                        'status' => 'For Receiving'
                    ]);

                    //Forward Log
                    Log::create([
                        'action_id' => Action::firstWhere('name', 'Forwarded')->id,
                        'document_id' => $attachment->id,
                        'bundle_id' => $document->id,
                        'user_id' => $this->user['id'],
                        'office_id' => $this->office,
                        'assigned_to' => $this->office,
                        'endorsed_to' => $data['endorsedToOtherPersonnel'],
                        'description' => "Bundle (" . $document->control_no . ") forwarded to " . $lookUpOffice['name'] . " for appropriate action.",
                        'remarks' => $data['remarks']
                    ]);

                    // For Receiving Log
                    Log::create([
                        'action_id' => Action::firstWhere('name', 'For Receiving')->id,
                        'document_id' => $attachment->id,
                        'bundle_id' => $document->id,
                        'user_id' => $this->user['id'],
                        'office_id' => $this->office,
                        'assigned_to' => $data['assignedTo'],
                        'endorsed_to' => $data['endorsedToOtherPersonnel'],
                        'description' => "Bundle (" . $document->control_no . ")" . " has been transferred and is to be received by " . $lookUpOffice['name'] . ".",
                        'remarks' => $data['remarks']
                    ]);
                }
            });

            return $this->redirect(Pending::class);
        });

        $this->showAlert($type = 'success', $message = 'successfully forwarded!');
    }
    /** End of Forward Documents */

    /** Miscellanous Functions */
    #[On('closeModal')]
    public function closeModal()
    {
        return $this->redirect(Pending::class);
    }

    public function showAlert($type, $message)
    {
        $this->alert($type, 'Document ' . $message, [
            'position' => 'top-end',
            'timer' => 10000,
            'toast' => true
        ]);
    }

    public function calculateTurnaroundTime($documentId)
    {
        try {
            // Get the start date (when document was created)
            $startLog = Log::where('document_id', $documentId)
                ->where('action_id', Action::firstWhere('name', 'Created')->id)
                ->orderBy('created_at', 'ASC')
                ->first();

            // Get the end date (when document was closed)
            $endLog = Log::where('document_id', $documentId)
                ->where('action_id', Action::firstWhere('name', 'Closed')->id)
                ->orderBy('created_at', 'DESC')
                ->first();

            // Check if both logs exist
            if (!$startLog || !$endLog) {
                return 0; // Return 0 if either log is missing
            }

            $startDate = $startLog->created_at;
            $endDate = $endLog->created_at;
            $totalDays = 0;

            if ($startDate && $endDate) {
                $totalDays = Carbon::parse($startDate)->diffInDaysFiltered(function (Carbon $date) {
                    return !$date->isWeekend();
                }, $endDate);

                return $totalDays;
            }

        } catch (\Exception $e) {

            return 0;
        }
    }

    public function inputRemarks($statement)
    {
        switch ($statement) {
            case 'Approved':
                return $this->remarks = $this->statement['approved'];
                break;
            case 'Signed':
                return $this->remarks = $this->statement['signed'];
                break;
            case 'Initialed':
                return $this->remarks = $this->statement['initialed'];
                break;
            case 'Checked':
                return $this->remarks = $this->statement['checked'];
                break;
            case 'Processed':
                return $this->remarks = $this->statement['processed'];
                break;
            default;
        }
    }

    public function colorIndicator($status)
    {
        switch ($status) {
            case 'Created':
                return "bg-gray-50";
                break;
            case 'Closed':
                return "bg-red-100";
                break;
            case 'On Process':
                return "bg-yellow-100";
                break;
            case 'Returned':
                return "bg-amber-100";
            default:
                return "bg-sky-100";
        }
    }

    public function iconIndicator($status)
    {
        switch ($status) {
            case 'Created':
                return '<svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-pen-line"><path d="m18 5-2.414-2.414A2 2 0 0 0 14.172 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2"/><path d="M21.378 12.626a1 1 0 0 0-3.004-3.004l-4.01 4.012a2 2 0 0 0-.506.854l-.837 2.87a.5.5 0 0 0 .62.62l2.87-.837a2 2 0 0 0 .854-.506z"/><path d="M8 18h1"/></svg>';
                break;
            case 'Closed':
                return '<svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-x-2"><path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="m8 12.5-5 5"/><path d="m3 12.5 5 5"/></svg>';
                break;
            case 'On Process':
                return '<svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-ccw"><path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/><path d="M16 16h5v5"/></svg>';
                break;
            case 'Returned':
                return '<svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-symlink"><path d="m10 18 3-3-3-3"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M4 11V4a2 2 0 0 1 2-2h9l5 5v13a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h7"/></svg>';
            default:
                return '<svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-input"><path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v4"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M2 15h10"/><path d="m9 18 3-3-3-3"/></svg>';
        }
    }

    public function documentTypeFilter($type)
    {
        return $this->selectFilter = Category::where('name', 'like', '%' . $type . '%')->pluck('id')->toArray();
    }

    public function filterUser($encoded_user)
    {
        $this->endorsedID = $encoded_user;

        $result = array_filter($this->responseEmployees['employeesList'], function ($employee) {
            return $employee['id'] == $this->endorsedID;
        });

        $result = array_values($result); // reindex the array
        if (empty($result)) {
            return 'Unknown User';
        }
        $findUser = $result[0];
        return $findUser['firstName'] . ' ' . $findUser['lastName'] . ' ' . $findUser['suffix'];
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
    /** End of Miscellanous Functions */

    public function render()
    {
        $documents = Document::query()
             ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('control_no', 'like', '%' . $this->search . '%')
                      ->orWhere('subject', 'like', '%' . $this->search . '%');
                })
                ->where('status', 'On Process')
                ->where('assigned_to', $this->office)
                ->whereNull('bundle_id');
            })
            ->when($this->selectFilter, function ($query) {
                $query->whereIn('category_id', $this->selectFilter);
            })
            ->when($this->startDate, function ($query) {
                $query->whereBetween('created_at', [Carbon::parse($this->startDate), Carbon::parse($this->endDate)->addDay()]);
            })
            ->where('status', 'On Process')
            ->whereNull('bundle_id')
            ->where('assigned_to', $this->office)
            ->orderBy('created_at', 'ASC')->paginate(50);

        return view(
            'livewire.status.pending',
            [
                'documents' => $documents
            ]
        );
    }
}
