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
    public $offices = [];
    public $response;
    public $percentage;

    /** Filter Date Variables */
    public $startDate;
    public $endDate;

    public function mount()
    {
        /** Filter Records last 30 days */
        $this->startDate = Carbon::now()->subMonth(1)->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');

       $this->checkApiConnection();
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

    public function documentsPercentage($incoming, $pending, $processed)
    {
        $total = $incoming + $pending + $processed;
        return $this->percentage = $processed ? ($processed / $total) * 100 : 0;
    }

    public function render()
    {

        $this->offices = Arr::sort($this->offices, function (array $value) {
            return $value['officeName'];
        });

        return view('livewire.report.document-status', [
            'offices' => $this->offices
        ]);
    }
}
