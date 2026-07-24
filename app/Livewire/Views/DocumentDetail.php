<?php

namespace App\Livewire\Views;

use App\Http\Controllers\ApiController;
use App\Models\Action;
use App\Models\Document;
use App\Models\Log;
use App\Services\ApiService;
use Carbon\Carbon;
use Illuminate\Support\Sleep;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithPagination;

class DocumentDetail extends Component
{
    use WithPagination;
    use LivewireAlert;

    #[Title('Document View | Document Tracking Information System')]

    public Document $document;
    public $logs = [];
    public $type = '';
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
    public $document_to_edit;
    public $isReadOnly = true;
    public $subject;

    /** Forward Variables */
    public int $id;
    public int $assignedTo;
    public $endorsedTo;
    public $office;
    public $user = [];
    public $control_no = '';
    protected $offices = [];
    public $selected_office;
    public $selected_personnel;
    public $remarks;
    public $forwardedDocument;

    /** Timeline Variables */
    public $turnaround_time;
    public $dt1;
    public $dt2;

    /** Add Document Variables */
    public $parent_bundle;
    public $bundle_children;
    public $pendings;
    public $attachments;
    public $documents_attached;

    /** Remove Document Variables */
    public $document_to_remove;

    /** Delete Document Variables */
    public $temp_del_id;
    public $document_to_delete;

    /** Listeners for Livewire Alerts */
    protected $listeners = [
        'forwardDocument',
        'delete',
        'remove',
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
    }

    public function mount($control_no)
    {
        /** User Information */
        $this->user = session('user');
        $this->office = $this->user['office']['id'];
        /** End User Information */

        $this->document = Document::firstWhere('control_no', $control_no);
        $this->id = $this->document->id;
        $this->control_no = $this->document->control_no;
        $this->type = $this->document->category->name;
        $this->subject = $this->document->subject;

        $this->pendings = Document::where('assigned_to', $this->office)->where('status', 'On Process')->whereNull('bundle_id')->orderBy('created_at', 'DESC')->get();
        $this->documents_attached = Document::where('assigned_to', $this->office)->where('status', 'On Process')->where('bundle_id', $this->id)->orderBy('created_at', 'DESC')->get();
        $this->bundle_children = Document::where('bundle_id', $this->id)->whereNot('assigned_to', $this->office)->orderBy('created_at', 'DESC')->get();
    }

    public function updatedAssignedTo($value)
    {
        $this->selected_office = $value;

        $this->subEmployees = array_values(array_filter($this->responseEmployees['employeesList'], function ($office) {
            return isset($office['office']['id']) && $office['office']['id'] == $this->selected_office;
        }));
    }

    /** Forward Document */
    public function modalForwardDocument(int $id)
    {
        $document = Document::find($id);
        $this->control_no = $document->control_no;
        $this->forwardedDocument = $document->id;
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
        if (!isset($this->responseOffices['officeList']) || !is_array($this->responseOffices['officeList'])) {
            return '';
        }

        $result = array_values(array_filter($this->responseOffices['officeList'], function ($office) use ($assigned_to) {
            return $office['id'] == $assigned_to;
        }));

        if (!isset($result[0])) {
            return '';
        }

        return $result[0]['officeName'] ?? '';
    }

    public function forwardDocument()
    {
        $data = $this->validate([
            'forwardedDocument' => 'required',
            'assignedTo' => 'required',
            'endorsedTo' => '',
            'remarks' => ''
        ]);

        $document = Document::find($data['forwardedDocument']);
        $doc_type = $document->is_bundle ? 'Bundle' : 'Document';
        $this->assignedTo = $data['assignedTo'];
        $this->endorsedTo = $data['endorsedTo'];
        $lookUpOffice = $this->lookUpOffice($this->assignedTo);

        $document->update([
            'assigned_to' => $data['assignedTo'],
            'endorsed_to' => $data['endorsedTo'],
            'status' => 'For Receiving'
        ]);

        // Forward Log
        Log::create([
            'action_id' => Action::firstWhere('name', 'Forwarded')->id,
            'document_id' => $document->id,
            'user_id' => $this->user['id'],
            'office_id' => $this->office,
            'assigned_to' => $this->office,
            'endorsed_to' => $this->endorsedTo,
            'description' => $doc_type . " forwarded to " . $lookUpOffice . " for appropriate action.",
            'remarks' => $data['remarks']
        ]);

        Sleep::for(2)->seconds();

        // For Receiving Log
        Log::create([
            'action_id' => Action::firstWhere('name', $document->status)->id,
            'document_id' => $document->id,
            'user_id' => $this->user['id'],
            'office_id' => $this->office,
            'assigned_to' => $this->assignedTo,
            'endorsed_to' => $this->endorsedTo,
            'description' => $doc_type . " has been transferred and is to be received by " . $lookUpOffice,
            'remarks' => $data['remarks']
        ]);

        //Add log for Documents forwarded together with this bundle
        foreach ($this->documents_attached as $attachment) {

            Document::find($attachment->id)->update([
                'assigned_to' => $this->assignedTo,
                'endorsed_to' => $this->endorsedTo,
                'status' => 'For Receiving'
            ]);

            // Forward Log
            Log::create([
                'action_id' => Action::firstWhere('name', 'Forwarded')->id,
                'document_id' => $attachment->id,
                'bundle_id' => $document->id,
                'user_id' => $this->user['id'],
                'office_id' => $this->office,
                'assigned_to' => $this->office,
                'endorsed_to' => $this->endorsedTo,
                'description' => $doc_type . " forwarded to " . $lookUpOffice . " for appropriate action.",
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
                'assigned_to' => $this->assignedTo,
                'endorsed_to' => $this->endorsedTo,
                'description' => $doc_type . " has been transferred and is to be received by " . $lookUpOffice . ".",
                'remarks' => $data['remarks']
            ]);
        }

        $this->dispatch('close-modal', class: '.document-modal');
        $this->showAlert($message = 'forwarded!');
        return redirect()->route('document.view', $this->document->control_no);
    }
    /** End of Forward Document */

