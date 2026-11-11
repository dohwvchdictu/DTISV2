<?php

namespace App\Livewire\Report;

use App\Models\Document;
use App\Services\ApiService;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Status of Documents | Document Tracking Information System')]
class DocumentStatus extends Component
{
    use WithPagination;
    use LivewireAlert;
    /**
     * Action ids. "Received" is what an office logs when it takes custody;
     * "Forwarded" and "Closed" are the two ways it finishes with a document.
     * Both sides of the completion rate are therefore log events over the same
     * date window, which is what makes the ratio meaningful.
     */
    private const ACTION_RECEIVED = 1;
    private const ACTION_FORWARDED = 3;
    private const ACTION_CLOSED = 5;
    private const ACTIONS_COMPLETED = [self::ACTION_FORWARDED, self::ACTION_CLOSED];

    /** Fallback when neither the citizen charter nor the category sets required_days. */
    private const DEFAULT_REQUIRED_DAYS = 20;

    /** Constant Variables */
    /** Office directory kept protected so it is not serialized into the Livewire snapshot; reloaded from cache in boot(). */
    protected $offices = [];
    protected $response;

    /** Filter Date Variables */
    public $startDate;
    public $endDate;

    /** Reloads the cached office directory on every request without bloating the snapshot. */
    public function boot()
    {
        $this->checkApiConnection();
    }

    public function mount()
    {
        /** Filter Records from the start of the current month to today */
        $this->startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');
    }

    /**
     * Applies the date range the user picked.
     *
     * The inputs are bound with a deferred wire:model, so this request is the
     * first time the new dates reach the server; re-rendering with them is the
     * whole job. Previously each keystroke fired its own request and re-walked
     * the log trail, and a half-typed year would query a nonsense range.
     */
    public function applyFilter()
    {
        $this->resetPage();
    }

