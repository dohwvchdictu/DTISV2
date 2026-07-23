<?php

namespace App\Livewire;

use App\Models\Document;
use App\Services\ApiService;
use Livewire\Component;

class DocumentTracking extends Component
{
    /** Constant Variables */
    public $offices = [];
    public $user = [];
    public $endorsedID;
    public $responseOffices;
    public $responseEmployees;
    public $employees = [];

    public $document;
    public $trackingData = [];
    public $isLoading = true;
    public $id;

    public function mount($document)
    {
        $this->document = $document;
        $this->loadTrackingData();
        $this->checkApiConnection();
    }

    /**
     * Check API server connection and fetch employee and office data
     * Returns true if successful, false otherwise
     */
    private function checkApiConnection()
    {
        $this->responseEmployees = app(ApiService::class)->getEmployeesData();
        $this->responseOffices = app(ApiService::class)->getOfficesData();

        if (!$this->responseEmployees || !$this->responseOffices) {
            $this->employees = [];
            $this->offices = [];
            $this->responseEmployees = null;
            $this->responseOffices = null;

            return false;
        }

        $this->employees = collect($this->responseEmployees['employeesList'] ?? [])
            ->sortBy('lastName')
            ->values()
            ->all();

        $this->offices = collect($this->responseOffices['officeList'] ?? [])
            ->sortBy('officeName')
            ->values()
            ->all();

        return true;
    }


    public function openModal()
    {
        $this->dispatch('open-tracking-modal');
    }

    public function loadTrackingData()
    {
        $this->isLoading = true;

        try {
            $document = Document::with(['logs' => function ($query) {
                $query->with(['user', 'action'])
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc'); // tiebreaker for entries sharing a timestamp (Forwarded + For Receiving)
            }])
                ->where('id', $this->document['id'])
                ->first();

            if ($document && $document->logs) {
                $this->trackingData = $document->logs->map(function ($log) {
                    $logArray = $log->toArray();
                    // Add office_id to the log data for display
                    if (isset($log->office_id)) {
                        $logArray['office_id'] = $log->office_id;
                    }
                    return $logArray;
                })->toArray();
            } else {
                $this->trackingData = [];
            }
        } catch (\Exception $e) {
            $this->trackingData = [];
            session()->flash('error', 'Error loading tracking data: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    public function closeModal()
    {
        $this->dispatch('close-tracking-modal');
    }

    public function colorIndicator($status)
    {
        switch ($status) {
            case 'Created':
                return "text-gray-500";
                break;
            case 'Closed':
                return "text-red-600";
                break;
            case 'On Process':
                return "text-yellow-600";
                break;
            case 'Returned':
                return "text-amber-600";
            default:
                return "text-sky-600";
        }
    }

    public function completeName()
    {
        return $this->user['firstName'] . ' ' . $this->user['lastName'] . ' ' . $this->user['suffix'];
    }

    public function filterUser($id)
    {
        $result = array_values(array_filter($this->employees, function ($employee) use ($id) {
            return $employee['id'] == $id;
        }));

        if (!isset($result[0])) {
            return '';
        }

        $findUser = $result[0];
        return $findUser['firstName'] . ' ' . $findUser['lastName'] . ' ' . $findUser['suffix'];
    }

    public function filterOffice($id)
    {
        if (!isset($id)) {
            return '';
        }

        $result = array_values(array_filter($this->offices, function ($office) use ($id) {
            return $office['id'] == $id;
        }));

        if (!isset($result[0])) {
            return '';
        }

        $findOffice = $result[0];
        return $findOffice['officeName'] ?? '';
    }

    public function render()
    {
        return view('livewire.document-tracking');
    }
}
