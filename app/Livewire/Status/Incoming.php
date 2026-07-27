<?php

namespace App\Livewire\Status;

use App\Models\Action;
use App\Models\Category;
use App\Models\Document;
use App\Models\Log;
use App\Services\ApiService;
use Carbon\Carbon;
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

        /**
         * No default date range.
         *
         * This used to default to the last quarter, which silently hid every
         * For Receiving / Returned document older than 3 months and made this table
         * disagree with the sidebar badge, which has never been date-bounded. A
         * status queue must show the whole queue; the date inputs remain available
         * for narrowing on demand.
         */

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

    /**
     * Single source of truth for "which documents belong on this screen".
     *
     * render(), updatedSelectAll() and receive() all build from this, so the
     * select-all set can never drift from the rows the user can actually see.
     * Previously each one hand-rolled its own filter list and they fell out of
     * sync, letting select-all pick up documents outside the active category and
     * date filters.
     */
    private function baseQuery()
    {
        return Document::query()
            ->whereNull('bundle_id')
            ->where('assigned_to', $this->office)
            ->whereIn('status', ['For Receiving', 'Returned'])
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
            /** Bounds applied independently so one blank input still filters sanely */
            ->when($this->startDate, function ($query) {
                $query->where('created_at', '>=', Carbon::parse($this->startDate)->startOfDay());
            })
            ->when($this->endDate, function ($query) {
                $query->where('created_at', '<=', Carbon::parse($this->endDate)->endOfDay());
            });
    }

    /**
     * Drop any pending selection. Called whenever the visible result set changes
     * so previously selected (now hidden) documents cannot be acted on.
     */
    private function clearSelection()
    {
        $this->selected_item = [];
        $this->selectAll = false;
    }

    /**
     * Reset pagination whenever a filter changes so results never land on an
     * out-of-range page, and clear the selection so it always reflects the
     * currently visible rows.
     */
    public function updatedSearch()
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedStartDate()
    {
        $this->resetPage();
        $this->clearSelection();
    }

    public function updatedEndDate()
    {
        $this->resetPage();
        $this->clearSelection();
    }

    /** Has the user actively narrowed the list? */
    private function hasActiveFilter(): bool
    {
        return filled($this->search)
            || !empty($this->selectFilter)
            || filled($this->startDate)
            || filled($this->endDate);
    }

    /**
     * Scope of a select-all click.
     *
     * Filtering is an explicit, bounded narrowing, so select-all takes the whole
     * filtered set across pages. With no filter it takes only the current page - an
     * office can hold over a thousand documents awaiting receipt, and an unfiltered
     * click must not be able to receive the entire queue at once.
     */
    private function selectableIds(): array
    {
        $query = $this->baseQuery()->orderBy('created_at', 'ASC');

        if ($this->hasActiveFilter()) {
            return $query->pluck('id')->all();
        }

        /** forPage() mirrors paginate()'s offset, so this is exactly the visible page */
        return $query->forPage(max(1, (int) $this->getPage()), 50)->pluck('id')->all();
    }

    /** Multiple Receive */
    public function updatedSelectAll($value)
    {
        $this->selected_item = $value ? $this->selectableIds() : [];
    }

    /**
     * Keep the header checkbox honest about the row checkboxes.
     *
     * Without this, unticking a single row left "select all" visually ticked, and
     * clicking it again did nothing because Livewire skips updated* hooks when the
     * value has not changed - so the row could not be re-added. A single EXISTS
     * query asks whether anything in the filtered set is still unselected.
     */
    public function updatedSelectedItem()
    {
        if (empty($this->selected_item)) {
            $this->selectAll = false;

            return;
        }

        $selectable = $this->selectableIds();

        $this->selectAll = !empty($selectable)
            && empty(array_diff($selectable, $this->selected_item));
    }

    /**
     * Resolve an office id to its office name.
     *
     * array_filter preserves the original keys, so the previous $result[$id - 1]
     * lookup only worked while officeList happened to be id-ordered and gap-free;
     * a deactivated or reordered office threw "Undefined array key". Reindex and
     * take the first match instead, mirroring filterOffice() below. The argument is
     * now authoritative — it used to be silently overridden by $this->assigned_to.
     */
    public function lookUpOffice($assigned_to)
    {
        $this->selected_office = $assigned_to;

        $result = array_values(array_filter($this->responseOffices['officeList'] ?? [], function ($office) {
            return isset($office['id']) && $office['id'] == $this->selected_office;
        }));

        return $result[0]['officeName'] ?? '';
    }

    public function receive()
    {
        /**
         * checkApiConnection() nulls out responseOffices and alerts on failure, so
         * carrying on would only crash later in lookUpOffice(). Bail out instead.
         */
        if (!$this->checkApiConnection()) {
            return;
        }

        /**
         * Re-validate the selection against the current filters before touching
         * anything. Guards against a stale selection - filters changed after
         * selecting, another user already received the document, or client-side
         * tampering - so we never act on rows outside the visible set.
         */
        $documentIds = $this->baseQuery()
            ->whereIn('id', $this->selected_item)
            ->pluck('id')
            ->toArray();

        if (empty($documentIds)) {
            $this->clearSelection();

            $this->alert('warning', 'Nothing to receive. The selected document(s) are no longer available.', [
                'position' => 'top-end',
                'timer' => 10000,
                'toast' => true
            ]);

            return;
        }

        $receivedActionId = Action::firstWhere('name', 'Received')?->id;

        if (!$receivedActionId) {
            $this->alert('error', 'The "Received" action is missing. Contact the system administrator.', [
                'position' => 'center',
                'toast' => true,
                'timer' => null,
                'showConfirmButton' => true,
                'confirmButtonText' => 'OK',
                'confirmButtonColor' => '#dc2626',
            ]);

            return;
        }

        /** Receiving office name, identical for every document in the batch */
        $lookUpOffice = $this->lookUpOffice($this->office);

        /**
         * One transaction for the whole batch. Previously each document had its own
         * transaction inside the loop, so a failure partway through committed a
         * partial receive that could not be undone from the UI.
         */
        DB::transaction(function () use ($documentIds, $receivedActionId, $lookUpOffice) {
            /** Loop Document item selected */
            foreach ($documentIds as $item) {
                $document = Document::find($item);

                if (!$document) {
                    continue;
                }

                $doc_type = $document->is_bundle ? 'Bundle' : 'Document';

                $document->update([
                    'assigned_to' => $this->office,
                    'status' => 'On Process'
                ]);

                Log::create([
                    'action_id' => $receivedActionId,
                    'document_id' => $document->id,
                    'user_id' => $this->user['id'],
                    'office_id' => $this->office,
                    'assigned_to' => $this->office,
                    'description' => $doc_type . " (" . $document->control_no . ") has been received and being process by " . $lookUpOffice . "."
                ]);

                /**
                 * Loop Attachments. 'Returned' is included because returning a bundle
                 * marks its children 'Returned' too - filtering on 'For Receiving'
                 * alone stranded them while the parent moved to 'On Process'.
                 */
                $this->attachments = Document::where('assigned_to', $this->office)
                    ->whereIn('status', ['For Receiving', 'Returned'])
                    ->where('bundle_id', $item)
                    ->orderBy('created_at', 'DESC')
                    ->get();

                foreach ($this->attachments as $attachment) {
                    $attachment->update([
                        'assigned_to' => $this->office,
                        'status' => 'On Process'
                    ]);

                    Log::create([
                        'action_id' => $receivedActionId,
                        'document_id' => $attachment->id,
                        'bundle_id' => $document->id,
                        'user_id' => $this->user['id'],
                        'office_id' => $this->office,
                        'assigned_to' => $this->office,
                        'description' => $doc_type . " (" . $document->control_no . ") has been received and being process by " . $lookUpOffice . "."
                    ]);
                }
            }
        });

        $this->clearSelection();

        /**
         * flash() stores the toast in the session so it survives the redirect. The
         * previous alert() dispatched a browser event that the redirect discarded,
         * so the success message never actually appeared. flash() also returns its
         * own redirect response, which we ignore - Livewire's redirect() below
         * performs the navigation.
         */
        $this->flash('success', 'Document successfully received!', [
            'position' => 'top-end',
            'timer' => 10000,
            'toast' => true
        ]);

        $this->redirect(Pending::class);
    }
    /** End of Multiple Receive */

    /** Miscellanous Functions */
    #[On('closeModal')]
    public function closeModal()
    {
        return $this->redirect(Incoming::class);
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
        $this->clearSelection();

        /**
         * An empty $type is the "All Documents" choice — clear the filter rather than
         * matching every category. The old like '%%' pluck returned every category id,
         * which silently excluded documents with a null category_id and also counted
         * as an active filter for select-all scoping.
         */
        return $this->selectFilter = $type === ''
            ? []
            : Category::where('name', 'like', '%' . $type . '%')->pluck('id')->toArray();
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
        $documents = $this->baseQuery()
            ->with(['logs', 'category'])
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