    public function checkApiConnection()
    {
        /** API */
        $this->response = app(ApiService::class)->getOfficesData();

        if (!$this->response) {
            $this->offices = [];

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

        $this->offices = app(ApiService::class)->getActiveOffices($this->response);

        return true;
    }

    /**
     * Share of the documents an office took in that it also finished.
     *
     * Both terms come from completionWindows(), where every receipt is followed
     * to its own outcome, so $finished is by construction a subset of $started
     * and the result cannot exceed 100%. Totalling Received and Forwarded/Closed
     * as two independent counts did not hold that property: an office's own
     * created-then-forwarded work, bundle fan-out and repeat forwards all landed
     * in the numerator with no matching receipt, so a real backlog could be
     * masked and most offices pinned at 100%.
     *
     * Null when nothing arrived — no intake means no rate to report, which the
     * views render as a dash rather than as 0%.
     */
    public function completionRate($started, $finished)
    {
        if (!$started) {
            return null;
        }

        return ($finished / $started) * 100;
    }

    /**
     * Per-office custody windows for the period, cached because the date inputs
     * are live-bound and every keystroke would otherwise re-walk the logs.
     *
     * Returns ['started' => collection, 'finished' => collection,
     * 'pending' => collection], all keyed by office id.
     */
    private function completionWindows($start, $end): array
    {
        $signature = md5(json_encode([
            'start' => $start->toDateTimeString(),
            'end' => $end->toDateTimeString(),
        ]));

        /** Key bumped to v2 when 'pending' was added, so entries cached without it are never read. */
        return Cache::remember('document_status_windows_v2_' . $signature, now()->addMinutes(5), function () use ($start, $end) {
            return $this->walkCompletionWindows($start, $end);
        });
    }

    /**
     * Walk each document's log trail and pair every receipt with its outcome.
     *
     * A custody window opens when an office logs "Received" and closes on the
     * next "Forwarded" or "Closed". Only windows that *opened* inside the period
     * are counted, and a window counts as finished even if it closed after the
     * period ended — the question is how much of the work that arrived in this
     * period has since been dealt with, so a document received on the last day
     * of the range is not marked unfinished simply for being recent.
     *
     * Pending is the window that is still open when the document's trail ends:
     * received by the office inside the period, never forwarded or closed since,
     * and the document is still "On Process" with that same office today. A
     * window abandoned by another office's receipt is neither finished nor
     * pending — the document is no longer this office's to hold.
     *
     * There is deliberately no upper bound on the query: the closing log may sit
     * past the period end. Rows stream through a cursor as lightweight stdClass
     * to keep memory flat.
     */
    private function walkCompletionWindows($start, $end): array
    {
        $logs = DB::table('logs')
            ->leftJoin('documents', 'documents.id', '=', 'logs.document_id')
            ->whereIn('logs.action_id', array_merge([self::ACTION_RECEIVED], self::ACTIONS_COMPLETED))
            ->where('logs.created_at', '>=', $start)
            ->orderBy('logs.document_id')
            ->orderBy('logs.created_at')
            ->orderBy('logs.id')
            ->select(
                'logs.document_id',
                'logs.assigned_to',
                'logs.action_id',
                'logs.created_at',
                'documents.status as document_status',
                'documents.assigned_to as document_holder'
            )
            ->cursor();

        /** Both sides are 'Y-m-d H:i:s', so a string compare avoids parsing every row. */
        $endAt = $end->toDateTimeString();

        $started = [];
        $finished = [];
        $pending = [];
        $currentDoc = null;
        $openWindow = null;
        $documentStatus = null;
        $documentHolder = null;

        /** Counts the previous document's still-open window as pending if that office still holds it. */
        $settle = function () use (&$openWindow, &$documentStatus, &$documentHolder, &$pending) {
            if ($openWindow !== null
                && $openWindow['in']
                && $documentStatus === 'On Process'
                && $documentHolder === $openWindow['office']) {
                $pending[$openWindow['office']] = ($pending[$openWindow['office']] ?? 0) + 1;
            }
        };

        foreach ($logs as $log) {
            $documentId = (int) $log->document_id;

            if ($documentId !== $currentDoc) {
                $settle();

                $currentDoc = $documentId;
                $openWindow = null;
                $documentStatus = $log->document_status;
                $documentHolder = $log->document_holder === null ? null : (int) $log->document_holder;
            }

            if ((int) $log->action_id === self::ACTION_RECEIVED) {
                $officeId = (int) $log->assigned_to;

                /** Offices re-scan the same receipt in batches; that is not a new window. */
                if ($openWindow !== null && $openWindow['office'] === $officeId) {
                    continue;
                }

                /**
                 * A receipt by a different office abandons the window in progress
                 * — the document moved on without a Forwarded log, as Returned
                 * does — so it stays counted as started but never as finished.
                 */
                $inPeriod = $log->created_at < $endAt;
                $openWindow = ['office' => $officeId, 'in' => $inPeriod];

                if ($inPeriod) {
                    $started[$officeId] = ($started[$officeId] ?? 0) + 1;
                }

                continue;
            }

            /** Forwarded or Closed — the query admits nothing else — closes it. */
            if ($openWindow !== null) {
                if ($openWindow['in']) {
                    $finished[$openWindow['office']] = ($finished[$openWindow['office']] ?? 0) + 1;
                }

                $openWindow = null;
            }
        }

        /** The last document's trail has no following row to trigger it. */
        $settle();

        return ['started' => collect($started), 'finished' => collect($finished), 'pending' => collect($pending)];
    }

    /**
     * Pending documents that have passed the deadline their service commitment
     * set, counted against the office now holding them.
     *
     * Overdue counts "On Process" documents created in the date window whose
     * deadline has passed. Note it is dated by creation while the Pending column
     * is dated by the office's receipt, so it is no longer a strict subset of
     * Pending and can occasionally exceed it for an office. The deadline is
     * the created date plus the required days of the document's citizen charter,
     * or of its category when it has no charter: the same basis the dashboard
     * and the External Requests report already use, so all three screens agree
     * on what "overdue" means.
     *
     * The clock runs from creation, not from the current office's receipt,
     * because required_days is an end-to-end commitment for the whole document.
     * Giving each office in the route its own full charter clock would reset the
     * deadline at every hand-off and leave almost nothing overdue. What varies
     * per office is only *who answers for it* — the document is charged to
     * whoever is holding it when the deadline passes.
     *
     * Weekends are excluded, holidays are not.
     *
     * Working-day arithmetic cannot be expressed in portable SQL, so the query
     * groups by office, commitment and creation date first — every document in
     * a group shares one deadline, so the collapse is lossless — and PHP does
     * the date maths once per group rather than once per document.
     */
    private function overdueByOffice($start, $end)
    {
        /**
         * The charter's required days win when it has a usable value, else the
         * category's, else the default. A non-positive value counts as unset: a
         * zero-day commitment would mark a document overdue the moment it was
         * encoded.
         */
        $requiredDays = 'case'
            . ' when citizen_charters.required_days > 0 then citizen_charters.required_days'
            . ' when categories.required_days > 0 then categories.required_days'
            . ' else ' . self::DEFAULT_REQUIRED_DAYS
            . ' end';

        $groups = DB::table('documents')
            ->leftJoin('citizen_charters', 'citizen_charters.id', '=', 'documents.citizen_charter_id')
            ->leftJoin('categories', 'categories.id', '=', 'documents.category_id')
            ->where('documents.status', 'On Process')
            ->whereBetween('documents.created_at', [$start, $end])
            ->groupBy('documents.assigned_to', DB::raw($requiredDays), DB::raw('date(documents.created_at)'))
            ->select([
                'documents.assigned_to',
                DB::raw($requiredDays . ' as required_days'),
                DB::raw('date(documents.created_at) as created_date'),
                DB::raw('count(*) as documents'),
            ])
            ->get();

        $today = Carbon::today();
        $overdue = [];

        foreach ($groups as $group) {
            $dueDate = Carbon::parse($group->created_date)->startOfDay()->addWeekdays((int) $group->required_days);

            /** Signed working days to the deadline: >0 left, <0 overdue. */
            if ((int) $today->diffInWeekdays($dueDate, false) >= 0) {
                continue;
            }

            $officeId = $group->assigned_to;
            $overdue[$officeId] = ($overdue[$officeId] ?? 0) + (int) $group->documents;
        }

        return collect($overdue);
    }

    /**
     * System-wide figures for the summary cards. Previously each card ran its
     * own query inline in the blade; they are computed here so the cards and the
     * table columns cannot disagree about what they are counting.
     */
    private function overallTotals($receivedByOffice, $completedByOffice, $pendingByOffice, $overdueByOffice): array
    {
        /** Summed from the same per-office windows the table shows, so the cards reconcile. */
        $received = $receivedByOffice->sum();
        $completed = $completedByOffice->sum();

        return [
            'pending' => $pendingByOffice->sum(),
            'received' => $received,
            'completed' => $completed,
            'overdue' => $overdueByOffice->sum(),
            'rate' => $this->completionRate($received, $completed),
        ];
    }

    public function render()
    {
        /**
         * The selected range, inclusive of both days the user picked: from the
         * start of startDate up to (but not including) the day after endDate.
         *
         * Shifting the start forward as well used to drop the whole of the first
         * selected day and pull in the day after the last one. The same window is
         * used by PerUnit, TurnaroundTime and the External Requests report, and
         * MiscController mirrors it for the printed copy — keep the four in step.
         */
        $start = Carbon::parse($this->startDate)->startOfDay();
        $end = Carbon::parse($this->endDate)->addDay()->startOfDay();

        /**
         * Pre-aggregate the per-office counts instead of the blade running
         * several count queries per office (~150 queries). The blade looks each
         * office up by id from these keyed collections.
         *
         * Received, Completed and Pending all come from the custody-window walk
         * rather than independent COUNT(*)s, so Completed can never exceed
         * Received, the displayed rate is exactly Completed / Received, and
         * Pending is dated by when the office received the document (not when
         * it was created), matching Received.
         */
        $windows = $this->completionWindows($start, $end);
        $receivedByOffice = $windows['started'];
        $completedByOffice = $windows['finished'];
        $pendingByOffice = $windows['pending'];

        $overdueByOffice = $this->overdueByOffice($start, $end);

        $offices = Arr::sort($this->offices, function (array $value) {
            return $value['officeName'];
        });

        return view('livewire.report.document-status', [
            'offices' => $offices,
            'pendingByOffice' => $pendingByOffice,
            'completedByOffice' => $completedByOffice,
            'receivedByOffice' => $receivedByOffice,
            'overdueByOffice' => $overdueByOffice,
            'totals' => $this->overallTotals($receivedByOffice, $completedByOffice, $pendingByOffice, $overdueByOffice),
        ]);
    }
}
