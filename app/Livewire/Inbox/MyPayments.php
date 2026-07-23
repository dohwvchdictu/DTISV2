<?php

namespace App\Livewire\Inbox;

use App\Models\Action;
use App\Models\Category;
use App\Models\Document;
use App\Models\Log;
use App\Services\ApiService;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Sleep;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class MyPayments extends Component
{
    use WithPagination;
    use LivewireAlert;

    #[Title('My Payments | Document Tracking Information System')]

    /** Constant Variables */
    public $office;
    public $user = [];
    public $categories_array = [];
    public $employees = [];
    public $subEmployees = [];
    public $filterOfficeEmployees = [];
    public $offices;
    public $responseEmployees;
    public $responseOffices;

    /** Search & Filter Variables*/
    public $search = '';
    public $selected_filter = [];

    /** Forward Document Variables */
    public $document;
    public int $id;

    /** Multiple Selection */
    public $selected_item = [];
    public $selectAll = false;
    public $assignedTo;
    public $endorsedTo;

    /** Forward Variables */
    public $remarks;
    public $attachments;
    public $selected_office;

    /** Filter Date Variables */
    public $startDate;
    public $endDate;

    /** Listeners for Livewire Alerts */
    protected $listeners = [
        'forward',
        'closeModal',
    ];

    public function mount()
    {
        /** User Information */
        $this->user = session('user');
        $this->office = $this->user['office']['id'];
        /** End User Information */

        /** Filter Records last 1 month */
        $this->startDate = Carbon::now()->subMonth(1)->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');

        $categories_obj = Category::where('name', 'like', '%' . 'Payment' . '%')->select('id')->get();
        foreach ($categories_obj->toArray() as $value) {
            $this->categories_array[] = $value['id'];
        }

        /** API - Load with error handling */
        $this->checkApiConnection();
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
            return false;
        }

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

    /** Forward Documents */
    public function modalForwardDocument()
    {
        $this->alert('warning', 'Forward ' . count($this->selected_item) . ' Payment?', [
            'position' => 'center',
            'toast' => true,
            'timer' => null,
            'showConfirmButton' => true,
            'confirmButtonText' => 'Confirm',
            'onConfirmed' => 'forward',
            'confirmButtonColor' => '#059669',
            'showCancelButton' => true,
            'cancelButtonText' => 'Cancel',
            'onDismissed' => 'closeModal'
        ]);
    }

    public function forward()
    {
        Arr::map($this->selected_item, function ($item) {
            $data = $this->validate([
                'assignedTo' => 'required',
                'endorsedTo' => '',
                'remarks' => ''
            ]);

            Document::find($item)->update([
                'assigned_to' => $data['assignedTo'],
                'endorsed_to' => $data['endorsedTo'],
                'status' => 'For Receiving'
            ]);

            $document = Document::find($item);
            $doc_type = is_object($document) && $document->is_bundle ? 'Bundle Payment' : 'Payment';
            $lookUpOffice = $this->lookUpOffice($document->assigned_to);

            // Forward Log
            Log::create([
                'action_id' => Action::firstWhere('name', 'Forwarded')->id,
                'document_id' => $document->id,
                'user_id' => $this->user['id'],
                'office_id' => $this->office,
                'assigned_to' => $this->office,
                'endorsed_to' => $data['endorsedTo'],
                'description' => $doc_type . " forwarded to " . $lookUpOffice['name'] . " for appropriate action.",
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
                'endorsed_to' => $data['endorsedTo'],
                'description' => $doc_type . " has been transferred and is to be received by " . $lookUpOffice['name'],
                'remarks' => $data['remarks']
            ]);

            //Add log for Documents forwarded together with this bundle
            $this->attachments = Document::where('assigned_to', $this->office)->where('status', 'On Process')->where('bundle_id', $item)->orderBy('created_at', 'DESC')->get();

            if ($this->attachments) {
                foreach ($this->attachments as $attachment) {

                    Document::find($attachment->id)->update([
                        'assigned_to' => $data['assignedTo'],
                        'endorsed_to' => $data['endorsedTo'],
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
                        'endorsed_to' => $data['endorsedTo'],
                        'description' => $doc_type . " forwarded to " . $lookUpOffice['name'] . " for appropriate action.",
                        'remarks' => $data['remarks']
                    ]);

                    Sleep::for(2)->seconds();

                    // For Receiving Log
                    Log::create([
                        'action_id' => Action::where('name', 'For Receiving')->first()->id,
                        'document_id' => $attachment->id,
                        'bundle_id' => $document->id,
                        'user_id' => $this->user['id'],
                        'office_id' => $this->office,
                        'assigned_to' => $data['assignedTo'],
                        'endorsed_to' => $data['endorsedTo'],
                        'description' => "Bundle (" . $document->control_no . ")" . " has been transferred and is to be received by " . $lookUpOffice['name'] . ".",
                        'remarks' => $data['remarks']
                    ]);
                }
            }

            return $this->redirect(MyPayments::class);
        });

        $this->showAlert($type = 'success', $message = 'successfully forwarded!');
    }
    /** End of Forward Document */

    /** Miscellanous Functions */
    #[On('closeModal')]
    public function closeModal()
    {
        return $this->redirect(MyPayments::class);
    }

    public function showAlert($type, $message)
    {
        $this->alert($type, 'Payment ' . $message, [
            'position' => 'top-end',
            'timer' => 10000,
            'toast' => true
        ]);
    }

    public function updatedSelectAll($value)
    {
        if (empty($this->selected_filter)) {
            $this->alert('error', 'Please select a status first!', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true
            ]);
            return;
        }

        $this->selected_item = $value ? Document::whereNull('bundle_id')
            ->where('office_id', $this->office)
            ->whereIn('status', $this->selected_filter)
            ->when($this->categories_array, function ($query) {
                $query->whereIn('category_id', $this->categories_array);
            })
            ->when($this->startDate, function ($query) {
                $query->whereBetween('created_at', [Carbon::parse($this->startDate), Carbon::parse($this->endDate)->addDay()]);
            })
            ->pluck('id')->toArray() : [];
    }

    public function canForwardSelected()
    {
        if (empty($this->selected_item)) {
            return false;
        }

        // Check if all selected documents have 'Created' status
        $totalSelected = count($this->selected_item);
        $createdCount = Document::whereIn('id', $this->selected_item)
            ->where('status', 'Created')
            ->count();

        return $totalSelected === $createdCount;
    }

    public function canGenerateSelected()
    {
        if (empty($this->selected_item)) {
            return false;
        }

        // Check if all selected documents have 'For Receiving' status
        $totalSelected = count($this->selected_item);
        $createdCount = Document::whereIn('id', $this->selected_item)
            ->where('status', 'For Receiving')
            ->count();

        return $totalSelected === $createdCount;
    }

    public function completeName()
    {
        return $this->user['firstName'] . ' ' . $this->user['lastName'] . ' ' . $this->user['suffix'];
    }

    public function filterUser($id)
    {
        $this->id = $id;

        $result = array_filter($this->employees, function ($employee) {
            return $employee['id'] == $this->id;
        });

        $result = array_values($result); // reindex array
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

    public function colorIndicator($status)
    {
        switch ($status) {
            case 'Created':
                return "bg-gray-100";
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
    /** End of Miscellanous Functions */

    public function render()
    {
        $documents = Document::query()
            ->when($this->search, function ($query) {
                $query->where('control_no', 'like', '%' . $this->search . '%')
                    ->orWhere('subject', 'like', '%' . $this->search . '%');
            })
            ->when($this->selected_filter, function ($query) {
                $query->whereIn('status', $this->selected_filter);
            })
            ->when($this->categories_array, function ($query) {
                $query->whereIn('category_id', $this->categories_array);
            })
            ->when($this->startDate, function ($query) {
                $query->whereBetween('created_at', [Carbon::parse($this->startDate), Carbon::parse($this->endDate)->addDay()]);
            })
            ->where('office_id', $this->office)
            ->orderBy('created_at', 'DESC')->paginate(10);

        return view(
            'livewire.inbox.my-payments',
            [
                'documents' => $documents
            ]
        );
    }
}
