<?php

namespace App\Livewire\Report;

use App\Models\Document;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class TurnaroundTime extends Component
{
    use LivewireAlert;
    use WithPagination;

    #[Title('Turnaround Time | Document Tracking Information System')]

    /**
     * Action ids. A hop starts when an office receives a document and ends the
     * moment that same office forwards, endorses, returns, or closes it. The
     * origin office only "Created" the document, so it is never a hop start and
     * is excluded — matching the rule that dwell is counted per receiving office.
     */
    private const ACTION_RECEIVED = 1;
    private const EXIT_ACTIONS = [3, 10, 4, 5]; // Forwarded, Endorsed, Returned, Closed

    /** Constant Variables */
    public $offices = [];
    public $response;
    public $perPage = 10;
    public $detailPerPage = 10;

    /** Office whose per-document breakdown is expanded (only one at a time). */
    public $expandedOffice = null;

    /** Sortable numeric columns shared by the office table and the detail table. */
    private const SORTABLE = ['avg', 'min', 'max'];

    /** Sort state for the main office table. */
    public $sortColumn = 'avg';
    public $sortDirection = 'desc';

    /** Sort state for the expanded per-category table. */
    public $detailSortColumn = 'avg';
    public $detailSortDirection = 'desc';

    /** Filter form inputs — take effect only when Filter is clicked */
    public $officeFilter = '';
    public $source = '';
    public $startDate;
    public $endDate;

    /** Filters currently applied to the data */
    public $applied = [];

    public function mount()
    {
        /** Filter Records from the start of the current year to today */
        $this->startDate = Carbon::now()->startOfYear()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');

        $this->applyFilters();

        $this->checkApiConnection();
    }

    public function applyFilters()
    {
        $this->applied = [
            'office' => $this->officeFilter,
            'source' => $this->source,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ];

        $this->expandedOffice = null;
        $this->resetPage();
    }

    /** Expand a single office's per-document breakdown, or collapse it if re-clicked. */
    public function toggleOffice($officeId)
    {
        $this->expandedOffice = $this->expandedOffice === (int) $officeId ? null : (int) $officeId;
        $this->resetPage('docs');
    }

    /** Sort the office table by a numeric column, toggling direction on repeat clicks. */
    public function sortBy($column)
    {
        if (!in_array($column, self::SORTABLE, true)) {
            return;
        }

        if ($this->sortColumn === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortColumn = $column;
            $this->sortDirection = 'desc';
        }

        $this->resetPage('page');
    }

    /** Sort the expanded per-category table by a numeric column. */
    public function sortDetailBy($column)
    {
        if (!in_array($column, self::SORTABLE, true)) {
            return;
        }

        if ($this->detailSortColumn === $column) {
            $this->detailSortDirection = $this->detailSortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->detailSortColumn = $column;
            $this->detailSortDirection = 'desc';
        }

        $this->resetPage('docs');
    }

    public function checkApiConnection()
    {
        /** API */
        $officeResponse = Http::get(config('services.api.base_url') . 'public/get-offices');

        if (!$officeResponse->ok()) {
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

        $this->response = $officeResponse->json();

        $this->offices = collect($this->response['officeList'] ?? [])
            ->sortBy('officeName')
            ->values()
            ->all();

        return true;
    }

    /**
     * Documents in scope for the report — filtered by source and creation date
     * only. The office filter is applied later, to the per-office result rows,
     * because a single document passes through several offices.
     */
    private function filteredDocuments()
    {
        return Document::query()
            ->when($this->applied['source'] ?? '', function ($query, $source) {
                $query->where('source', $source);
            })
            ->whereBetween('created_at', [
                Carbon::parse($this->applied['startDate'] ?? $this->startDate),
                Carbon::parse($this->applied['endDate'] ?? $this->endDate)->addDay(),
            ]);
    }

    /** Whole business days between two moments, weekends excluded. */
    private function businessDays($start, $end): int
    {
        $start = Carbon::parse($start);
        $end = Carbon::parse($end);

        if ($end->lessThanOrEqualTo($start)) {
            return 0;
        }

        return $start->diffInDaysFiltered(function (Carbon $date) {
            return !$date->isWeekend();
        }, $end);
    }

    /**
     * Walk every in-scope document's logs and accumulate, per office, the dwell
     * time of each completed hop (Received → Forwarded/Endorsed/Returned/Closed).
     * Returns [office stats keyed by office_id, overall summary].
     *
     * Only source and date drive the heavy walk, so the result is cached under
     * those keys — the office filter is a cheap post-filter on the rows and the
     * same walk is reused when a user switches offices or pages the table.
     */
    private function computeDwell(): array
    {
        $signature = md5(json_encode([
            'source' => $this->applied['source'] ?? '',
            'start' => $this->applied['startDate'] ?? $this->startDate,
            'end' => $this->applied['endDate'] ?? $this->endDate,
        ]));

        return Cache::remember('turnaround_dwell_' . $signature, now()->addMinutes(5), function () {
            return $this->walkDwell();
        });
    }

    /** The uncached hop walk backing computeDwell(). */
    private function walkDwell(): array
    {
        $totalDocuments = $this->filteredDocuments()->count();

        $perOffice = [];
        $documentsWithHop = [];
        $overall = ['count' => 0, 'sum' => 0, 'min' => null, 'max' => null];

        if ($totalDocuments === 0) {
            return ['offices' => $perOffice, 'total' => 0, 'documents' => 0, 'overall' => $overall];
        }

        /**
         * Join logs to documents so the date/source filter runs in SQL and we
         * avoid a giant IN(...) on document ids. Rows are streamed with a cursor
         * (lightweight stdClass, not Eloquent models) to keep memory flat over
         * the hundreds of thousands of logs a full year can hold.
         */
        $rangeStart = Carbon::parse($this->applied['startDate'] ?? $this->startDate);
        $rangeEnd = Carbon::parse($this->applied['endDate'] ?? $this->endDate)->addDay();

        $logs = DB::table('logs')
            ->join('documents', 'documents.id', '=', 'logs.document_id')
            ->whereIn('logs.action_id', array_merge([self::ACTION_RECEIVED], self::EXIT_ACTIONS))
            ->when($this->applied['source'] ?? '', function ($query, $source) {
                $query->where('documents.source', $source);
            })
            ->whereBetween('documents.created_at', [$rangeStart, $rangeEnd])
            ->orderBy('logs.document_id')
            ->orderBy('logs.created_at')
            ->orderBy('logs.id')
            ->select('logs.document_id', 'logs.office_id', 'logs.action_id', 'logs.created_at')
            ->cursor();

        $currentDoc = null;
        $openHop = null; // ['office' => id, 'time' => created_at]

        foreach ($logs as $log) {
            $documentId = (int) $log->document_id;

            if ($documentId !== $currentDoc) {
                $currentDoc = $documentId;
                $openHop = null;
            }

            if ((int) $log->action_id === self::ACTION_RECEIVED) {
                $openHop = ['office' => (int) $log->office_id, 'time' => $log->created_at];
                continue;
            }

            /** An exit action closes the hop opened by the matching receive. */
            if ($openHop === null) {
                continue;
            }

            $office = $openHop['office'];
            $days = $this->businessDays($openHop['time'], $log->created_at);

            if (!isset($perOffice[$office])) {
                $perOffice[$office] = ['count' => 0, 'sum' => 0, 'min' => $days, 'max' => $days];
            }

            $perOffice[$office]['count']++;
            $perOffice[$office]['sum'] += $days;
            $perOffice[$office]['min'] = min($perOffice[$office]['min'], $days);
            $perOffice[$office]['max'] = max($perOffice[$office]['max'], $days);

            $overall['count']++;
            $overall['sum'] += $days;
            $overall['min'] = $overall['min'] === null ? $days : min($overall['min'], $days);
            $overall['max'] = $overall['max'] === null ? $days : max($overall['max'], $days);

            $documentsWithHop[$documentId] = true;
            $openHop = null;
        }

        return [
            'offices' => $perOffice,
            'total' => $totalDocuments,
            'documents' => count($documentsWithHop),
            'overall' => $overall,
        ];
    }

    /**
     * Per-category breakdown for a single office: one row per document category,
     * summarising the dwell of the completed hops at that office, plus a count of
     * documents currently sitting there (received, not yet forwarded/closed).
     * Loaded on demand when a row is expanded and cached under office + filters.
     */
    private function officeDetail(int $officeId): array
    {
        $signature = md5(json_encode([
            'office' => $officeId,
            'source' => $this->applied['source'] ?? '',
            'start' => $this->applied['startDate'] ?? $this->startDate,
            'end' => $this->applied['endDate'] ?? $this->endDate,
        ]));

        return Cache::remember('turnaround_detail_' . $signature, now()->addMinutes(5), function () use ($officeId) {
            return $this->walkOfficeDetail($officeId);
        });
    }

    /** The uncached per-office hop walk backing officeDetail(). */
    private function walkOfficeDetail(int $officeId): array
    {
        $rangeStart = Carbon::parse($this->applied['startDate'] ?? $this->startDate);
        $rangeEnd = Carbon::parse($this->applied['endDate'] ?? $this->endDate)->addDay();

        /** Only documents that were received at this office are worth walking. */
        $documentIds = DB::table('logs')
            ->join('documents', 'documents.id', '=', 'logs.document_id')
            ->where('logs.office_id', $officeId)
            ->where('logs.action_id', self::ACTION_RECEIVED)
            ->when($this->applied['source'] ?? '', function ($query, $source) {
                $query->where('documents.source', $source);
            })
            ->whereBetween('documents.created_at', [$rangeStart, $rangeEnd])
            ->distinct()
            ->pluck('logs.document_id');

        if ($documentIds->isEmpty()) {
            return ['categories' => [], 'current' => 0, 'completed' => 0];
        }

        /** document_id => category_id, so each hop can be attributed to a type. */
        $categoryByDoc = Document::whereIn('id', $documentIds)->pluck('category_id', 'id');

        $logs = DB::table('logs')
            ->whereIn('document_id', $documentIds)
            ->whereIn('action_id', array_merge([self::ACTION_RECEIVED], self::EXIT_ACTIONS))
            ->orderBy('document_id')
            ->orderBy('created_at')
            ->orderBy('id')
            ->select('document_id', 'office_id', 'action_id', 'created_at')
            ->cursor();

        $categories = [];
        $current = 0;
        $completed = 0;
        $currentDoc = null;
        $openHop = null;

        /** A still-open hop at this office means the document is sitting here now. */
        $finalize = function () use (&$openHop, &$current, $officeId) {
            if ($openHop !== null && $openHop['office'] === $officeId) {
                $current++;
            }
        };

        foreach ($logs as $log) {
            $documentId = (int) $log->document_id;

            if ($documentId !== $currentDoc) {
                $finalize();
                $currentDoc = $documentId;
                $openHop = null;
            }

            if ((int) $log->action_id === self::ACTION_RECEIVED) {
                $openHop = ['office' => (int) $log->office_id, 'time' => $log->created_at];
                continue;
            }

            if ($openHop === null) {
                continue;
            }

            if ($openHop['office'] === $officeId) {
                $categoryId = (int) ($categoryByDoc[$documentId] ?? 0);
                $days = $this->businessDays($openHop['time'], $log->created_at);

                if (!isset($categories[$categoryId])) {
                    $categories[$categoryId] = ['count' => 0, 'sum' => 0, 'min' => $days, 'max' => $days];
                }

                $categories[$categoryId]['count']++;
                $categories[$categoryId]['sum'] += $days;
                $categories[$categoryId]['min'] = min($categories[$categoryId]['min'], $days);
                $categories[$categoryId]['max'] = max($categories[$categoryId]['max'], $days);
                $completed++;
            }

            $openHop = null;
        }

        $finalize();

        return ['categories' => $categories, 'current' => $current, 'completed' => $completed];
    }

    /** Map of office_id => officeName pulled from the offices API list. */
    private function officeNames(): array
    {
        return collect($this->offices)->pluck('officeName', 'id')->toArray();
    }

    /**
     * One row per office, sorted by the chosen column, honouring the office filter.
     */
    private function officeRows(array $perOffice): array
    {
        $names = $this->officeNames();
        $selected = $this->applied['office'] ?? '';

        $rows = [];

        foreach ($perOffice as $officeId => $stats) {
            if ($selected !== '' && (string) $officeId !== (string) $selected) {
                continue;
            }

            $rows[] = [
                'office_id' => $officeId,
                'name' => $names[$officeId] ?? ('Office #' . $officeId),
                'documents' => $stats['count'],
                'avg' => $stats['count'] ? round($stats['sum'] / $stats['count'], 1) : null,
                'min' => $stats['min'],
                'max' => $stats['max'],
            ];
        }

        usort($rows, $this->rowSorter($this->sortColumn, $this->sortDirection));

        return $rows;
    }

    /**
     * Comparator for the dwell tables: sort by the chosen numeric column in the
     * chosen direction, breaking ties by document count (busiest first) so the
     * order stays stable.
     */
    private function rowSorter(string $column, string $direction): callable
    {
        $factor = $direction === 'asc' ? 1 : -1;

        return function ($a, $b) use ($column, $factor) {
            $cmp = ($a[$column] <=> $b[$column]) * $factor;

            return $cmp !== 0 ? $cmp : $b['documents'] <=> $a['documents'];
        };
    }

    public function render()
    {
        $data = $this->computeDwell();
        $allRows = collect($this->officeRows($data['offices']));

        $overall = $data['overall'];
        $selected = $this->applied['office'] ?? '';

        /**
         * When a single office is selected the summary should reflect that office
         * only; otherwise show the system-wide figures across every hop.
         */
        if ($selected !== '') {
            $handled = $allRows->sum('documents');
            $totals = [
                'documents' => $handled,
                'total' => $data['total'],
                'average' => $allRows->count() ? $allRows->first()['avg'] : null,
                'fastest' => $allRows->count() ? $allRows->min('min') : null,
                'slowest' => $allRows->count() ? $allRows->max('max') : null,
            ];
        } else {
            $totals = [
                'documents' => $data['documents'],
                'total' => $data['total'],
                'average' => $overall['count'] ? round($overall['sum'] / $overall['count'], 1) : null,
                'fastest' => $overall['min'],
                'slowest' => $overall['max'],
            ];
        }

        $page = Paginator::resolveCurrentPage('page');
        $rows = new LengthAwarePaginator(
            $allRows->slice(($page - 1) * $this->perPage, $this->perPage)->values(),
            $allRows->count(),
            $this->perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']
        );

        /** Lazy-load the expanded office's per-category breakdown, if any. */
        $detail = null;
        if ($this->expandedOffice !== null) {
            $result = $this->officeDetail($this->expandedOffice);

            $names = \App\Models\Category::pluck('name', 'id');

            $categoryRows = collect($result['categories'])
                ->map(function ($stats, $categoryId) use ($names) {
                    return [
                        'name' => $names[$categoryId] ?? 'Uncategorized',
                        'documents' => $stats['count'],
                        'avg' => $stats['count'] ? round($stats['sum'] / $stats['count'], 1) : null,
                        'min' => $stats['min'],
                        'max' => $stats['max'],
                    ];
                })
                ->sort($this->rowSorter($this->detailSortColumn, $this->detailSortDirection))
                ->values();

            $docsPage = Paginator::resolveCurrentPage('docs');

            $detail = [
                'office' => $this->expandedOffice,
                'current' => $result['current'],
                'completed' => $result['completed'],
                'rows' => new LengthAwarePaginator(
                    $categoryRows->slice(($docsPage - 1) * $this->detailPerPage, $this->detailPerPage)->values(),
                    $categoryRows->count(),
                    $this->detailPerPage,
                    $docsPage,
                    ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'docs']
                ),
            ];
        }

        return view('livewire.report.turnaround-time', [
            'rows' => $rows,
            'totals' => $totals,
            'detail' => $detail,
        ]);
    }
}
