<?php

namespace App\Livewire\Report;

use App\Models\Document;
use App\Services\ApiService;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Title;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class DocumentStatus extends Component
{
    use WithPagination;
    use LivewireAlert;

    #[Title('Status of Documents | Document Tracking Information System')]

    /** Constant Variables */
    /** Office directory kept protected so it is not serialized into the Livewire snapshot; reloaded from cache in boot(). */
    protected $offices = [];
    protected $response;
    public $percentage;

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
        /** Filter Records last 30 days */
        $this->startDate = Carbon::now()->subMonth(1)->format('Y-m-d');
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

    public function documentsPercentage($incoming, $pending, $processed)
    {
        $total = $incoming + $pending + $processed;
        return $this->percentage = $processed ? ($processed / $total) * 100 : 0;
    }

    public function render()
    {
        $start = Carbon::parse($this->startDate)->addDay(1);
        $end = Carbon::parse($this->endDate)->addDay(1);

        /**
         * Pre-aggregate the per-office counts in three grouped queries instead
         * of the blade running 3 count queries per office (~150 queries). The
         * blade now looks each office up by id from these keyed collections.
         */
        $incomingByOffice = Document::query()
            ->whereIn('status', ['For Receiving', 'Returned'])
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('assigned_to, COUNT(*) as aggregate')
            ->groupBy('assigned_to')
            ->pluck('aggregate', 'assigned_to');

        $pendingByOffice = Document::query()
            ->where('status', 'On Process')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('assigned_to, COUNT(*) as aggregate')
            ->groupBy('assigned_to')
            ->pluck('aggregate', 'assigned_to');

        // Processed = documents this office Forwarded (3) or Closed (5) in the window.
        // (Previously the blade passed the whole $office array to where('assigned_to', ...),
        //  which is corrected here to group by assigned_to.)
        $processedByOffice = \App\Models\Log::query()
            ->whereIn('action_id', [3, 5])
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('assigned_to, COUNT(*) as aggregate')
            ->groupBy('assigned_to')
            ->pluck('aggregate', 'assigned_to');

        $offices = Arr::sort($this->offices, function (array $value) {
            return $value['officeName'];
        });

        return view('livewire.report.document-status', [
            'offices' => $offices,
            'incomingByOffice' => $incomingByOffice,
            'pendingByOffice' => $pendingByOffice,
            'processedByOffice' => $processedByOffice,
        ]);
    }
}
