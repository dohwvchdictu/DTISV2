<?php

namespace App\Livewire\Report;

use Livewire\Component;
use App\Models\Document;
use App\Services\ApiService;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

class Employees extends Component
{
    use WithPagination;
    use LivewireAlert;

    #[Title('Status per Employee | Document Tracking Information System')]

    /** Constant Variables */
    /**
     * Full employee list (~400 KB) is only used inside mount() to derive the
     * small per-office $employees subset below. Kept protected so it is never
     * serialized into the Livewire snapshot.
     */
    protected $responseEmployees;
    public $employees = [];
    public $sortedEmployees = [];
    public $response;
    public $percentage;
    public $user = [];
    public $office;
    public $personnel;

    /** Filter Date Variables */
    public $startDate;
    public $endDate;

    public function mount()
    {
        /** User Information */
        $this->user = session('user', []);
        $this->office = $this->user['office']['id'] ?? null;

        // Check if user has office information
        if (!isset($this->user['office']['id'])) {
            $this->alert('error', 'User office information not found in session.');
            return;
        }

        /** Filter Records last 30 days */
        $this->startDate = Carbon::now()->subMonth(1)->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');

        /** API for Employees */
        try {
            $this->responseEmployees = app(ApiService::class)->getEmployeesData();

            // Check if API response is valid
            if (!isset($this->responseEmployees['employeesList']) || !is_array($this->responseEmployees['employeesList'])) {
                $this->employees = [];
                $this->alert('warning', 'No employee data available.');
                return;
            }

            $this->employees = array_filter($this->responseEmployees['employeesList'], function ($office) {
                return isset($office['office']['id']) && $office['office']['id'] == $this->user['office']['id'];
            });
        } catch (\Exception $e) {
            $this->employees = [];
            $this->alert('error', 'Failed to fetch employee data: ' . $e->getMessage());
        }
    }

    public function documentsPercentage($incoming, $pending, $processed)
    {
        $total = $incoming + $pending + $processed;
        return $this->percentage = $processed ? ($processed / $total) * 100 : 0;
    }

    public function render()
    {
        $this->sortedEmployees = Arr::sort($this->employees, function (array $value) {
            return $value['lastName'];
        });

        $start = Carbon::parse($this->startDate)->subDay();
        $end = Carbon::parse($this->endDate)->addDay();

        /**
         * Pre-aggregate per-employee counts in three grouped queries instead of
         * the blade running 3 count queries per employee. The blade looks each
         * employee up by id from these keyed collections.
         */
        $incomingByEmployee = Document::query()
            ->whereIn('status', ['For Receiving', 'Returned'])
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('endorsed_to, COUNT(*) as aggregate')
            ->groupBy('endorsed_to')
            ->pluck('aggregate', 'endorsed_to');

        $pendingByEmployee = Document::query()
            ->whereIn('status', ['On Process', 'Endorsed'])
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('endorsed_to, COUNT(*) as aggregate')
            ->groupBy('endorsed_to')
            ->pluck('aggregate', 'endorsed_to');

        // Documents this office acted on (Forwarded/Closed), grouped by the acting employee.
        $processedByEmployee = Document::query()
            ->join('logs', 'logs.document_id', '=', 'documents.id')
            ->where('logs.assigned_to', $this->office)
            ->whereIn('logs.action_id', [3, 5])
            ->whereBetween('documents.created_at', [$start, $end])
            ->selectRaw('logs.user_id, COUNT(DISTINCT documents.id) as aggregate')
            ->groupBy('logs.user_id')
            ->pluck('aggregate', 'logs.user_id');

        return view('livewire.report.employees', [
            'employees' => $this->sortedEmployees,
            'incomingByEmployee' => $incomingByEmployee,
            'pendingByEmployee' => $pendingByEmployee,
            'processedByEmployee' => $processedByEmployee,
        ]);
    }
}
