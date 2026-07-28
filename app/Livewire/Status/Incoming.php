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
    public ?int $document_id = null;
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
     * What this office may legally receive, regardless of what is on screen.
     *
     * These are the invariants: the document is assigned here, is still awaiting
     * receipt, and is a top-level document rather than an attachment. receive()
     * validates against *this*, not against baseQuery() - whether a document happens
     * to match the search box the user has typed right now says nothing about
     * whether receiving it is safe, and validating against the filters made it
     * impossible to build a batch across several searches.
     */
    private function eligibilityQuery()
    {
        return Document::query()
            ->whereNull('bundle_id')
            ->where('assigned_to', $this->office)
            ->whereIn('status', ['For Receiving', 'Returned']);
    }

    /**
     * Which documents belong on screen right now: eligibility plus the active
     * filters. Used by render() and by select-all, so the select-all set can never
     * drift from the rows the user can actually see.
     */
    private function baseQuery()
    {
        return $this->eligibilityQuery()
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

    /** Drop the whole selection. Bound to the toolbar's Clear button. */
    public function clearSelection()
    {
        $this->selected_item = [];
        $this->selectAll = false;
    }

    /**
     * The documents currently selected, resolved for the review panel.
     *
     * Built from eligibilityQuery() rather than baseQuery() on purpose: the whole
     * point of the panel is to show picks that are off-screen because they were made
     * under a different search. Anything that has since stopped being eligible simply
     * drops out, which matches what receive() would do with it.
     */
    public function selectedDocuments()
    {
        if (empty($this->selected_item)) {
            return collect();
        }

        $selection = $this->normalizedSelection();

        /**
         * Newest pick first. selected_item preserves the order rows were ticked, so a
         * document's position in that array is its selection order. Sorting on it means
         * whatever was just added appears at the top - the panel's main job is
         * confirming that a pick registered, now that picks come from several different
         * searches - and a select-all merge appends its ids, so it lands as one block.
         *
         * Sorted in PHP against the array rather than in SQL: the database has no idea
         * what order the user clicked in, and this costs no extra query.
         */
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

    /**
     * Called whenever a filter changes.
     *
     * Resets pagination so results never land on an out-of-range page, and unticks
     * "select all" because it described the previous result set - but deliberately
     * keeps the hand-picked rows. Searching for one document, ticking it, then
     * searching for another is a normal way to assemble a batch; clearing here made
     * only the last pick survive. The toolbar shows a running count so a carried-over
     * selection is never invisible.
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

    public function updatedStartDate()
    {
        $this->filterChanged();
    }

    public function updatedEndDate()
    {
        $this->filterChanged();
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

    /**
     * selected_item arrives from the DOM as strings while the database returns
     * integers. Normalise so array_diff and array_unique compare like with like.
     */
    private function normalizedSelection(): array
    {
        return array_values(array_unique(array_map('intval', (array) $this->selected_item)));
    }

    /** Multiple Receive */
    public function updatedSelectAll($value)
    {
        if (!$value) {
            $this->selected_item = [];

            return;
        }

        /**
         * Merge rather than replace: select-all adds everything currently in scope
         * without discarding rows the user picked under an earlier search.
         */
        $this->selected_item = array_values(array_unique(
            array_merge($this->normalizedSelection(), $this->selectableIds())
        ));
    }

    /**
     * Keep the header checkbox honest about the row checkboxes.
     *
     * Without this, unticking a single row left "select all" visually ticked, and
     * clicking it again did nothing because Livewire skips updated* hooks when the
     * value has not changed - so the row could not be re-added. "All selected" means
     * every row currently in scope is in the selection; the selection may legitimately
     * hold more than that, carried over from an earlier search.
     */
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
         * Re-validate against eligibility, not against the active filters. This still
         * blocks anything that is not ours, is no longer awaiting receipt, or was
         * received by someone else in the meantime - while allowing a batch that was
         * assembled across several different searches. Filtering on baseQuery() here
         * silently dropped every pick that did not match the search box as it stood
         * at the moment of submission.
         */
        $documentIds = $this->eligibilityQuery()
            ->whereIn('id', $this->normalizedSelection())
            ->pluck('id')
            ->all();

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
        $this->filterChanged();

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

    /**
     * Resolve a user id to a display name.
     *
     * The ?? [] guard is load-bearing: checkApiConnection() sets responseEmployees to
     * null when the directory API is down, and this method is called once per row
     * from the Blade. Without it, array_filter(null, ...) threw a TypeError and an
     * outage took out the whole table instead of degrading to "Unknown User".
     */
    public function filterUser($encoded_user)
    {
        $this->endorsedID = $encoded_user;

        $result = array_values(array_filter($this->responseEmployees['employeesList'] ?? [], function ($employee) {
            return isset($employee['id']) && $employee['id'] == $this->endorsedID;
        }));

        if (empty($result)) {
            return 'Unknown User';
        }

        $findUser = $result[0];

        return trim($findUser['firstName'] . ' ' . $findUser['lastName'] . ' ' . ($findUser['suffix'] ?? ''));
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
