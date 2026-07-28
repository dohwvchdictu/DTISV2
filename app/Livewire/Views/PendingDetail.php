<?php

namespace App\Livewire\Views;

use Livewire\Component;

use App\Models\Action;
use App\Models\Document;
use App\Models\Log;
use App\Services\ApiService;
use Carbon\Carbon;
use Illuminate\Support\Sleep;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Illuminate\Support\Str;

class PendingDetail extends Component
{
    use WithPagination;
    use LivewireAlert;

    #[Title('Status View | Document Tracking Information System')]

    public Document $document;
    public $logs = [];
    public $type = '';
    public $user = [];
    /**
     * Large, rarely-changing directory data. Kept protected so it is NOT
     * serialized into the Livewire snapshot on every request; reloaded from
     * cache each request via boot().
     */
    protected $responseOffices;
    protected $responseEmployees;
    protected $employees = [];
    /** Small per-office subset shown in a dropdown — must stay public for the Blade view. */
    public $subEmployees = [];
    protected $filterOfficeEmployees = [];
    protected $offices = [];
    public $phrase = '';
    public $passphrase = '';

    /** Forward Variables */
    public int $id;
    public int $assignedTo;
    public int $endorsedToPersonnel;
    public int $endorsedToOtherPersonnel;
    public $office;
    public $control_no = '';
    public $selected_office;
    public $remarks;
    public $document_to_forward;
    public $document_to_endorse;

    /** Close Variables */
    public $document_to_close;
    public $statement = [
        'approved' => 'Document has been approved.',
        'signed' => 'Document has been signed.',
        'initialed' => 'Document has been initialed.',
        'checked' => 'Document has been checked.',
        'processed' => 'Document has been processed.'
    ];

    /** Timeline Variables */
    public $turnaround_time;
    public $dt1;
    public $dt2;

    /** Add Document Variables */
    public $pendings;
    public $attachments;
    public $documents_attached;
    public $parent_bundle;

    /** Photo */
    public int $employeePhoto;
    public $photoUrl;
    public $jwtToken;
    public $image;

    /** Listeners for Livewire Alerts */
    protected $listeners = [
        'forwardDocument',
        'endorse',
        'close',
        'closeModal',
    ];

    /**
     * Runs on every request (before mount and before public-prop hydration).
     * Reloads the protected directory data from cache so it is available for
     * render and action methods without bloating the Livewire snapshot.
     */
    public function boot()
    {
        $this->responseEmployees = app(ApiService::class)->getEmployeesData();
        $this->employees = collect($this->responseEmployees['employeesList'] ?? [])
            ->sortBy('lastName')
            ->values()
            ->all();

        $this->responseOffices = app(ApiService::class)->getOfficesData();
        $this->offices = app(ApiService::class)->getActiveOffices($this->responseOffices);

        $sessionOfficeId = session('user')['office']['id'] ?? null;
        $this->filterOfficeEmployees = array_filter($this->employees, function ($office) use ($sessionOfficeId) {
            return isset($office['office']['id']) && $office['office']['id'] == $sessionOfficeId;
        });
    }

    public function mount($control_no)
    {
        /** User Information */
        $this->user = session('user');
        $this->office = $this->user['office']['id'];
        /** End User Information */

        $this->document = Document::firstWhere('control_no', $control_no);
        $this->id = $this->document->id;
        $this->document_to_close = $this->document->id;
        $this->parent_bundle = $this->document->id;
        $this->control_no = $this->document->control_no;
        $this->type = $this->document->category->name;

        $this->pendings = Document::where('assigned_to', $this->office)->where('status', 'On Process')->whereNull('bundle_id')->orderBy('created_at', 'DESC')->get();
        $this->documents_attached = Document::where('assigned_to', $this->office)->where('status','On Process')->where('bundle_id', $this->parent_bundle)->orderBy('created_at', 'DESC')->get();

    }

    public function updatedAssignedTo($value)
    {
        $this->selected_office = $value;

        $this->subEmployees = array_filter($this->employees, function ($office) {
            return isset($office['office']['id']) && $office['office']['id'] == $this->selected_office;
        });
    }

    /** Forward Document */
    public function modalForwardDocument(int $id)
    {
        $document = Document::find($id);
        $this->control_no = $document->control_no;
        $this->document_to_forward = $document->id;
        $this->remarks = '';
    }

    public function confirmForwardDocument()
    {
        $this->alert('warning', 'Forward Document?', [
            'position' => 'center',
            'toast' => true,
            'timer' => null,
            'showConfirmButton' => true,
            'confirmButtonText' => 'Confirm',
            'onConfirmed' => 'forwardDocument',
            'confirmButtonColor' => '#059669',
            'showCancelButton' => true,
            'cancelButtonText' => 'Cancel',
            'onDismissed' => 'closeModal'
        ]);
    }

