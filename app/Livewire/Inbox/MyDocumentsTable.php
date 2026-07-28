<?php

namespace App\Livewire\Inbox;

use App\Models\Action;
use App\Models\Category;
use App\Models\Document;
use App\Models\Log;
use App\Services\ApiService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class MyDocumentsTable extends Component
{
    use WithPagination;
    use LivewireAlert;

    /** Constants */
    public $categories_array = [];
    public $user = [];
    public $office;
    /**
     * Large, rarely-changing directory data. Kept protected so it is NOT
     * serialized into the Livewire snapshot on every request; reloaded from
     * cache each request via boot().
     */
    protected $offices = [];
    protected $employees = [];
    /** Small per-office subset shown in a dropdown — must stay public for the Blade view. */
    public $subEmployees = [];
    protected $filterOfficeEmployees = [];
    public $id = 0;
    protected $responseEmployees;
    protected $responseOffices;

    /** Search & Filter Variables*/
    public $search = '';
    public $selected_filter = [];

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
        'closeModal'
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

        $this->categories_array = Category::where(function ($query) {
            $query->where('name', 'like', '%' . 'Payment' . '%')
                ->orWhere('name', 'like', '%' . 'Purchase' . '%');
        })->pluck('id')->toArray();
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
            $this->filterOfficeEmployees = [];
            $this->responseEmployees = null;
            $this->responseOffices = null;
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
     * Which documents belong on screen right now: owned by this office, excluding the
     * categories that have their own screens, plus the active filters.
     */
    private function baseQuery()
    {
        return Document::query()
            ->where('office_id', $this->office)
            ->when($this->search, function ($query) {
                // Properly scope the OR conditions within a nested where
                $query->where(function ($q) {
                    $q->where('control_no', 'like', '%' . $this->search . '%')
                        ->orWhere('subject', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->selected_filter, function ($query) {
                $query->whereIn('status', $this->selected_filter);
            })
            ->when($this->categories_array, function ($query) {
                $query->whereNotIn('category_id', $this->categories_array);
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
     * What may actually be acted on: the rows whose checkbox is enabled - still
     * Created or awaiting receipt, and a top-level document rather than an
     * attachment, since forward() treats every selected id as a bundle parent.
     *
     * The bulk action validates against this rather than the active filters, so a
     * batch assembled across several searches survives submission.
     */
    private function eligibilityQuery()
    {
        return Document::query()
            ->whereNull('bundle_id')
            ->where('office_id', $this->office)
            ->whereIn('status', ['Created', 'For Receiving'])
            ->when($this->categories_array, function ($query) {
                $query->whereNotIn('category_id', $this->categories_array);
            });
    }

    /** Has the user actively narrowed the list? */
    private function hasActiveFilter(): bool
    {
        return filled($this->search)
            || !empty($this->selected_filter)
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
            ->whereNull('bundle_id')
            ->whereIn('status', ['Created', 'For Receiving'])
            ->orderBy('created_at', 'DESC');

        if ($this->hasActiveFilter()) {
            return $query->pluck('id')->all();
        }

        /** forPage() mirrors paginate()'s offset — this table pages at 10, not 50 */
        return $query->forPage(max(1, (int) $this->getPage()), 10)->pluck('id')->all();
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
     * The documents currently selected, resolved for the review panel — picks made
     * under an earlier search are off-screen but still queued.
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
     * Reset pagination whenever a filter changes so results never land on an
     * out-of-range page, and untick "select all" because it described the previous
     * result set - but keep hand-picked rows so a batch can span several searches.
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

    public function updatedSelectedFilter()
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

        // Validate officeList exists
        if (!isset($this->responseOffices['officeList']) || !is_array($this->responseOffices['officeList'])) {
            return [
                'id' => $assigned_to,
                'code' => 'UNKNOWN',
                'name' => 'Unknown Office'
            ];
        }

        $result = array_filter($this->responseOffices['officeList'], function ($office) {
            return $office['id'] == $this->assignedTo;
        });

        if (empty($result)) {
            return [
                'id' => $assigned_to,
                'code' => 'UNKNOWN',
                'name' => 'Unknown Office'
            ];
        }

        $findOffice = reset($result); // Safely get first element
        
        return [
            'id' => $findOffice['id'] ?? $assigned_to,
            'code' => $findOffice['officeCode'] ?? 'UNKNOWN',
            'name' => $findOffice['officeName'] ?? 'Unknown Office'
        ];
    }

    /** Forward Documents */
    public function forward()
    {
        /** Validate once, up front — this used to run inside the loop */
        $data = $this->validate([
            'assignedTo' => 'required',
            'endorsedTo' => '',
            'remarks' => ''
        ]);

        /**
         * Re-validate against eligibility, not the active filters. Still blocks
         * anything outside this office or no longer actionable, while allowing a batch
         * assembled across several searches.
         */
        $documentIds = $this->eligibilityQuery()
            ->whereIn('id', $this->normalizedSelection())
            ->pluck('id')
            ->all();

        if (empty($documentIds)) {
            $this->clearSelection();

            $this->alert('warning', 'Nothing to forward. The selected document(s) are no longer available.', [
                'position' => 'top-end',
                'timer' => 10000,
                'toast' => true
            ]);

            return;
        }

        /** Resolved once instead of on every loop iteration, with a guard */
        $forwardedActionId = Action::firstWhere('name', 'Forwarded')?->id;
        $receivingActionId = Action::firstWhere('name', 'For Receiving')?->id;

        if (!$forwardedActionId || !$receivingActionId) {
            $this->alert('error', 'A required action row is missing. Contact the system administrator.', [
                'position' => 'center',
                'toast' => true,
                'timer' => null,
                'showConfirmButton' => true,
                'confirmButtonText' => 'OK',
                'confirmButtonColor' => '#dc2626',
            ]);

            return;
        }

        /** Destination office, identical for every document in the batch */
        $lookUpOffice = $this->lookUpOffice($data['assignedTo']);

        /**
         * One transaction for the whole batch. Previously each document had its own
         * transaction inside the loop, so a failure part-way handed some documents to
         * the other office and left the rest here.
         */
        DB::transaction(function () use ($documentIds, $data, $lookUpOffice, $forwardedActionId, $receivingActionId) {
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
                    'endorsed_to' => $data['endorsedTo'],
                    'status' => 'For Receiving'
                ]);

                // Forward Log
                Log::create([
                    'action_id' => $forwardedActionId,
                    'document_id' => $document->id,
                    'user_id' => $this->user['id'],
                    'office_id' => $this->office,
                    'assigned_to' => $this->office,
                    'endorsed_to' => $data['endorsedTo'],
                    'description' => $doc_type . " forwarded to " . $lookUpOffice['name'] . " for appropriate action.",
                    'remarks' => $data['remarks']
                ]);

                // For Receiving Log
                Log::create([
                    'action_id' => $receivingActionId,
                    'document_id' => $document->id,
                    'user_id' => $this->user['id'],
                    'office_id' => $this->office,
                    'assigned_to' => $data['assignedTo'],
                    'endorsed_to' => $data['endorsedTo'],
                    'description' => $doc_type . " has been transferred and is to be received by " . $lookUpOffice['name'],
                    'remarks' => $data['remarks']
                ]);

                foreach ($attachments as $attachment) {
                    $attachment->update([
                        'assigned_to' => $data['assignedTo'],
                        'endorsed_to' => $data['endorsedTo'],
                        'status' => 'For Receiving'
                    ]);

                    // Forward Log
                    Log::create([
                        'action_id' => $forwardedActionId,
                        'document_id' => $attachment->id,
                        'bundle_id' => $document->id,
                        'user_id' => $this->user['id'],
                        'office_id' => $this->office,
                        'assigned_to' => $this->office,
                        'endorsed_to' => $data['endorsedTo'],
                        'description' => $doc_type . " forwarded to " . $lookUpOffice['name'] . " for appropriate action.",
                        'remarks' => $data['remarks']
                    ]);

                    // For Receiving Log
                    Log::create([
                        'action_id' => $receivingActionId,
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
        });

        $this->clearSelection();

        /** flash() survives the redirect; a dispatched alert did not */
        return $this->flash('success', 'Document successfully forwarded!', [
            'position' => 'top-end',
            'timer' => 10000,
            'toast' => true
        ], '/my-documents');
    }
    /** End of Forward Documents */

    /** Filter by Date */
    public function filterByDate()
    {
        $data = $this->validate([
            'startDate' => 'required',
            'endDate' => 'required'
        ]);

        $this->startDate = $data['startDate'];
        $this->endDate = $data['endDate'];
    }
    /** End of Filter by Date */

    /** Miscellanous Functions */
    #[On('closeModal')]
    public function closeModal()
    {
        return $this->redirect(MyDocuments::class);
    }

    public function updatedSelectAll($value)
    {
        if (!$value) {
            $this->selected_item = [];

            return;
        }

        if (empty($this->selected_filter)) {
            $this->alert('error', 'Please select a status first!', [
                'position' => 'top-end',
                'timer' => 3000,
                'toast' => true
            ]);

            $this->selectAll = false;

            return;
        }

        /**
         * Merge, so select-all does not discard rows picked under an earlier search.
         * This used to ignore the search box entirely, selecting documents that were
         * not in the filtered table at all.
         */
        $this->selected_item = array_values(array_unique(
            array_merge($this->normalizedSelection(), $this->selectableIds())
        ));
    }

    public function canForwardSelected()
    {
        $selection = $this->normalizedSelection();

        if (empty($selection)) {
            return false;
        }

        // Check if all selected documents have 'Created' status
        return count($selection) === Document::whereIn('id', $selection)
            ->where('status', 'Created')
            ->count();
    }

    public function canGenerateSelected()
    {
        $selection = $this->normalizedSelection();

        if (empty($selection)) {
            return false;
        }

        // Check if all selected documents have 'For Receiving' status
        return count($selection) === Document::whereIn('id', $selection)
            ->where('status', 'For Receiving')
            ->count();
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

        if (!isset($result[0])) {
            return '';
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

        // Look up against the full officeList (not the active-only dropdown
        // list) so historical documents assigned to a deactivated office
        // still resolve to its code.
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

    public function colorIndicator($status)
    {
        switch ($status) {
            case 'Created':
                return "bg-gray-100 dark:bg-neutral-700";
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
                return '<svg class="shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-check"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>';
                break;
            default:
                return '<svg class="shrink-0 size-5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-circle-x"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6"/><path d="m9 9 6 6"/></svg>';
        }
    }

    /** End of Miscellanous Functions */


    public function render()
    {
        $documents = $this->baseQuery()
            ->with('category')
            ->orderBy('created_at', 'DESC')
            ->paginate(10);

        return view(
            'livewire.inbox.my-documents-table',
            [
                'documents' => $documents
            ]
        );
    }
}
