<?php

namespace App\Livewire\Status;

use App\Models\Action;
use App\Models\Category;
use App\Models\Document;
use App\Models\Log;
use App\Services\ApiService;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class Incoming extends Component
{
    use WithPagination;
    use LivewireAlert;

    #[Title('Incoming Documents | Document Tracking Information System')]

    /** Constant Variables */
    /**
     * Directory data (offices/employees) is large (~400 KB) and barely changes.
     * Kept protected so Livewire does NOT serialize it into the wire snapshot on
     * every request; it is reloaded cheaply from cache each request via boot().
     */
    protected $offices = [];
    public $user = [];
    public $endorsedID;
    protected $responseOffices;
    protected $responseEmployees;
    protected $employees = [];
    protected $filterOfficeEmployees = [];

    /** Search & Filter Variables*/
    public $search = '';
    public $selectFilter = [];

    /** Multiple Selection */
    public $selected_item = [];
    public $selectAll = false;
    public $assigned_to;

    /** Receive Variables */
    public int $document_id;
    public $selected_office;
    public $office;
    public $attachments;

    /** Filter Date Variables */
    public $startDate;
    public $endDate;

    /** Listeners for Livewire Alerts */
    protected $listeners = [
        'receive',
        'closeModal'
    ];

    /** Modal Variables */
    public $modalTitle;
    public $modalContent;
    public $modalAction;

    /**
     * Runs on every request (before mount and before public-prop hydration).
     * Reloads the protected directory data from cache so it is available for
     * render and action methods without bloating the Livewire snapshot.
     */
    public function boot()
    {
        $this->checkApiConnection();
    }

    public function mount()
    {
        /** User Information */
        $this->user = session('user', []);
        $this->office = $this->user['office']['id'];
        /** End User Information */

        /** Filter Records last 30 days */
        $this->startDate = Carbon::now()->subQuarter(1)->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');

        $this->modalTitle = 'Receive Document';
        $this->modalContent = 'Are you sure you want to receive the selected document(s)?';
        $this->modalAction = 'receive';

    }

    /**
     * Check API server connection and fetch employee and office data
     * Returns true if successful, false otherwise
     */
    private function checkApiConnection()
    {
        $this->responseEmployees = app(ApiService::class)->getEmployeesData();
        $this->responseOffices = app(ApiService::class)->getOfficesData();

        if (!$this->responseEmployees || !$this->responseOffices) {
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

        $this->employees = collect($this->responseEmployees['employeesList'] ?? [])
            ->sortBy('lastName')
            ->values()
            ->all();

        $this->offices = app(ApiService::class)->getActiveOffices($this->responseOffices);

        $sessionOfficeId = session('user')['office']['id'] ?? null;
        $this->filterOfficeEmployees = array_filter($this->employees, function ($office) use ($sessionOfficeId) {
            return isset($office['office']['id']) && $office['office']['id'] == $sessionOfficeId;
        });

        return true;
    }

    /** Reset pagination whenever a filter changes so results never land on an out-of-range page */
    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStartDate()
    {
        $this->resetPage();
    }

    public function updatedEndDate()
    {
        $this->resetPage();
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
            ->whereIn('status', ['For Receiving', 'Returned'])
            ->pluck('id')->toArray() : [];
    }

    public function modalReceiveDocument()
    {
        $this->alert('info', 'Receive ' . count($this->selected_item) . ' Documents?', [
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
        $this->checkApiConnection();

        /** Loop Document item selected */
        Arr::map($this->selected_item, function ($item) {
            DB::transaction(function () use ($item) {
                Document::find($item)->update([
                    'assigned_to' => $this->office,
                    'status' => 'On Process'
                ]);

                $document = Document::find($item);
                $doc_type = is_object($document) && $document->is_bundle ? 'Bundle' : 'Document';
                $lookUpOffice = $this->lookUpOffice($document->assigned_to);

                Log::create([
                    'action_id' => Action::firstWhere('name', 'Received')->id,
                    'document_id' => $document->id,
                    'user_id' => $this->user['id'],
                    'office_id' => $this->office,
                    'assigned_to' => $this->office,
                    'description' => $doc_type . " (" . $document->control_no . ") has been received and being process by " . $lookUpOffice . "."
                ]);

                /** Loop Attachments */
                $this->attachments = Document::where('assigned_to', $this->office)->where('status', 'For Receiving')->where('bundle_id', $item)->orderBy('created_at', 'DESC')->get();
                foreach ($this->attachments as $attachment) {

                    Document::find($attachment->id)->update([
                        'assigned_to' => $this->office,
                        'status' => 'On Process'
                    ]);

                    Log::create([
                        'action_id' => Action::firstWhere('name', 'Received')->id,
                        'document_id' => $attachment->id,
                        'bundle_id' => $document->id,
                        'user_id' => $this->user['id'],
                        'office_id' => $this->office,
                        'assigned_to' => $this->office,
                        'description' => $doc_type . " (" . $document->control_no . ") has been received and being process by " . $lookUpOffice . "."
                    ]);
                }
            });
        });

        $this->showAlert($message = 'received!');
        $this->redirect(Pending::class);
    }
    /** End of Multiple Receive */

    /** Miscellanous Functions */
    #[On('closeModal')]
    public function closeModal()
    {
        return $this->redirect(Incoming::class);
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

    public function documentTypeFilter($type)
    {
        $this->resetPage();

        return $this->selectFilter = Category::where('name', 'like', '%' . $type . '%')->pluck('id')->toArray();
    }

    public function filterUser($encoded_user)
    {
        $this->endorsedID = $encoded_user;

        $result = array_filter($this->responseEmployees['employeesList'], function ($employee) {
            return $employee['id'] == $this->endorsedID;
        });

        $result = array_values($result); // reindex array
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

        // Full officeList (not the active-only dropdown list) so documents
        // assigned to a deactivated office still resolve to its code.
        $result = array_filter($this->responseOffices['officeList'] ?? [], function ($office) {
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
            ->with(['logs', 'category'])
            ->where('assigned_to', $this->office)
            ->whereIn('status', ['For Receiving', 'Returned'])
            ->whereNull('bundle_id')
            ->when($this->search, function ($query) {
                // Properly scope the OR conditions within a nested where
                $query->where(function ($q) {
                    $q->where('control_no', 'like', '%' . $this->search . '%')
                        ->orWhere('subject', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->selectFilter, function ($query) {
                $query->whereIn('category_id', $this->selectFilter);
            })
            ->when($this->startDate && $this->endDate, function ($query) {
                $query->whereBetween('created_at', [
                    Carbon::parse($this->startDate)->startOfDay(),
                    Carbon::parse($this->endDate)->endOfDay()
                ]);
            })
            ->orderBy('created_at', 'ASC')
            ->paginate(50);

        return view(
            'livewire.status.incoming',
            [
                'documents' => $documents
            ]
        );
    }
}
