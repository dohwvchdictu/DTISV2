<?php

namespace App\Livewire\Status;

use App\Models\Document;
use App\Models\Log;
use App\Services\ApiService;
use Carbon\Carbon;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class Forwarded extends Component
{
    use WithPagination;

    #[Title('Processed Documents | Document Tracking Information System')]
    /** Constant Variables */
    public $user = [];
    public $office;
    public $responseEmployees;
    public $responseOffices;
    public $employees = [];
    public $offices = [];
    public $id;

    /** Search & Filter Variables*/
    public $search = '';

    /** Multiple Selection */
    public $selected_item = [];
    public $selectAll = false;
    public $assignedTo;
    public $endorsedTo;
    public $categories_array = [];

    /** Track Document  Variables */
    public $logs = [];
    public $control_no;
    public $trackLogs = [];
    public $turnaround_time;
    public $selected_office;
    public $dt1;
    public $dt2;

    /** Filter Date Variables */
    public $startDate;
    public $endDate;
    public $startTime;
    public $endTime;

    public function mount()
    {
        /** User Information */
        $this->user = session('user');
        $this->office = $this->user['office']['id'];
        /** End User Information */

        /** Filter Records Yesterday */
        $this->startDate = Carbon::now()->subDay(1)->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');

        $this->startTime = Carbon::now()->subDay(1)->format('h:i');
        $this->endTime = Carbon::now()->format('h:i');

        // Fetch API data with error handling
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

        return true;
    }

    public function lookUpOffice($assigned_to)
    {
        $this->selected_office = $this->assignedTo ?? $assigned_to;

        // Validate officeList exists
        if (!isset($this->responseOffices['officeList']) || !is_array($this->responseOffices['officeList'])) {
            return 'Unknown Office';
        }

        $result = array_filter($this->responseOffices['officeList'], function ($office) {
            return $office['id'] == $this->selected_office;
        });

        if (empty($result)) {
            return 'Unknown Office';
        }

        $findOffice = reset($result); // Get first element safely
        return $findOffice['officeName'] ?? 'Unknown Office';
    }

    /** Track Document */
    public function trackDocument(int $id)
    {
        $logs = Log::where('document_id', $id)->orderBy('created_at', 'DESC')->get();
        $document = Document::find($id);

        if (!$document || $logs->isEmpty()) {
            return redirect()->to('/status-forwarded');
        }

        // Safely get created_at timestamps
        $firstLog = $logs->firstWhere('action_id', 1);
        $lastLog = $logs->firstWhere('action_id', 5);
        
        $this->dt1 = $firstLog ? $firstLog->created_at : false;
        $this->dt2 = $lastLog ? $lastLog->created_at : Carbon::now();

        if ($this->dt1) {
            $this->turnaround_time = Carbon::parse($this->dt1)->diffInDaysFiltered(function (Carbon $date) {
                return !$date->isWeekend();
            }, $this->dt2);
        }

        $this->logs = $logs;
        $this->control_no = $document->control_no;
        
        return;
    }

    public function suffixTurnaroundTime()
    {
        return $this->turnaround_time <= 1 ? 'Day' : 'Days';
    }

    public function calculateTurnaroundTime(int $id)
    {
        $logs = Log::where('document_id', $id)->orderBy('created_at', 'DESC')->get();

        if ($logs->isEmpty()) {
            $this->turnaround_time = 0;
            return;
        }

        // Safely get created_at timestamps
        $firstLog = $logs->firstWhere('action_id', 1);
        $lastLog = $logs->firstWhere('action_id', 5);
        
        $this->dt1 = $firstLog ? $firstLog->created_at : false;
        $this->dt2 = $lastLog ? $lastLog->created_at : Carbon::now();

        if ($this->dt1) {
            $this->turnaround_time = Carbon::parse($this->dt1)->diffInDaysFiltered(function (Carbon $date) {
                return !$date->isWeekend();
            }, $this->dt2);
        } else {
            $this->turnaround_time = 0;
        }
    }
    /** End of Track Document */

    /** Miscellanous Functions */
    public function updatedSelectAll($value)
    {
        $this->selected_item = $value ? Document::whereNull('bundle_id')
            ->whereHas('logs', function ($query) {
                $query->where('assigned_to', $this->office)
                      ->whereIn('action_id', [3])
                      ->whereBetween('created_at', [Carbon::parse($this->startDate . ' ' . $this->startTime), Carbon::parse($this->endDate . ' ' . $this->endTime)->addDay()]);
            })
            // Eager load logs to prevent N+1 queries
            ->with(['logs' => function ($query) {
                $query->where('assigned_to', $this->office)
                      ->whereIn('action_id', [3])
                      ->orderBy('created_at', 'DESC');
            }])
            ->where('status', 'For Receiving')
            ->pluck('id')->toArray() : [];
    }

    // public function updatedSelectAll($value)
    // {
    //     $this->selected_item = $value ? Document::whereNull('bundle_id')
    //         ->where('office_id', $this->office)
    //         ->where('status', 'For Receiving')
    //         ->pluck('id')->toArray() : [];
    // }

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

    /**
     * Get the log timestamp for a document
     * Optimized to use eager-loaded logs if available
     */
    public function filterLog($document)
    {
        // If $document is a Document model instance with loaded logs
        if ($document instanceof Document && $document->relationLoaded('logs')) {
            $log = $document->logs->first();
            return $log ? $log->created_at : '';
        }
        
        // Fallback to querying if not eager-loaded (for backward compatibility)
        $log = Log::where('document_id', is_object($document) ? $document->id : $document)
            ->whereIn('action_id', [3, 5])
            ->where('assigned_to', $this->office)
            ->first();

        return $log ? $log->created_at : '';
    }

    /**
     * Get the user who processed a document
     * Optimized to use eager-loaded logs if available
     */
    public function filterUserProcessed($document)
    {
        // If $document is a Document model instance with loaded logs
        if ($document instanceof Document && $document->relationLoaded('logs')) {
            $log = $document->logs->first();
        } else {
            // Fallback to querying if not eager-loaded
            $log = Log::where('document_id', is_object($document) ? $document->id : $document)
                ->whereIn('action_id', [3, 5])
                ->where('assigned_to', $this->office)
                ->first();
        }

        if (!$log) {
            return '';
        }

        $userId = $log->user_id;

        // Use collection's firstWhere for better performance
        $employeeCollection = collect($this->employees);
        $userInfo = $employeeCollection->firstWhere('id', $userId);

        if (!$userInfo) {
            return '';
        }

        return trim($userInfo['firstName'] . ' ' . $userInfo['lastName'] . ' ' . ($userInfo['suffix'] ?? ''));
    }

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
    /** End of Miscellanous Functions */

    public function render()
    {
        $documents = Document::query()
            ->whereNull('bundle_id')
            ->when($this->search, function ($query) {
                // Properly scope the OR conditions to avoid query issues
                $query->where(function ($q) {
                    $q->where('control_no', 'like', '%' . $this->search . '%')
                      ->orWhere('subject', 'like', '%' . $this->search . '%');
                });
            })
            ->whereHas('logs', function ($query) {
                $query->where('assigned_to', $this->office)
                      ->whereIn('action_id', [3, 5]);
            })
            // Eager load logs to prevent N+1 queries
            ->with(['logs' => function ($query) {
                $query->where('assigned_to', $this->office)
                      ->whereIn('action_id', [3, 5])
                      ->orderBy('created_at', 'DESC');
            }])
            ->orderBy('created_at', 'DESC')
            ->paginate(50);

        return view(
            'livewire.status.forwarded',
            [
                'documents' => $documents
            ]
        );
    }
}
