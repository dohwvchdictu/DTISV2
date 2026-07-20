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

class TurnaroundTime extends Component
{
    use LivewireAlert;
    use WithPagination;

    #[Title('Turnaround Time | Document Tracking Information System')]

    /** Constant Variables */
    public $offices = [];
    public $response;
    public $purchaseRequestCategoryIds = [];
    public $paymentCategoryIds = [];
    public $perPage = 10;

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
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
        ];

        $this->resetPage();
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

    /** Documents matching the currently applied filters, any status */
    private function filteredDocuments()
    {
        return Document::query()
            ->when($this->applied['office'] ?? '', function ($query, $officeId) {
                $query->where('office_id', $officeId);
            })
            ->when($this->applied['source'] ?? '', function ($query, $source) {
                $query->where('source', $source);
            })
            ->whereBetween('created_at', [
                Carbon::parse($this->applied['startDate'] ?? $this->startDate),
                Carbon::parse($this->applied['endDate'] ?? $this->endDate)->addDay(),
            ]);
    }

    /**
     * Closed documents only — turnaround time is computed and stored when a
     * document is closed, so only these carry a measurable value.
     */
    private function closedDocuments()
    {
        return $this->filteredDocuments()->where('status', 'Closed');
    }

    private function bucketFor($categoryId): string
    {
        if (in_array($categoryId, $this->purchaseRequestCategoryIds)) {
            return 'purchase_requests';
        }

        if (in_array($categoryId, $this->paymentCategoryIds)) {
            return 'payments';
        }

        return 'general';
    }

    /**
     * Turnaround stats per category as a flat list ordered Purchase Request,
     * then Payment, then General — most closed documents first within each
     * group. AVG/MIN/MAX ignore NULL turnaround times, so documents closed
     * before turnaround tracking existed don't distort the averages.
     */
    private function typeBreakdown(): array
    {
        $names = Category::pluck('name', 'id');

        /** Documents of any status, for the Closed vs Total context column */
        $totals = $this->filteredDocuments()
            ->selectRaw('category_id, COUNT(*) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $stats = $this->closedDocuments()
            ->selectRaw('category_id, COUNT(*) as closed')
            ->selectRaw('AVG(turnaroundtime) as avg_tat')
            ->selectRaw('MIN(turnaroundtime) as min_tat')
            ->selectRaw('MAX(turnaroundtime) as max_tat')
            ->groupBy('category_id')
            ->get()
            ->keyBy('category_id');

        $buckets = ['purchase_requests' => [], 'payments' => [], 'general' => []];

        foreach ($totals as $categoryId => $total) {
            $stat = $stats->get($categoryId);
            $closed = (int) ($stat->closed ?? 0);

            $buckets[$this->bucketFor($categoryId)][] = [
                'name' => $names[$categoryId] ?? 'Uncategorized',
                'total' => (int) $total,
                'closed' => $closed,
                'avg' => $closed && $stat->avg_tat !== null ? round((float) $stat->avg_tat, 1) : null,
                'min' => $closed && $stat->min_tat !== null ? (int) $stat->min_tat : null,
                'max' => $closed && $stat->max_tat !== null ? (int) $stat->max_tat : null,
            ];
        }

        foreach ($buckets as $bucket => $rows) {
            usort($rows, function ($a, $b) {
                return [$b['closed'], $b['total']] <=> [$a['closed'], $a['total']];
            });

            $buckets[$bucket] = $rows;
        }

        return array_merge($buckets['purchase_requests'], $buckets['payments'], $buckets['general']);
    }

    public function render()
    {
        $allRows = collect($this->typeBreakdown());

        /** Overall stats across every matching closed document, not just the current page */
        $summary = $this->closedDocuments()
            ->selectRaw('COUNT(*) as closed')
            ->selectRaw('AVG(turnaroundtime) as avg_tat')
            ->selectRaw('MIN(turnaroundtime) as min_tat')
            ->selectRaw('MAX(turnaroundtime) as max_tat')
            ->first();

        $closed = (int) ($summary->closed ?? 0);

        $totals = [
            'closed' => $closed,
            'total' => $allRows->sum('total'),
            'average' => $closed && $summary->avg_tat !== null ? round((float) $summary->avg_tat, 1) : null,
            'fastest' => $closed && $summary->min_tat !== null ? (int) $summary->min_tat : null,
            'slowest' => $closed && $summary->max_tat !== null ? (int) $summary->max_tat : null,
        ];

        $page = Paginator::resolveCurrentPage('page');
        $rows = new LengthAwarePaginator(
            $allRows->slice(($page - 1) * $this->perPage, $this->perPage)->values(),
            $allRows->count(),
            $this->perPage,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page']
        );

        return view('livewire.report.turnaround-time', [
            'rows' => $rows,
            'totals' => $totals,
        ]);
    }
}
