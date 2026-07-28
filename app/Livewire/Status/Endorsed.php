<?php

namespace App\Livewire\Status;

use App\Models\Action;
use App\Models\Category;
use App\Models\Document;
use App\Models\Log;
use App\Services\ApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
/** App\Models\Log already owns the "Log" name here, so the facade needs an alias. */
use Illuminate\Support\Facades\Log as LogFacade;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;

class Endorsed extends Component
{
    use WithPagination;
    use LivewireAlert;

    #[Title('Endorsed Documents | Document Tracking Information System')]

    /** Constant Variables */
    public $response;
    /**
     * Large, rarely-changing directory data. Kept protected so it is NOT
     * serialized into the Livewire snapshot on every request; reloaded from
     * cache each request via boot().
     */
    protected $offices = [];
    public $user = [];
    public $office;
    public $phrase = '';
    public $passphrase = '';
    protected $responseOffices;
    protected $responseEmployees;
    protected $employees = [];
    /** Small per-office subset shown in a dropdown — must stay public for the Blade view. */
    public $subEmployees = [];
    protected $filterOfficeEmployees = [];
    public $endorsedID;
    public $statement = [
        'approved' => 'Document has been approved.',
        'signed' => 'Document has been signed.',
        'initialed' => 'Document has been initialed.',
        'checked' => 'Document has been checked.',
        'processed' => 'Document has been processed.'
    ];

    /** Search & Filter Variables*/
    public $search = '';
    public $selectFilter = [];

    /** Multiple Selection */
    public $selected_item = [];
    public $selectAll = false;
    public $assignedTo;
    public $endorseTo;
    public $endorsedToPersonnel;
    public $endorsedToOtherPersonnel;

    /** Forward Variables */
    public int $document_id;
    public $remarks;
    public $attachments;
    public $selected_office;

    /** Filter Date Variables */
    public $startDate;
    public $endDate;

    /** Listeners for Livewire Alerts */
    protected $listeners = [
        'close',
        'forward',
        'closeModal'
    ];

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

        /**
         * No default date range. A 3-month default silently hid every On Process
         * document older than a quarter - precisely the overdue ones - and made this
         * table disagree with the sidebar badge, which has never been date-bounded.
         * The date inputs remain available for narrowing on demand.
         */
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
     * What this user may legally close or forward from this screen, regardless of
     * what is on screen: endorsed to them, still On Process, in their office, and a
     * top-level document rather than an attachment.
     *
     * The bulk actions validate against *this*, not against baseQuery(). Whether a
     * document matches the search box as it stands at submission says nothing about
     * whether acting on it is safe, and validating against the filters made it
     * impossible to build a batch across several searches.
     */
    private function eligibilityQuery()
    {
        return Document::query()
            ->whereNull('bundle_id')
            ->where('assigned_to', $this->office)
            ->where('status', 'On Process')
            ->where('endorsed_to', $this->user['id']);
    }