    public function lookUpOffice($assigned_to)
    {
        $this->selected_office = $this->assignedTo ?? $assigned_to;

        $result = array_filter($this->responseOffices['officeList'], function ($office) {
            return $office['id'] == $this->selected_office;
        });

        $findOffice = $result[$this->selected_office - 1];
        return $findOffice['officeName'];
    }

    public function forwardDocument()
    {
        $data = $this->validate([
            'document_to_forward' => 'required',
            'endorsedToOtherPersonnel' => '',
            'assignedTo' => 'required',
            'remarks' => ''
        ]);

        $document = Document::find($data['document_to_forward']);
        $document->update([
            'assigned_to' => $data['assignedTo'],
            'endorsed_to' => $data['endorsedToOtherPersonnel'],
            'status' => 'For Receiving'
        ]);

        $this->assignedTo = $data['assignedTo'];
        $doc_type = $document->is_bundle ? 'Bundle' : 'Document';
        $lookUpOffice = $this->lookUpOffice($this->assignedTo);

        // Forward Log
        Log::create([
            'action_id' => Action::firstWhere('name', 'Forwarded')->id,
            'document_id' => $document->id,
            'user_id' => $this->user['id'],
            'office_id' => $this->office,
            'assigned_to' => $this->office,
            'endorsed_to' => $data['endorsedToOtherPersonnel'],
            'description' => $doc_type . " forwarded to " . $lookUpOffice . " for appropriate action.",
            'remarks' => $data['remarks']
        ]);

        Sleep::for(2)->seconds();

        // For Receiving Log
        Log::create([
            'action_id' => Action::firstWhere('name', 'For Receiving')->id,
            'document_id' => $document->id,
            'user_id' => $this->user['id'],
            'office_id' => $this->office,
            'assigned_to' => $data['assignedTo'],
            'endorsed_to' => $data['endorsedToOtherPersonnel'],
            'description' => $doc_type . " has been transferred and is to be received by " . $lookUpOffice,
            'remarks' => $data['remarks']
        ]);

        //Add log for Documents forwarded together with this bundle
        foreach ($this->documents_attached as $attachment) {

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
                'description' => "Bundle (" . $document->control_no . ") forwarded to " . $lookUpOffice . " for appropriate action.",
                'remarks' => $data['remarks']
            ]);

            Sleep::for(2)->seconds();

