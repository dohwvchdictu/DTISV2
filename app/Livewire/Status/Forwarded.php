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

    public function mount()
    {
        /** User Information */
        $this->user = session('user');
        $this->office = $this->user['office']['id'];
        /** End User Information */

        /**
         * No default date range.
         *
         * The window is now applied to the table as well as to select-all, so a
         * one-day default would hide almost everything on load. The two date inputs
         * remain for narrowing on demand, applied by the Filter button.
         */
    }

    /**
     * Runs on every request (before mount and before public-prop hydration).
     * Reloads the protected directory data from cache so it is available for
     * render and action methods without bloating the Livewire snapshot.
     */
    public function boot()
    {
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

        $this->offices = app(ApiService::class)->getActiveOffices($this->responseOffices);

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

    /**
     * The processing logs this screen is built on: routed through this office, and
     * — when a window is set — processed inside it.
     *
     * The date/time window belongs on the log, not on documents.created_at. This
     * table renders the log's timestamp (see filterLog), so filtering the document's
     * creation date meant the column users read and the column the filter tested
     * were different ones: about 64% of rows display a date on a different calendar
     * day from the one the filter used. A document processed at 5:17 PM but created
     * at 1:42 PM disappeared the moment its own displayed time was typed into the
     * filter.
     *
     * Shared by the existence check, the eager load and the per-row lookups so the
     * set that matches, the set that renders and the set select-all collects cannot
     * drift apart again.
     */
    private function processedLogScope($query)
    {
        return $query
            ->where('assigned_to', $this->office)
            ->whereIn('action_id', [3, 5])
            /** Bounds applied independently so one blank input still filters sanely */
            ->when($this->startDate, function ($q) {
                $q->where('created_at', '>=', Carbon::parse($this->startDate)->startOfDay());
            })
            ->when($this->endDate, function ($q) {
                $q->where('created_at', '<=', Carbon::parse($this->endDate)->endOfDay());
            });
    }

    /**
     * Which documents belong on this screen: routed through this office, top-level,
     * plus the active search and date window.
     *
     * The date/time window used to be applied *only* inside select-all while render()
     * ignored it entirely, so the two showed different sets and select-all could put
     * documents into a logbook that were never on screen.
     */
    private function baseQuery()
    {
        return Document::query()
            ->whereNull('bundle_id')
            ->whereHas('logs', fn ($query) => $this->processedLogScope($query))
            ->when($this->search, function ($query) {
                // Properly scope the OR conditions to avoid query issues
                $query->where(function ($q) {
                    $q->where('control_no', 'like', '%' . $this->search . '%')
                        ->orWhere('subject', 'like', '%' . $this->search . '%');
                });
            });
    }

    /**
     * What may actually be put into a logbook: the on-screen set, narrowed to the
     * rows whose checkbox is enabled. Rows in any other status are displayed for
     * history but cannot be selected.
     */
    private function eligibilityQuery()
    {
        return Document::query()
            ->whereNull('bundle_id')
            ->where('status', 'For Receiving')
            ->whereHas('logs', function ($query) {
                $query->where('assigned_to', $this->office)
                    ->whereIn('action_id', [3, 5]);
            });
    }

    /** Has the user actively narrowed the list? */
    private function hasActiveFilter(): bool
    {
        return filled($this->search)
            || filled($this->startDate)
            || filled($this->endDate);
    }

    /**
     * Scope of a select-all click: the whole filtered set when a filter is active,
     * otherwise only the current page. Restricted to selectable rows either way.
     */
    private function selectableIds(): array
    {
        $query = $this->baseQuery()
            ->where('status', 'For Receiving')
            ->orderBy('created_at', 'DESC');

        if ($this->hasActiveFilter()) {
            return $query->pluck('id')->all();
        }

        /** forPage() mirrors paginate()'s offset, so this is exactly the visible page */
        return $query->forPage(max(1, (int) $this->getPage()), 50)->pluck('id')->all();
    }

    /** selected_item arrives from the DOM as strings; the database returns integers. */
    private function normalizedSelection(): array
    {
        return array_values(array_unique(array_map('intval', (array) $this->selected_item)));
    }

    /** Drop the whole selection. Bound to the toolbar's Clear button. */
    public function clearSelection()
    {
        $this->selected_item = [];
        $this->selectAll = false;
    }

    /**
     * Called whenever a filter changes. Resets pagination (no such hook existed) and
     * unticks "select all", but keeps hand-picked rows so a logbook can be assembled
     * across several searches.
     */
    private function filterChanged()
    {
        $this->resetPage();
        $this->selectAll = false;
    }

    public function updatedSearch()
    {
        $this->filterChanged();
    }

    /**
     * The date range is applied on demand, from the Filter button, instead of on every
     * change. wire:model is deferred in Livewire 3, so both inputs travel to the server
     * together when this runs - picking a start date no longer runs a query before the
     * end date has been chosen.
     */
    public function applyFilter()
    {
        $this->filterChanged();
    }

    public function clearFilter()
    {
        $this->reset('startDate', 'endDate');
        $this->filterChanged();
    }

    public function updatedSelectAll($value)
    {
        if (!$value) {
            $this->selected_item = [];

            return;
        }

        /** Merge, so select-all does not discard rows picked under an earlier search */
        $this->selected_item = array_values(array_unique(
            array_merge($this->normalizedSelection(), $this->selectableIds())
        ));
    }

    /** Keep the header checkbox honest about the row checkboxes. */
    public function updatedSelectedItem()
    {
        $this->selected_item = $this->normalizedSelection();

        if (empty($this->selected_item)) {
            $this->selectAll = false;

            return;
        }

        $selectable = $this->selectableIds();

        $this->selectAll = !empty($selectable)
            && empty(array_diff($selectable, $this->selected_item));
    }

    /**
     * The documents currently selected, resolved for the review panel — the chance to
     * check exactly what will go into the logbook before generating it.
     */
    public function selectedDocuments()
    {
        if (empty($this->selected_item)) {
            return collect();
        }

        $selection = $this->normalizedSelection();

        /** Newest pick first, so whatever was just ticked is the top row */
        $order = array_flip($selection);

        return $this->eligibilityQuery()
            ->with('category')
            ->whereIn('id', $selection)
            ->get()
            ->sortByDesc(fn ($document) => $order[$document->id] ?? -1)
            ->values();
    }

    /** Remove a single document from the selection, from inside the review panel. */
    public function deselect($id)
    {
        $this->selected_item = array_values(
            array_diff($this->normalizedSelection(), [(int) $id])
        );

        $this->updatedSelectedItem();
    }

    public function canGenerateSelected()
    {
        $selection = $this->normalizedSelection();

        if (empty($selection)) {
            return false;
        }

        // Every selected document must still be selectable on this screen
        return count($selection) === $this->eligibilityQuery()
            ->whereIn('id', $selection)
            ->count();
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
        $log = $this->processedLogScope(
            Log::where('document_id', is_object($document) ? $document->id : $document)
        )->orderBy('created_at', 'ASC')->first();

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
            $log = $this->processedLogScope(
                Log::where('document_id', is_object($document) ? $document->id : $document)
            )->orderBy('created_at', 'ASC')->first();
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
        $documents = $this->baseQuery()
            // Eager load logs + category to prevent N+1 queries.
            // ASC so ->first() on the loaded relation returns the earliest matching
            // log — the same record the old per-row query (no order) displayed.
            ->with(['category', 'logs' => function ($query) {
                $this->processedLogScope($query)->orderBy('created_at', 'ASC');
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
