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
use Illuminate\Support\Str;

class Pending extends Component
{
    use WithPagination;
    use LivewireAlert;

    #[Title('Pending Documents | Document Tracking Information System')]

    /** Constant Variables */
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

    /** API Responses */
    protected $responseEmployees;
    protected $responseOffices;
    protected $employees = [];
    /** Small per-office subset shown in a dropdown — must stay public for the Blade view. */
    public $subEmployees = [];
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
    public $endorsedToPersonnel;
    public $endorsedToOtherPersonnel;
    public $selected_office;
    /** Reloaded from cache each request in boot(); accessed via $this-> in Blade. */
    protected $filterOfficeEmployees = [];

    /** Forward Variables */
    public int $document_id;
    public $remarks;
    public $attachments;

    /** Add Document Variables */
    public $pendings;
    public $documents_attached;
    public $parent_bundle;

    /** Filter Date Variables */
    public $startDate;
    public $endDate;

    /** Listeners for Livewire Alerts */
    protected $listeners = [
        'close',
        'forward',
        'endorse',
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
         * No default date range.
         *
         * This used to default to the last quarter, which silently hid every
         * On Process document older than 3 months - precisely the overdue ones -
         * and made this table disagree with the sidebar badge, which has never been
         * date-bounded. A status queue must show the whole queue; the date inputs
         * remain available for narrowing on demand.
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
     * Single source of truth for "which documents belong on this screen".
     *
     * render(), select-all and the three bulk actions all build from this so the
     * selection can never drift from the rows the user can actually see. Each of
     * them used to hand-roll its own filter list, and select-all's copy was missing
     * the category and date conditions entirely - meaning a filtered select-all fed
     * documents the user never saw into close() and forward().
     */
    private function baseQuery()
    {
        return Document::query()
            ->whereNull('bundle_id')
            ->where('assigned_to', $this->office)
            ->where('status', 'On Process')
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
     * filtered set across pages. With no filter it takes only the current page -
     * this office can hold hundreds of On Process documents and an unfiltered click
     * must not be able to close or forward the entire queue at once.
     */
    private function selectableIds(): array
    {
        $query = $this->baseQuery()->orderBy('created_at', 'ASC');

        if ($this->hasActiveFilter()) {
            return $query->pluck('id')->toArray();
        }

        /** forPage() mirrors paginate()'s offset, so this is exactly the visible page */
        return $query->forPage(max(1, (int) $this->getPage()), 50)->pluck('id')->all();
    }

    /**
     * Drop any pending selection. Called whenever the visible result set changes so
     * previously selected (now hidden) documents cannot be acted on.
     */
    private function clearSelection()
    {
        $this->selected_item = [];
        $this->selectAll = false;
    }

    /**
     * Reset pagination whenever a filter changes so results never land on an
     * out-of-range page, and clear the selection so it always reflects the
     * currently visible rows. None of these hooks existed before, so this component
     * could show an empty table after filtering from a later page.
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

    /** Multiple Selection */
    public function updatedSelectAll($value)
    {
        $this->selected_item = $value ? $this->selectableIds() : [];
    }

    /**
     * Keep the header checkbox honest about the row checkboxes.
     *
     * Without this, unticking a single row left "select all" visually ticked, and
     * clicking it again did nothing because Livewire skips updated* hooks when the
     * value has not changed - so the row could not be re-added.
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
     * Two bugs fixed here. array_filter and array_map both preserve the original
     * keys, so the old $findOffice[$id - 1] lookup only worked while officeList
     * happened to be id-ordered and gap-free; a deactivated or reordered office
     * threw "Undefined array key". And the API-down branch returned a bare null
     * while both callers immediately did $lookUpOffice['name'], turning an outage
     * into "Trying to access array offset on null". Always returns an array now.
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

    /**
     * Shared guard for the three bulk actions: confirm the API is reachable, then
     * re-validate the selection against the current filters before anything is
     * written. Guards against a stale selection - filters changed after selecting,
     * another user already acted on the document, or client-side tampering - so we
     * never act on rows outside the visible set. Returns null when the caller must
     * abort.
     */
    private function validatedSelection(): ?array
    {
        /**
         * checkApiConnection() nulls out responseOffices and alerts on failure;
         * carrying on would only produce "Unknown Office" logs at best.
         */
        if (!$this->checkApiConnection()) {
            return null;
        }

        $documentIds = $this->baseQuery()
            ->whereIn('id', $this->selected_item)
            ->pluck('id')
            ->toArray();

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
     * Returns null (after alerting) when any name is missing.
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
            'timer' => null,
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
         * loop, so a failure on document 5 threw after documents 1-4 were already
         * closed and logged.
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

        /**
         * One transaction for the whole batch. Previously each document had its own
         * transaction inside the loop, so a failure partway through committed a
         * partial close that cannot be undone from the UI.
         */
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

        /**
         * flash() stores the toast in the session so it survives the redirect. The
         * old code dispatched a browser event and then redirected immediately, so the
         * success message never actually appeared.
         */
        return $this->flash('success', 'Document successfully closed!', [
            'position' => 'top-end',
            'timer' => 10000,
            'toast' => true
        ], '/status-pending');
    }
    /** End of Document */

    /** Endorsement Document */
    public function endorse()
    {
        /** Validate once, up front — this used to run inside the loop */
        $data = $this->validate([
            'endorsedToPersonnel' => 'required',
            'remarks' => 'required'
        ]);

        $documentIds = $this->validatedSelection();

        if ($documentIds === null) {
            return;
        }

        $actions = $this->actionIds(['Endorsed']);

        if ($actions === null) {
            return;
        }

        $lookUpPersonnel = $this->filterUser($data['endorsedToPersonnel']);
        $endorsed = 0;

        /**
         * This method previously had no transaction at all — the document update, its
         * log, and every attachment update and log were each committed independently.
         */
        DB::transaction(function () use ($documentIds, $actions, $data, $lookUpPersonnel, &$endorsed) {
            foreach ($documentIds as $item) {
                $document = Document::find($item);

                if (!$document) {
                    continue;
                }

                /** Already endorsed to this personnel — nothing to record */
                if ($document->endorsed_to == $data['endorsedToPersonnel']) {
                    continue;
                }

                $doc_type = $document->is_bundle ? 'Bundle' : 'Document';

                //Update Document
                $document->update([
                    'endorsed_to' => $data['endorsedToPersonnel'],
                    'status' => 'On Process'
                ]);

                //Endorse Log
                Log::create([
                    'action_id' => $actions['Endorsed'],
                    'document_id' => $document->id,
                    'user_id' => $this->user['id'],
                    'office_id' => $this->office,
                    'assigned_to' => $this->office,
                    'endorsed_to' => $data['endorsedToPersonnel'],
                    'description' => $doc_type . " endorsed to " . $lookUpPersonnel . " for appropriate action.",
                    'remarks' => $data['remarks']
                ]);

                //Add log for Documents endorsed together with this bundle
                $attachments = Document::where('assigned_to', $this->office)
                    ->where('status', 'On Process')
                    ->where('bundle_id', $item)
                    ->orderBy('created_at', 'DESC')
                    ->get();

                foreach ($attachments as $attachment) {
                    $attachment->update([
                        'endorsed_to' => $data['endorsedToPersonnel'],
                        'status' => 'On Process'
                    ]);

                    //Endorse Log
                    Log::create([
                        'action_id' => $actions['Endorsed'],
                        'document_id' => $attachment->id,
                        'bundle_id' => $document->id,
                        'user_id' => $this->user['id'],
                        'office_id' => $this->office,
                        'assigned_to' => $this->office,
                        'endorsed_to' => $data['endorsedToPersonnel'],
                        'description' => "Bundle (" . $document->control_no . ") endorsed to " . $lookUpPersonnel . " for appropriate action.",
                        'remarks' => $data['remarks']
                    ]);
                }

                $endorsed++;
            }
        });

        $this->clearSelection();

        /**
         * Report what actually happened. Documents already endorsed to the chosen
         * personnel are skipped, and the old code still claimed success even when
         * every single one was skipped.
         */
        if ($endorsed === 0) {
            $this->dispatch('close-modal', class: '.document-modal');

            $this->alert('info', 'No changes — the selected document(s) are already endorsed to that personnel.', [
                'position' => 'top-end',
                'timer' => 10000,
                'toast' => true
            ]);

            return;
        }

        return $this->flash('success', 'Document successfully endorsed!', [
            'position' => 'top-end',
            'timer' => 10000,
            'toast' => true
        ], '/status-pending');
    }
    /** End of Endorsement Document */

    /** Forward Documents */
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
         * read this back from $document->assigned_to *after* updating it, arriving at
         * the same value by a far less obvious route.
         */
        $lookUpOffice = $this->lookUpOffice($data['assignedTo']);

        /**
         * One transaction for the whole batch. Previously each document had its own
         * transaction inside the loop, so a failure partway through left some
         * documents handed to the other office and the rest still here.
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
        ], '/status-pending');
    }
    /** End of Forward Documents */

    /** Miscellanous Functions */
    #[On('closeModal')]
    public function closeModal()
    {
        return $this->redirect(Pending::class);
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

        $result = array_values($result); // reindex the array
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
            'livewire.status.pending',
            [
                'documents' => $documents
            ]
        );
    }
}
