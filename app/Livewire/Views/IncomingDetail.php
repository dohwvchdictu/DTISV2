<?php

namespace App\Livewire\Views;

use App\Livewire\Status\Pending;
use App\Models\Action;
use App\Models\Document;
use App\Models\Log;
use App\Services\ApiService;
use Carbon\Carbon;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class IncomingDetail extends Component
{
    use WithPagination;
    use LivewireAlert;

    #[Title('Status View | Document Tracking Information System')]

    /** Constant Variables */
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
    public $parent_bundle;

    /** Forward Variables */
    public int $id;
    public int $assigned_to;
    public $control_no = '';
    protected $offices = [];
    public $selected_office;

    /** Return Variables */
    public $returnedDocument;
    public int $returnTo;
    public $remarks;

    /** Receive Variables */
    public int $document_id;
    public int $return_office;
    public $office;

    /** Timeline Variables */
    public $turnaround_time;
    public $dt1;
    public $dt2;

    /** Add Document Variables */
    public $pendings;
    public $attachments;
    public $documents_attached;

    /** Listeners for Livewire Alerts */
    protected $listeners = [
        'receive',
        'return',
        'closeModal'
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
        $this->offices = collect($this->responseOffices['officeList'] ?? [])
            ->sortBy('officeName')
            ->values()
            ->all();
    }

    public function mount($control_no)
    {
        /** User Information */
        $this->user = session('user');
        $this->office = $this->user['office']['id'];
        /** End User Information */

        $this->document = Document::firstWhere('control_no', $control_no);
        $this->id = $this->document->id;
        $this->parent_bundle = $this->document->id;
        $this->control_no = $this->document->control_no;
        $this->type = $this->document->category->name;

        $this->pendings = Document::where('assigned_to', $this->office)->where('status', 'On Process')->whereNull('bundle_id')->orderBy('created_at', 'DESC')->get();
        $this->documents_attached = Document::where('assigned_to', $this->office)->whereIn('status', ['For Receiving', 'Returned'])->where('bundle_id', $this->parent_bundle)->orderBy('created_at', 'DESC')->get();

    }

    /** Receive Document */
    public function modalReceiveDocument(int $document_id)
    {
        $this->alert('info', 'Receive Document?', [
            'position' => 'center',
            'toast' => true,
            'timer' => null,
            'showConfirmButton' => true,
            'confirmButtonText' => 'Confirm',
            'onConfirmed' => 'receive',
            'confirmButtonColor' => '#059669',
            'showCancelButton' => true,
            'cancelButtonText' => 'Cancel',
            'onDismissed' => 'closeModal'
        ]);

        $this->document_id = $document_id;
    }

    public function lookUpOffice($assigned_to)
    {
        $this->selected_office = $this->assigned_to ?? $assigned_to;

        $result = array_filter($this->responseOffices['officeList'], function ($office) {
            return $office['id'] == $this->selected_office;
        });

        $findOffice = $result[$this->selected_office - 1];
        return $findOffice['officeName'];
    }

    public function receive()
    {
        $document = Document::find($this->document_id);

        $document->update([
            'assigned_to' => $this->office,
            'status' => 'On Process'
        ]);

        $this->assigned_to = $document->assigned_to;
        $doc_type = $document->is_bundle ? 'Bundle' : 'Document';
        $lookUpOffice = $this->lookUpOffice($this->assigned_to);

        // Receive Log
        Log::create([
            'action_id' => Action::firstWhere('name', 'Received')->id,
            'document_id' => $document->id,
            'user_id' => $this->user['id'],
            'office_id' => $this->office,
            'assigned_to' => $this->office,
            'endorsed_to' => $document->endorsed_to,
            'description' =>  $doc_type . " (" . $document->control_no . ") has been received and being process by " . $lookUpOffice . "."
        ]);

        foreach ($this->documents_attached as $attachment) {

            Document::find($attachment->id)->update([
                'assigned_to' => $this->office,
                'status' => 'On Process'
            ]);

            // Receive Log
            Log::create([
                'action_id' => Action::firstWhere('name', 'Received')->id,
                'document_id' => $attachment->id,
                'bundle_id' =>  $document->id,
                'user_id' => $this->user['id'],
                'office_id' => $this->office,
                'assigned_to' => $this->office,
                'endorsed_to' => $document->endorsed_to,
                'description' =>  $doc_type . " (" . $document->control_no . ") has been received and being process by " . $lookUpOffice . "."
            ]);
        }

        $this->showAlert($message = 'received!');

        $this->redirect(Pending::class);
    }
    /** End of Receive Document */

    /** Track Document */
    public function trackDocument(int $id)
    {
        $logs = Log::where('document_id', $id)->orderBy('created_at', 'DESC')->get();
        $document = Document::find($id);

        $this->dt1 = $logs->firstWhere('action_id', 1)->created_at ?? false;
        $this->dt2 = $logs->firstWhere('action_id', 5)->created_at ?? Carbon::now();

        if ($this->dt1) {
            $this->turnaround_time = Carbon::parse($this->dt1)->diffInDaysFiltered(function (Carbon $date) {
                return !$date->isWeekend();
            }, $this->dt2);
        }

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

    /** Return Document */
    public function modalReturnDocument(int $id)
    {
        $document = Document::find($id);
        $this->control_no = $document->control_no;
        $this->returnedDocument = $document->id;
    }

    public function confirmReturnDocument()
    {
        $this->alert('error', 'Return Document?', [
            'position' => 'center',
            'toast' => true,
            'timer' => null,
            'showConfirmButton' => true,
            'confirmButtonText' => 'Confirm',
            'onConfirmed' => 'return',
            'confirmButtonColor' => '#dc2626',
            'showCancelButton' => true,
            'cancelButtonText' => 'Cancel',
            'onDismissed' => 'closeModal'
        ]);
    }

    public function return()
    {
        $data = $this->validate([
            'returnedDocument' => 'required',
            'returnTo' => 'required',
            'remarks' => 'required'
        ]);

        $document = Document::find($data['returnedDocument']);
        $document->update([
            'assigned_to' => $data['returnTo'],
            'status' => 'Returned'
        ]);

        $this->assigned_to = $this->user['office']['id'];
        $doc_type = $document->is_bundle == '1' ? 'Bundle' : 'Document';
        $lookUpOffice = $this->lookUpOffice($this->assigned_to);

        /** Look-up for Return Office */
        $this->return_office = $data['returnTo'];
        $res = array_filter($this->responseOffices['officeList'], function ($office) {
            return isset($office['id']) && $office['id'] == $this->return_office;
        });
        $returnOffice = $res[$this->return_office - 1];

        // Return to Owner Log
        Log::create([
            'action_id' => Action::firstWhere('name', 'Returned')->id,
            'document_id' => $document->id,
            'user_id' => $this->user['id'],
            'office_id' => $this->user['office']['id'],
            'assigned_to' => $data['returnTo'],
            'remarks' => $data['remarks'],
            'description' => $doc_type . " has been returned to " . $returnOffice['officeName'] . " by " . $lookUpOffice . "."
        ]);

        // Return Attached Documents
        $this->documents_attached = Document::where('assigned_to', $this->office)->where('bundle_id', $data['returnedDocument'])->orderBy('created_at', 'DESC')->get();
        foreach ($this->documents_attached as $attachment) {

            Document::find($attachment->id)->update([
                'assigned_to' => $data['returnTo'],
                'status' => 'Returned'
            ]);

            // Return Log
            Log::create([
                'action_id' => Action::firstWhere('name', 'Returned')->id,
                'document_id' => $attachment->id,
                'user_id' => $this->user['id'],
                'office_id' => $this->user['office']['id'],
                'assigned_to' => $data['returnTo'],
                'remarks' => $data['remarks'],
                'description' => $doc_type . " has been returned to " . $returnOffice['officeName'] . " by " . $lookUpOffice . "."
            ]);
        }

        $this->showAlert($message = "returned to " . $returnOffice['officeName'] . "!");

        return redirect()->to('/status-incoming');
    }
    /** End of Return Document */

    /** Miscellanous Functions */
    public function filterUser($encoded_user)
    {
        $this->id = $encoded_user;

        $result = array_filter($this->employees, function ($employee) {
            return $employee['id'] == $this->id;
        });

        $result = array_values($result); // reindex array
        if (!isset($result[0])) {
            return '';
        }
        $findUser = $result[0];
        return $findUser['firstName'] . ' ' . $findUser['lastName'] . ' ' . $findUser['suffix'];
    }

    public function selectRoute($type)
    {
        if (Str::limit($this->type, 17) == 'Purchase Request...') {
            return redirect()->to('/my-purchase-requests');
        } elseif (Str::limit($this->type, 11) == 'Payment for...') {
            return redirect()->to('/my-payments');
        } else {
            return redirect()->to('/my-documents');
        }
    }

    #[On('closeModal')]
    public function closeModal()
    {
        return redirect()->route('document.incoming', $this->document->control_no);
    }

    public function showAlert($message)
    {
        $this->alert('success', 'Document successfully ' . $message, [
            'position' => 'top-end',
            'timer' => 10000,
            'toast' => true
        ]);
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
        return view('livewire.views.incoming-detail');
    }
}
