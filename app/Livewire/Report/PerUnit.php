<?php

namespace App\Livewire\Report;

use App\Models\Category;
use App\Models\Document;
use App\Services\ApiService;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
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
    /** Office directory kept protected so it is not serialized into the Livewire snapshot; reloaded from cache in boot(). */
    protected $offices = [];
    protected $response;
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

    public function mount()
    {
        /** Filter Records from the start of the current year to today */
        $this->startDate = Carbon::now()->startOfYear()->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');

        $this->purchaseRequestCategoryIds = Category::where('name', 'like', 'Purchase Request%')->pluck('id')->toArray();
        $this->paymentCategoryIds = Category::where('name', 'like', 'Payment%')->pluck('id')->toArray();

        $this->applyFilters();
    }

    /** Reloads the cached office directory on every request without bloating the snapshot. */
    public function boot()
    {
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
            ->when($this->applied['office'] ?? '', function ($query, $officeId) {
                $query->where('office_id', $officeId);
            })
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
     * Per-category document counts as a flat list ordered Purchase Request,
     * then Payment, then General — highest count first within each group.
     * Each entry carries its group so the view can put the count in the
     * matching summary column.
     */
    private function categoryBreakdown(): array
    {
        $names = Category::pluck('name', 'id');

        $buckets = ['purchase_requests' => [], 'payments' => [], 'general' => []];

        $this->filteredDocuments()
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
        $allRows = collect($this->categoryBreakdown());

        $grouped = $allRows->groupBy('bucket');

        /** Grand totals across every matching document, not just the current page */
        $totals = [
            'purchase_requests' => $grouped->get('purchase_requests', collect())->sum('count'),
            'payments' => $grouped->get('payments', collect())->sum('count'),
            'general' => $grouped->get('general', collect())->sum('count'),
        ];
        $totals['total'] = $totals['purchase_requests'] + $totals['payments'] + $totals['general'];

        $page = Paginator::resolveCurrentPage('page');
        $rows = new LengthAwarePaginator(
            $allRows->slice(($page - 1) * $this->perPage, $this->perPage)->values(),
            $allRows->count(),
            $this->perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']
        );

        return view('livewire.report.per-unit', [
            'rows' => $rows,
            'totals' => $totals,
        ]);
    }
}