            // For Receiving Log
            Log::create([
                'action_id' => Action::firstWhere('name', 'For Receiving')->id,
                'document_id' => $attachment->id,
                'bundle_id' => $document->id,
                'user_id' => $this->user['id'],
                'office_id' => $this->office,
                'assigned_to' => $data['assignedTo'],
                'endorsed_to' => $data['endorsedToOtherPersonnel'],
                'description' => "Bundle (" . $document->control_no . ")" . " has been transferred and is to be received by " . $lookUpOffice . ".",
                'remarks' => $data['remarks']
            ]);
        }


        $this->dispatch('close-modal', class: '.document-modal');
        $this->showAlert($type = 'success', $message = 'successfully forwarded!');

        if ($document->endorsed_to === null) {
            return redirect('/status-pending');
        }
        return redirect('/status-endorsed');
    }
    /** End of Forward Document */

    /** Endorsement Document */
    public function modalEndorseDocument(int $id)
    {
        $document = Document::find($id);
        $this->control_no = $document->control_no;
        $this->document_to_endorse = $document->id;
        $this->remarks = '';
    }

    public function confirmEndorseDocument()
    {
        $this->alert('warning', 'Endorse Document?', [
            'position' => 'center',
            'toast' => true,
            'timer' => null,
            'showConfirmButton' => true,
            'confirmButtonText' => 'Confirm',
            'onConfirmed' => 'endorse',
            'confirmButtonColor' => '#6A5ACD',
            'showCancelButton' => true,
            'cancelButtonText' => 'Cancel',
            'onDismissed' => 'closeModal'
        ]);
    }

    public function endorse()
    {
        $data = $this->validate([
            'document_to_endorse' => 'required',
            'endorsedToPersonnel' => 'required',
            'remarks' => 'required'
        ]);

        //Update Document
        $document = Document::find($data['document_to_endorse']);
        $document->update([
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
        foreach ($this->documents_attached as $attachment) {

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

        $this->dispatch('close-modal', class: '.document-modal');
        $this->showAlert($type = 'success', $message = 'successfully endorsed!');
        return redirect()->to('/status-pending');
    }
    /** End of Endorsement Document */

    /** Track Document */
    public function trackDocument(int $id)
    {
        $logs = Log::where('document_id', $id)->orderBy('created_at', 'DESC')->get();
        $document = Document::find($id);

        $this->calculateTurnaroundTime($document->id);

        if ($logs) {
            return [
                $this->logs = $logs,
                $this->control_no = $document->control_no,
            ];
        } else {
            return redirect()->to('/my-documents');
        }
    }

    public function suffixTurnaroundTime()
    {
        return $this->turnaround_time <= 1 ? 'Day' : 'Days';
    }
    /** End of Track Document */

    /** Close Document */
    public function modalCloseDocument()
    {
        $this->phrase = Str::random(8);
        $this->remarks = '';
    }

    public function confirmCloseDocument()
    {
        $this->alert('warning', 'Close Document?', [
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
        $data = $this->validate([
            'phrase' => 'required',
            'passphrase' => 'required',
            'remarks' => 'required'
        ]);

        if ($data['phrase'] === $data['passphrase']) {

            $document = Document::find($this->document_to_close);

            $document->update([
                'status' => 'Closed',
            ]);

            $this->assignedTo = $document->assigned_to;
            $doc_type = $document->is_bundle == '1' ? 'Bundle' : 'Document';
            $lookUpOffice = $this->lookUpOffice($this->assignedTo);

            // Close Log
            Log::create([
                'action_id' => Action::firstWhere('name', $document->status)->id,
                'document_id' => $document->id,
                'user_id' => $this->user['id'],
                'office_id' => $this->office,
                'assigned_to' => $this->office,
                'description' => $doc_type . " (" . $document->control_no . ") has been acted upon and closed by " . $lookUpOffice . ".",
                'remarks' => $data['remarks']
            ]);

            $document->update([
                'turnaroundtime' => $this->calculateTurnaroundTime($document->id)
            ]);

            if ($this->documents_attached) {
                //Add log for Documents closed together with this bundle
                foreach ($this->documents_attached as $attachment) {

                    Document::find($attachment->id)->update([
                        'status' => 'Closed',
                    ]);

                    //Closed Log
                    Log::create([
                        'action_id' => Action::firstWhere('name', 'Closed')->id,
                        'document_id' => $attachment->id,
                        'bundle_id' => $document->id,
                        'user_id' => $this->user['id'],
                        'office_id' => $this->office,
                        'assigned_to' => $attachment->assigned_to,
                        'description' => "Bundle (" . $document->control_no . ") has been acted upon and closed by " . $lookUpOffice . ".",
                        'remarks' => $data['remarks']
                    ]);

                    Document::find($attachment->id)->update([
                        'turnaroundtime' =>  $this->calculateTurnaroundTime($attachment->id)
                    ]);
                }
            }

            $this->showAlert($type = 'success', $message = 'successfully closed!');
            if ($document->endorsed_to === null) {
                return redirect('/status-pending');
            }
            return redirect('/status-endorsed');
        } else {
            $this->showAlert($type = 'error', $message = 'unsuccessfully closed, enter the phrase correctly!');
            return redirect()->route('document.pending', $this->document->control_no);
        }
    }
    /** End of Close Document */

    /** Miscellanous Functions */
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

    public function filterUser($encoded_user)
    {
        $this->id = $encoded_user;

        $result = array_filter($this->employees, function ($employee) {
            return $employee['id'] == $this->id;
        });

        $result = array_values($result); // reindex array
        $findUser = $result[0] ?? null;
        if (!$findUser) {
            return '';
        }
        return $findUser['firstName'] . ' ' . $findUser['lastName'] . ' ' . $findUser['suffix'];
    }

    #[On('closeModal')]
    public function closeModal()
    {
        return redirect()->route('document.pending', $this->document->control_no);
    }

    public function showAlert($type, $message)
    {
        $this->alert($type, 'Document ' . $message, [
            'position' => 'top-end',
            'timer' => 10000,
            'toast' => true
        ]);
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
                return "bg-gray-50 dark:bg-neutral-700";
                break;
            case 'Closed':
                return "bg-red-100 dark:bg-red-500/20";
                break;
            case 'On Process':
                return "bg-yellow-100 dark:bg-yellow-500/20";
                break;
            case 'Returned':
                return "bg-amber-100 dark:bg-amber-500/20";
            default:
                return "bg-sky-100 dark:bg-sky-500/20";
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

    public function typeIndicator($type)
    {
        switch ($type) {
            case 1:
                return '<svg class="shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-check"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg> Bundle';
                break;
            default:
                return '<svg class="shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-x"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg> Document';
        }
    }
    /** End of Miscellanous Functions */

    public function render()
    {
        return view('livewire.views.pending-detail');
    }
}
