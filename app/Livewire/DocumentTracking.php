<?php

namespace App\Livewire;

use App\Models\Document;
use App\Services\ApiService;
use App\Support\Concerns\BuildsDocumentTimeline;
use Livewire\Component;

class DocumentTracking extends Component
{
    use BuildsDocumentTimeline;

    /** Constant Variables */
    /**
     * Large, rarely-changing directory data. Kept protected so it is NOT
     * serialized into the Livewire snapshot on every request; reloaded from
     * cache each request via boot().
     *
     * Both are keyed by id: the timeline resolves an office and an employee
     * name for every row, so a linear scan per row added up.
     */
    protected $offices = [];
    protected $employees = [];

    public $document;
    public $trackingData = [];

    /**
     * Runs on every request (before mount and before public-prop hydration).
     * Reloads the protected directory data from cache so it is available for
     * render and helper methods without bloating the Livewire snapshot.
     */
    public function boot()
    {
        $this->checkApiConnection();
    }

    public function mount($document)
    {
        $this->document = $document;
        $this->loadTrackingData();
    }

    /**
     * Check API server connection and fetch employee and office data
     * Returns true if successful, false otherwise
     */
    private function checkApiConnection()
    {
        $responseEmployees = app(ApiService::class)->getEmployeesData();
        $responseOffices = app(ApiService::class)->getOfficesData();

        if (!$responseEmployees || !$responseOffices) {
            $this->employees = [];
            $this->offices = [];

            return false;
        }

        $this->employees = collect($responseEmployees['employeesList'] ?? [])
            ->keyBy('id')
            ->all();

        $this->offices = collect($responseOffices['officeList'] ?? [])
            ->keyBy('id')
            ->all();

        return true;
    }

    public function openModal()
    {
        $this->dispatch('open-tracking-modal');
    }

    public function loadTrackingData()
    {
        try {
            $document = Document::with(['category', 'logs' => function ($query) {
                $query->with(['user', 'action'])
                    ->orderBy('created_at', 'desc')
                    ->orderBy('id', 'desc'); // tiebreaker for entries sharing a timestamp (Forwarded + For Receiving)
            }])
                ->where('id', $this->document['id'])
                ->first();

            // Callers may pass a flattened document array without the category
            // relation; backfill it so the modal renders on its own.
            if ($document && !isset($this->document['category'])) {
                $this->document['category'] = $document->category?->toArray();
            }

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
                return "text-gray-500 dark:text-neutral-400";
                break;
            case 'Closed':
                return "text-red-600 dark:text-red-400";
                break;
            case 'On Process':
                return "text-yellow-600 dark:text-yellow-400";
                break;
            case 'Returned':
                return "text-amber-600 dark:text-amber-400";
            default:
                return "text-sky-600 dark:text-sky-400";
        }
    }

    public function filterUser($id)
    {
        $employee = $this->employees[$id] ?? null;

        if (!$employee) {
            return '';
        }

        return $employee['firstName'] . ' ' . $employee['lastName'] . ' ' . $employee['suffix'];
    }

    public function filterOffice($id)
    {
        $office = $this->offices[$id] ?? null;

        return $office['officeName'] ?? '';
    }

    /** The search modal looks offices up by its own helper name. */
    protected function resolveTimelineOffice($id): ?string
    {
        return $this->filterOffice($id);
    }

    public function render()
    {
        return view('livewire.document-tracking');
    }
}
