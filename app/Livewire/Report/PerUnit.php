<?php

namespace App\Livewire\Report;

use App\Models\Category;
use App\Models\Document;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Http;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class PerUnit extends Component
{
    use LivewireAlert;
    use WithPagination;

    #[Title('Per Unit | Document Tracking Information System')]

    /** Constant Variables */
    public $offices = [];
    public $response;
    public $purchaseRequestCategoryIds = [];
    public $paymentCategoryIds = [];
    public $statuses = ['Created', 'For Receiving', 'On Process', 'Returned', 'Closed'];
    public $perPage = 10;

    /** Filter form inputs — take effect only when Filter is clicked */
    public $officeFilter = '';
    public $source = '';
    public $status = '';
    public $startDate;
    public $endDate;

    /** Filters currently applied to the data */
    public $applied = [];

    /** Office ids whose category breakdown is expanded */
    public $expanded = [];

    public function mount()
    {
        /** Filter Records from the start of the current year to today */
        $this->startDate = Carbon::now()->startOfYear()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');

        $this->purchaseRequestCategoryIds = Category::where('name', 'like', 'Purchase Request%')->pluck('id')->toArray();
        $this->paymentCategoryIds = Category::where('name', 'like', 'Payment%')->pluck('id')->toArray();

        $this->applyFilters();

        $this->checkApiConnection();
    }

    public function applyFilters()
    {
        $this->applied = [
            'office' => $this->officeFilter,
            'source' => $this->source,
            'status' => $this->status,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ];

        $this->expanded = [];
        $this->resetPage();
    }

    public function toggleUnit($officeId)
    {
        if (in_array($officeId, $this->expanded)) {
            $this->expanded = array_values(array_diff($this->expanded, [$officeId]));
        } else {
            $this->expanded[] = $officeId;
        }
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

    /** Documents matching the currently applied filters */
    private function filteredDocuments()
    {
        return Document::query()
            ->when($this->applied['source'] ?? '', function ($query, $source) {
                $query->where('source', $source);
            })
            ->when($this->applied['status'] ?? '', function ($query, $status) {
                $query->where('status', $status);
            })
            ->whereBetween('created_at', [
                Carbon::parse($this->applied['startDate'] ?? $this->startDate),
                Carbon::parse($this->applied['endDate'] ?? $this->endDate)->addDay(),
            ]);
    }

    /**
     * Conditional SUM over a set of category ids, with bindings. An empty
     * set yields a constant 0 so the SQL stays valid.
     */
    private function categoryCountExpression(array $categoryIds, string $alias): array
    {
        if (empty($categoryIds)) {
            return ['0 as ' . $alias, []];
        }

        $placeholders = implode(',', array_fill(0, count($categoryIds), '?'));

        return ['SUM(CASE WHEN category_id IN (' . $placeholders . ') THEN 1 ELSE 0 END) as ' . $alias, $categoryIds];
    }

    /**
     * Per-category document counts for one office as a flat list ordered
     * Purchase Request, then Payment, then General — highest count first
     * within each group. Each entry carries its group so the view can put
     * the count in the matching summary column.
     */
    private function categoryBreakdown($officeId): array
    {
        $names = Category::pluck('name', 'id');

        $buckets = ['purchase_requests' => [], 'payments' => [], 'general' => []];

        $this->filteredDocuments()
            ->where('office_id', $officeId)
            ->selectRaw('category_id, COUNT(*) as total')
            ->groupBy('category_id')
            ->orderByDesc('total')
            ->get()
            ->each(function ($row) use ($names, &$buckets) {
                if (in_array($row->category_id, $this->purchaseRequestCategoryIds)) {
                    $bucket = 'purchase_requests';
                } elseif (in_array($row->category_id, $this->paymentCategoryIds)) {
                    $bucket = 'payments';
                } else {
                    $bucket = 'general';
                }

                $buckets[$bucket][] = [
                    'name' => $names[$row->category_id] ?? 'Uncategorized',
                    'bucket' => $bucket,
                    'count' => (int) $row->total,
                ];
            });

        return array_merge($buckets['purchase_requests'], $buckets['payments'], $buckets['general']);
    }

    public function render()
    {
        [$purchaseSql, $purchaseBindings] = $this->categoryCountExpression($this->purchaseRequestCategoryIds, 'purchase_requests');
        [$paymentSql, $paymentBindings] = $this->categoryCountExpression($this->paymentCategoryIds, 'payments');

        /** Counts per originating office, aggregated in a single query */
        $counts = $this->filteredDocuments()
            ->select('office_id')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw($purchaseSql, $purchaseBindings)
            ->selectRaw($paymentSql, $paymentBindings)
            ->groupBy('office_id')
            ->get()
            ->keyBy('office_id');

        $allRows = collect($this->offices)
            ->when($this->applied['office'] ?? '', function ($collection, $officeId) {
                return $collection->filter(function ($office) use ($officeId) {
                    return $office['id'] == $officeId;
                });
            })
            ->map(function ($office) use ($counts) {
                $count = $counts->get($office['id']);

                $total = (int) ($count->total ?? 0);
                $purchaseRequests = (int) ($count->purchase_requests ?? 0);
                $payments = (int) ($count->payments ?? 0);

                return [
                    'id' => $office['id'],
                    'name' => $office['officeName'] ?? '—',
                    'purchase_requests' => $purchaseRequests,
                    'payments' => $payments,
                    'general' => $total - $purchaseRequests - $payments,
                    'total' => $total,
                ];
            })
            ->values();

        /** Grand totals across every matching unit, not just the current page */
        $totals = [
            'purchase_requests' => $allRows->sum('purchase_requests'),
            'payments' => $allRows->sum('payments'),
            'general' => $allRows->sum('general'),
            'total' => $allRows->sum('total'),
        ];

        $page = Paginator::resolveCurrentPage('page');
        $rows = new LengthAwarePaginator(
            $allRows->slice(($page - 1) * $this->perPage, $this->perPage)->values(),
            $allRows->count(),
            $this->perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']
        );

        /** Category breakdown only for expanded units on the current page */
        $details = [];
        foreach ($rows as $row) {
            if (in_array($row['id'], $this->expanded)) {
                $details[$row['id']] = $this->categoryBreakdown($row['id']);
            }
        }

        return view('livewire.report.per-unit', [
            'rows' => $rows,
            'totals' => $totals,
            'details' => $details,
        ]);
    }
}