    /**
     * Which documents belong on screen right now: eligibility plus active filters.
     * Used by render() and select-all, so the select-all set can never drift from
     * the rows the user can actually see - it previously ignored both the category
     * and the date filters.
     */
    private function baseQuery()
    {
        return $this->eligibilityQuery()
            ->when($this->search, function ($query) {
                /**
                 * Nested so the OR cannot escape its group. The previous version
                 * chained orWhere() inline against repeated scope conditions, which
                 * only produced the right rows by accident.
                 */
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

    /** Has the user actively narrowed the list? */
    private function hasActiveFilter(): bool
    {
        return filled($this->search)
            || !empty($this->selectFilter)
            || filled($this->startDate)
            || filled($this->endDate);
    }

    /**
     * Scope of a select-all click: the whole filtered set when a filter is active
     * (an explicit, bounded narrowing), otherwise only the current page so an
     * unfiltered click cannot close or forward the entire queue at once.
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

    /** Drop the whole selection. Bound to the toolbar's Clear button. */
    public function clearSelection()
    {
        $this->selected_item = [];
        $this->selectAll = false;
    }

    /**
     * The documents currently selected, resolved for the review panel. Built from
     * eligibilityQuery() because the point of the panel is to show picks that are
     * off-screen, made under a different search.
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

    /**
     * Called whenever a filter changes. Resets pagination (no such hook existed, so
     * filtering from a later page could show an empty table) and unticks "select
     * all", but keeps hand-picked rows so a batch can span several searches.
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

    /** selected_item arrives from the DOM as strings; the database returns integers. */
    private function normalizedSelection(): array
    {
        return array_values(array_unique(array_map('intval', (array) $this->selected_item)));
    }

    /** Multiple Selection */
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

    /**
     * Keep the header checkbox honest about the row checkboxes. "All selected" means
     * every row currently in scope is in the selection; the selection may hold more
     * than that, carried over from an earlier search.
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
     * Shared guard for the bulk actions: confirm the API is reachable, then
     * re-validate the selection against eligibility before anything is written.
     * Returns null when the caller must abort.
     */
    private function validatedSelection(): ?array
    {
        if (!$this->checkApiConnection()) {
            return null;
        }

        $documentIds = $this->eligibilityQuery()
            ->whereIn('id', $this->normalizedSelection())
            ->pluck('id')
            ->all();

        if (empty($documentIds)) {
            $this->clearSelection();

            $this->alert('warning', 'Nothing to act on. The selected document(s) are no longer available.', [
                'position' => 'top-end',
                'timer' => 10000,
                'toast' => true
            ]);

            return null;
        }

        return $documentIds;
    }

    /**
     * Resolve the action rows once, up front. These used to be re-queried on every
     * loop iteration with an unguarded ->id, so a missing row crashed mid-write.
     */
    private function actionIds(array $names): ?array
    {
        $ids = [];

        foreach ($names as $name) {
            $id = Action::firstWhere('name', $name)?->id;

            if (!$id) {
                $this->alert('error', 'The "' . $name . '" action is missing. Contact the system administrator.', [
                    'position' => 'center',
                    'toast' => true,
                    'timer' => null,
                    'showConfirmButton' => true,
                    'confirmButtonText' => 'OK',
                    'confirmButtonColor' => '#dc2626',
                ]);

                return null;
            }

            $ids[$name] = $id;
        }

        return $ids;
    }

    public function updatedAssignedTo($value)
    {
        $this->selected_office = $value;

        $this->subEmployees = array_filter($this->employees, function ($office) {
            return isset($office['office']['id']) && $office['office']['id'] == $this->selected_office;
        });
    }

    /**
     * Resolve an office id to its id/code/name.
     *
     * array_filter and array_map both preserve the original keys, so the old
     * $findOffice[$id - 1] lookup only worked while officeList happened to be
     * id-ordered and gap-free; a deactivated or reordered office threw "Undefined
     * array key". The API-down branch also returned a bare null while both callers
     * did $lookUpOffice['name']. Always returns an array now.
     */
    public function lookUpOffice($assigned_to)
    {
        $this->assignedTo = $assigned_to;

        $unknown = ['id' => $assigned_to, 'code' => '', 'name' => 'Unknown Office'];

        if ($this->responseOffices === null || !isset($this->responseOffices['officeList'])) {
            $this->alert('error', 'No response from API server. Check connection and try again.', [
                'position' => 'center',
                'toast' => true,
                'timer' => null,
                'showConfirmButton' => true,
                'confirmButtonText' => 'OK',
                'confirmButtonColor' => '#dc2626',
            ]);

            return $unknown;
        }

        $findOffice = array_values(array_filter($this->responseOffices['officeList'], function ($office) {
            return isset($office['id']) && $office['id'] == $this->assignedTo;
        }));

        if (!isset($findOffice[0])) {
            return $unknown;
        }

        return [
            'id' => $findOffice[0]['id'],
            'code' => $findOffice[0]['officeCode'] ?? '',
            'name' => $findOffice[0]['officeName'] ?? 'Unknown Office'
        ];
    }

    /** End of Multiple Selection */

    /** Close Document */
    public function modalCloseDocument()
    {
        $this->phrase = Str::random(8);
    }

    public function confirmCloseDocument()
    {
        $this->alert('warning', 'Close ' . count($this->selected_item) . ' Documents?', [
            'position' => 'center',
            'toast' => true,
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
        /**
         * Validate everything once, up front. remarks used to be validated inside the
         * loop - and after the first document had already been set to Closed - so a
         * failure part-way left earlier documents closed with no log entry.
         */
        $data = $this->validate([
            'phrase' => 'required',
            'passphrase' => 'required',
            'remarks' => 'required'
        ]);

        if ($data['phrase'] !== $data['passphrase']) {
            $this->alert('error', 'Document unsuccessfully closed, please enter the characters correctly!', [
                'position' => 'top-end',
                'timer' => 10000,
                'toast' => true
            ]);

            return;
        }

        $documentIds = $this->validatedSelection();

        if ($documentIds === null) {
            return;
        }

        $actions = $this->actionIds(['Closed']);

        if ($actions === null) {
            return;
        }

        /** This method previously had no transaction at all */
        DB::transaction(function () use ($documentIds, $actions, $data) {
            foreach ($documentIds as $item) {
                $document = Document::find($item);

                if (!$document) {
                    continue;
                }

                $doc_type = $document->is_bundle ? 'Bundle' : 'Document';
                $lookUpOffice = $this->lookUpOffice($document->assigned_to);

                $document->update([
                    'status' => 'Closed'
                ]);

                Log::create([
                    'action_id' => $actions['Closed'],
                    'document_id' => $document->id,
                    'user_id' => $this->user['id'],
                    'office_id' => $this->office,
                    'assigned_to' => $this->office,
                    'description' => $doc_type . " (" . $document->control_no . ") has been acted upon and closed by " . $lookUpOffice['name'],
                    'remarks' => $data['remarks']
                ]);

                /** Calculate Turn Around Time — reads the Closed log written above */
                $document->update([
                    'turnaroundtime' => $this->calculateTurnaroundTime($document->id)
                ]);

                $attachments = Document::where('assigned_to', $this->office)
                    ->where('status', 'On Process')
                    ->where('bundle_id', $item)
                    ->orderBy('created_at', 'DESC')
                    ->get();

                foreach ($attachments as $attachment) {
                    $attachment->update([
                        'status' => 'Closed'
                    ]);

                    //Closed Log
                    Log::create([
                        'action_id' => $actions['Closed'],
                        'document_id' => $attachment->id,
                        'bundle_id' => $document->id,
                        'user_id' => $this->user['id'],
                        'office_id' => $this->office,
                        'assigned_to' => $attachment->assigned_to,
                        'description' => "Bundle (" . $document->control_no . ") has been acted upon and closed by " . $lookUpOffice['name'] . ".",
                        'remarks' => $data['remarks']
                    ]);

                    /** Calculate Turn Around Time */
                    $attachment->update([
                        'turnaroundtime' => $this->calculateTurnaroundTime($attachment->id)
                    ]);
                }
            }
        });

        $this->clearSelection();

        /** flash() survives the redirect; a dispatched alert did not */
        return $this->flash('success', 'Document successfully closed!', [
            'position' => 'top-end',
            'timer' => 10000,
            'toast' => true
        ], '/status-endorsed');
    }
    /** End of Document */

    /** Forward Documents */
    public function modalForwardDocument()
    {
        $this->alert('warning', 'Forward ' . count($this->selected_item) . ' Documents?', [
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
        /** Validate once, up front — this used to run inside the loop */
        $data = $this->validate([
            'assignedTo' => 'required',
            'endorsedToOtherPersonnel' => '',
            'remarks' => ''
        ]);

        $documentIds = $this->validatedSelection();

        if ($documentIds === null) {
            return;
        }

        $actions = $this->actionIds(['Forwarded', 'For Receiving']);

        if ($actions === null) {
            return;
        }

        /**
         * Destination office, identical for every document in the batch. The old code
         * read it back from $document->assigned_to after updating it, arriving at the
         * same value by a far less obvious route.
         */
        $lookUpOffice = $this->lookUpOffice($data['assignedTo']);

        /**
         * One transaction for the whole batch; this method previously had none.
         *
         * The two Sleep::for(2)->seconds() calls that sat between the log writes are
         * gone. They cost two seconds per document plus two per attachment - a
         * fifty-document forward blocked the request for over three minutes - and
         * inside a transaction they would hold row locks for the entire nap. The logs
         * are ordered by insertion, so nothing depended on the delay.
         */
        DB::transaction(function () use ($documentIds, $actions, $data, $lookUpOffice) {
            foreach ($documentIds as $item) {
                $document = Document::find($item);

                if (!$document) {
                    continue;
                }

                $doc_type = $document->is_bundle ? 'Bundle' : 'Document';

                //Documents forwarded together with this bundle, read while they are
                //still keyed to the current office
                $attachments = Document::where('assigned_to', $this->office)
                    ->where('status', 'On Process')
                    ->where('bundle_id', $item)
                    ->orderBy('created_at', 'DESC')
                    ->get();

                $document->update([
                    'assigned_to' => $data['assignedTo'],
                    'endorsed_to' => $data['endorsedToOtherPersonnel'],
                    'status' => 'For Receiving'
                ]);

                //Forwarded Log by the current office
                Log::create([
                    'action_id' => $actions['Forwarded'],
                    'document_id' => $document->id,
                    'user_id' => $this->user['id'],
                    'office_id' => $this->office,
                    'assigned_to' => $this->office,
                    'endorsed_to' => $data['endorsedToOtherPersonnel'],
                    'description' => $doc_type . " forwarded to " . $lookUpOffice['name'] . " for appropriate action.",
                    'remarks' => $data['remarks']
                ]);

                // For Receiving Log for the next office
                Log::create([
                    'action_id' => $actions['For Receiving'],
                    'document_id' => $document->id,
                    'user_id' => $this->user['id'],
                    'office_id' => $this->office,
                    'assigned_to' => $data['assignedTo'],
                    'endorsed_to' => $data['endorsedToOtherPersonnel'],
                    'description' => $doc_type . " has been transferred and is to be received by " . $lookUpOffice['name'],
                    'remarks' => $data['remarks']
                ]);

                foreach ($attachments as $attachment) {
                    $attachment->update([
                        'assigned_to' => $data['assignedTo'],
                        'endorsed_to' => $data['endorsedToOtherPersonnel'],
                        'status' => 'For Receiving'
                    ]);

                    //Forward Log
                    Log::create([
                        'action_id' => $actions['Forwarded'],
                        'document_id' => $attachment->id,
                        'bundle_id' => $document->id,
                        'user_id' => $this->user['id'],
                        'office_id' => $this->office,
                        'assigned_to' => $this->office,
                        'endorsed_to' => $data['endorsedToOtherPersonnel'],
                        'description' => "Bundle (" . $document->control_no . ") forwarded to " . $lookUpOffice['name'] . " for appropriate action.",
                        'remarks' => $data['remarks']
                    ]);

                    // For Receiving Log
                    Log::create([
                        'action_id' => $actions['For Receiving'],
                        'document_id' => $attachment->id,
                        'bundle_id' => $document->id,
                        'user_id' => $this->user['id'],
                        'office_id' => $this->office,
                        'assigned_to' => $data['assignedTo'],
                        'endorsed_to' => $data['endorsedToOtherPersonnel'],
                        'description' => "Bundle (" . $document->control_no . ")" . " has been transferred and is to be received by " . $lookUpOffice['name'] . ".",
                        'remarks' => $data['remarks']
                    ]);
                }
            }
        });

        $this->clearSelection();

        return $this->flash('success', 'Document successfully forwarded!', [
            'position' => 'top-end',
            'timer' => 10000,
            'toast' => true
        ], '/status-endorsed');
    }
    /** End of Forward Documents */

    /** Miscellanous Functions */
    #[On('closeModal')]
    public function closeModal()
    {
        /** Stay on this screen. Dismissing a confirmation used to redirect to Pending. */
        return $this->redirect(Endorsed::class);
    }

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
            // Log error and return 0 if calculation fails
            LogFacade::error('Turnaround time calculation failed for document ID ' . $documentId . ': ' . $e->getMessage());
            return 0;
        }

        /** Fell through with no dates — return 0 rather than writing null to turnaroundtime */
        return 0;
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
            'livewire.status.endorsed',
            [
                'documents' => $documents
            ]
        );
    }
}
