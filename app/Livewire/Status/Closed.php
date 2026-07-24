<?php

namespace App\Livewire\Status;

use App\Models\Document;
use App\Models\Log;
use App\Services\ApiService;
use Livewire\Attributes\Title;
use Livewire\Component;

class Closed extends Component
{
    #[Title('Closed Documents | Document Tracking Information System')]
    /** Constant Variables */
    public $user = [];
    public $office;
    /**
     * Large, rarely-changing directory data. Kept protected so it is NOT
     * serialized into the Livewire snapshot on every request; reloaded from
     * cache each request via boot().
     */
    protected $responseEmployees;
    protected $responseOffices;
    protected $employees = [];
    protected $offices = [];
    public $id;

    /** Search & Filter Variables*/
    public $search = '';

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
        $this->user = session('user');
        $this->office = $this->user['office']['id'];
        /** End User Information */
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

        $this->offices = app(ApiService::class)->getActiveOffices($this->responseOffices);

        return true;
    }

    /** Miscellanous Functions */
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

    public function filterLog($document)
    {
        // If $document is a Document model instance with loaded logs
        if ($document instanceof Document && $document->relationLoaded('logs')) {
            $log = $document->logs->first();
            return $log ? $log->created_at : '';
        }

        // Fallback to querying if not eager-loaded (for backward compatibility)
        $log = Log::where('document_id', is_object($document) ? $document->id : $document)
            ->where('action_id', 5)
            ->where('assigned_to', $this->office)
            ->first();

        return $log ? $log->created_at : '';
    }

    public function filterUser($document)
    {
        // Use the eager-loaded logs when given a Document model to avoid a per-row query
        if ($document instanceof Document && $document->relationLoaded('logs')) {
            $log = $document->logs->first();
        } else {
            // Fallback to querying if not eager-loaded (for backward compatibility)
            $log = Log::where('document_id', is_object($document) ? $document->id : $document)
                ->where('action_id', 5)
                ->where('assigned_to', $this->office)
                ->first();
        }

        if (!$log) {
            return '';
        }
        $this->id = $log->user_id;

        $result = array_filter($this->employees, function ($employee) {
            return $employee['id'] == $this->id;
        });

        $userList = array_values($result);
        if (!isset($userList[0])) {
            return '';
        }
        $userInfo = $userList[0];
        return $userInfo['firstName'] . ' ' . $userInfo['lastName'] . ' ' . $userInfo['suffix'];
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
                    ->where('action_id', 5);
            })
            // Eager load logs + category to prevent N+1 queries.
            // ASC so ->first() on the loaded relation returns the earliest matching
            // log — the same record the old per-row query (no order) displayed.
            ->with(['category', 'logs' => function ($query) {
                $query->where('assigned_to', $this->office)
                    ->where('action_id', 5)
                    ->orderBy('created_at', 'ASC');
            }])
            ->orderBy('created_at', 'DESC')
            ->paginate(50);

        return view(
            'livewire.status.closed',
            [
                'documents' =>  $documents
            ]
        );
    }
}