    /** Track Document */
    public function trackDocument(int $id)
    {
        $document = Document::find($id);
        $logs = Log::with(['action', 'user'])
            ->where('document_id', $document->id)
            ->orderBy('created_at', 'DESC')
            ->orderBy('id', 'DESC') // tiebreaker for entries sharing a timestamp (Forwarded + For Receiving)
            ->get();

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

    public function calculateTurnaroundTime(int $id)
    {
        $logs = Log::where('document_id', $id)->orderBy('created_at', 'DESC')->get();

        $this->dt1 = $logs->firstWhere('action_id', 1)->created_at ?? false;
        $this->dt2 = $logs->firstWhere('action_id', 5)->created_at ?? Carbon::now();

        if ($this->dt1) {
            $this->turnaround_time = Carbon::parse($this->dt1)->diffInDaysFiltered(function (Carbon $date) {
                return !$date->isWeekend();
            }, $this->dt2);
        }
    }
    /** End of Track Document */

    /** Delete Document */
    public function confirmDelete($id)
    {
        $this->alert('error', 'Delete Document?', [
            'position' => 'center',
            'toast' => true,
            'timer' => null,
            'showConfirmButton' => true,
            'confirmButtonText' => 'Confirm',
            'onConfirmed' => 'delete',
            'confirmButtonColor' => '#dc2626',
            'showCancelButton' => true,
            'cancelButtonText' => 'Cancel',
            'onDismissed' => 'closeModal'
        ]);
        $this->temp_del_id = $id;
    }

    public function delete()
    {
        $this->document_to_delete = Document::find($this->temp_del_id);
        $this->document_to_delete->delete();
        $this->document_to_delete->logs()->delete();

        $this->showAlert('deleted!');
        return $this->selectRoute($this->type);
    }
    /** End of Delete Document */

    /** Add Documents in Bundle */
    public function modalSelectDocuments(int $parent_bundle)
    {
        $document = Document::find($parent_bundle);

        $this->control_no = $document->control_no;
        $this->parent_bundle = $document->id;
    }

    public function confirmSelectedDocuments()
    {
        $data = $this->validate([
            'parent_bundle' => 'required',
            'attachments' => 'required'
        ]);

        $bundle = Document::find($data['parent_bundle']);

        foreach ($data['attachments'] as $attachment) {
            Document::find($attachment)->update(
                [
                    'bundle_id' => $data['parent_bundle'],
                ]
            );

            /** Update Log of each document regarding its addition to this Bundle */
            Log::create([
                'action_id' => Action::firstWhere('name', 'Attached')->id,
                'document_id' => $attachment,
                'bundle_id' => $data['parent_bundle'],
                'user_id' => $this->user['id'],
                'office_id' => $this->office,
                'assigned_to' => $this->office,
                'description' => "Document has been attached to Bundle of " . $bundle->category->name . " (" . $bundle->control_no . ")."
            ]);
        }

        $this->dispatch('close-modal', class: '.document-modal');
        $this->showAlert($message = 'added to bundle!');
        return redirect()->route('document.view', $this->document->control_no);
    }
    /** End of Add Documents */

    /** Remove Attached Document */
    public function confirmRemoveDocument(int $document_to_remove)
    {
        $this->document_to_remove = $document_to_remove;

        $this->alert('error', 'Remove attached Document?', [
            'position' => 'center',
            'toast' => true,
            'timer' => null,
            'showConfirmButton' => true,
            'confirmButtonText' => 'Confirm',
            'onConfirmed' => 'remove',
            'confirmButtonColor' => '#dc2626',
            'showCancelButton' => true,
            'cancelButtonText' => 'Cancel',
            'onDismissed' => 'closeModal'
        ]);
    }

    public function remove()
    {
        $document = Document::find($this->document_to_remove);
        $bundle = Document::find($document->bundle_id);

        /** Update Log of the document regarding its removal from this Bundle */
        Log::create([
            'action_id' => Action::firstWhere('name', 'Removed')->id,
            'document_id' => $document->id,
            'bundle_id' => null,
            'user_id' => $this->user['id'],
            'office_id' => $this->office,
            'assigned_to' => $this->office,
            'description' => "Document has been removed to Bundle of " . $bundle->category->name . " (" . $bundle->control_no . ")."
        ]);

        $document->update([
            'bundle_id' => null
        ]);

        $this->dispatch('close-modal', class: '.document-modal');
        $this->showAlert($message = 'removed to bundle!');
        return redirect()->route('document.view', $this->document->control_no);
    }
    /** End of Remove Attached Document */

    /** Miscellanous Functions */
    public function toggleEditSubject($document_to_edit)
    {
        if (Document::find($document_to_edit)->status == 'Closed') {
            $this->alert('error', 'Cannot edit subject of closed document!', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true
            ]);
            return;
        }

        $this->isReadOnly = !$this->isReadOnly;
        $document = Document::find($document_to_edit);
        $document->subject = $this->subject;
        $document->save();
    }
    public function filterUser($encoded_user)
    {
        if ($encoded_user) {
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
        return redirect()->route('document.view', $this->document->control_no);
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

        return view('livewire.views.document-detail');
    }
}
