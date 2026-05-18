<?php

namespace App\Livewire;

use App\Models\Document;
use Illuminate\Support\Facades\Http;
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
        $employeeResponse = Http::get(config('services.api.base_url') . 'public/get-employees');
        $officeResponse = Http::get(config('services.api.base_url') . 'public/get-offices');

        if (!$employeeResponse->ok() || !$officeResponse->ok()) {
            $this->employees = [];
            $this->offices = [];
            $this->responseEmployees = null;
            $this->responseOffices = null;

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

        $this->responseEmployees = $employeeResponse->json();
        $this->responseOffices = $officeResponse->json();

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
                    ->orderBy('created_at', 'desc');
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
        $this->id = $id;

        $result = array_filter($this->employees, function ($employee) {
            return $employee['id'] == $this->id;
        });

        $result = array_values($result); // reindex array
        $findUser = $result[0];
        return $findUser['firstName'] . ' ' . $findUser['lastName'] . ' ' . $findUser['suffix'];
    }

    public function filterOffice($id)
    {
        if (!isset($id)) {
            return '';
        }

        $this->id = $id;

        $result = array_filter($this->offices, function ($office) {
            return $office['id'] == $this->id;
        });

        $result = array_values($result); // reindex array

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
