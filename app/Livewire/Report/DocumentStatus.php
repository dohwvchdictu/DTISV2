<?php

namespace App\Livewire\Report;

use App\Models\Document;
use App\Services\ApiService;
use App\Support\BusinessDays;
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

    /** A pending document is overdue once it has sat this long at one office. */
    private const OVERDUE_AFTER_DAYS = 3;

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
     * Returns ['started' => collection, 'finished' => collection], both keyed by
     * office id.
     */
    private function completionWindows($start, $end): array
    {
        $signature = md5(json_encode([
            'start' => $start->toDateTimeString(),
            'end' => $end->toDateTimeString(),
        ]));

        return Cache::remember('document_status_windows_' . $signature, now()->addMinutes(5), function () use ($start, $end) {
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
     * There is deliberately no upper bound on the query: the closing log may sit
     * past the period end. Rows stream through a cursor as lightweight stdClass
     * to keep memory flat.
     */
    private function walkCompletionWindows($start, $end): array
    {
        $logs = DB::table('logs')
            ->whereIn('action_id', array_merge([self::ACTION_RECEIVED], self::ACTIONS_COMPLETED))
            ->where('created_at', '>=', $start)
            ->orderBy('document_id')
            ->orderBy('created_at')
            ->orderBy('id')
            ->select('document_id', 'assigned_to', 'action_id', 'created_at')
            ->cursor();

        /** Both sides are 'Y-m-d H:i:s', so a string compare avoids parsing every row. */
        $endAt = $end->toDateTimeString();

        $started = [];
        $finished = [];
        $currentDoc = null;
        $openWindow = null;

        foreach ($logs as $log) {
            $documentId = (int) $log->document_id;

            if ($documentId !== $currentDoc) {
                $currentDoc = $documentId;
                $openWindow = null;
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

        return ['started' => collect($started), 'finished' => collect($finished)];
    }

    /**
     * Pending documents that have sat too long at the office now holding them.
     *
     * Overdue is a strict subset of the Pending column — same status and same
     * date window — narrowed to those whose most recent receipt at the current
     * office is more than OVERDUE_AFTER_DAYS business days old. Age is measured
     * from the receipt rather than from creation, because the question is how
     * long *this* office has held it, not how old the document is.
     *
     * Weekends are excluded, matching the Turnaround Time report, so a document
     * received on Friday is not overdue until the following Wednesday.
     *
     * Business days cannot be expressed in portable SQL, so one grouped query
     * streams the candidate receipts and the filtering happens here. Pending is
     * a small slice of the table, so this stays cheap.
     */
    private function overdueByOffice($start, $end)
    {
        $receipts = DB::table('logs')
            ->join('documents', 'documents.id', '=', 'logs.document_id')
            ->where('documents.status', 'On Process')
            ->whereBetween('documents.created_at', [$start, $end])
            ->where('logs.action_id', self::ACTION_RECEIVED)
            /** Only receipts logged by the office currently holding the document. */
            ->whereColumn('logs.assigned_to', 'documents.assigned_to')
            ->groupBy('logs.document_id', 'documents.assigned_to')
            ->selectRaw('logs.document_id, documents.assigned_to, MAX(logs.created_at) as received_at')
            ->cursor();

        $now = Carbon::now();
        $overdue = [];

        foreach ($receipts as $receipt) {
            if (BusinessDays::between($receipt->received_at, $now) <= self::OVERDUE_AFTER_DAYS) {
                continue;
            }

            $officeId = $receipt->assigned_to;
            $overdue[$officeId] = ($overdue[$officeId] ?? 0) + 1;
        }

        return collect($overdue);
    }

    /**
     * System-wide figures for the summary cards. Previously each card ran its
     * own query inline in the blade; they are computed here so the cards and the
     * table columns cannot disagree about what they are counting.
     */
    private function overallTotals($start, $end, $receivedByOffice, $completedByOffice, $overdueByOffice): array
    {
        /** Summed from the same per-office windows the table shows, so the cards reconcile. */
        $received = $receivedByOffice->sum();
        $completed = $completedByOffice->sum();

        return [
            'pending' => Document::where('status', 'On Process')
                ->whereBetween('created_at', [$start, $end])
                ->count(),
            'received' => $received,
            'completed' => $completed,
            'overdue' => $overdueByOffice->sum(),
            'rate' => $this->completionRate($received, $completed),
        ];
    }

    public function render()
    {
        $start = Carbon::parse($this->startDate)->addDays(1);
        $end = Carbon::parse($this->endDate)->addDays(1);

        /**
         * Pre-aggregate the per-office counts in grouped queries instead of the
         * blade running several count queries per office (~150 queries). The
         * blade now looks each office up by id from these keyed collections.
         */
        $pendingByOffice = Document::query()
            ->where('status', 'On Process')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('assigned_to, COUNT(*) as aggregate')
            ->groupBy('assigned_to')
            ->pluck('aggregate', 'assigned_to');

        /**
         * Received and Completed both come from the custody-window walk rather
         * than from two independent COUNT(*)s, so Completed can never exceed
         * Received and the displayed rate is exactly Completed / Received.
         */
        $windows = $this->completionWindows($start, $end);
        $receivedByOffice = $windows['started'];
        $completedByOffice = $windows['finished'];

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
            'totals' => $this->overallTotals($start, $end, $receivedByOffice, $completedByOffice, $overdueByOffice),
        ]);
    }
}
