<?php

namespace App\Livewire\Report;

use Livewire\Component;
use App\Models\Document;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Title;
use Livewire\WithPagination;

class Employees extends Component
{
    use WithPagination;
    use LivewireAlert;

    #[Title('Status per Employee | Document Tracking Information System')]

    /** Constant Variables */
    public $responseEmployees;
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
            $this->responseEmployees = Http::get('http://192.168.100.162:8081/public/get-employees')->json();
            
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

        return view('livewire.report.employees', [
            'employees' => $this->sortedEmployees
        ]);
    }
}
