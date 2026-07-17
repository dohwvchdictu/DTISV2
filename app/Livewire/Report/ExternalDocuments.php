<?php

namespace App\Livewire\Report;

use App\Models\Action;
use App\Models\Document;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

class ExternalDocuments extends Component
{
    use WithPagination;

    #[Title('External Requests | Document Tracking Information System')]

    /** Constant Variables */
    public $user = [];
    public $office;
    public $offices = [];
    public $employees = [];
    public $response;

    /** Filter Date Variables */
    public $startDate;
    public $endDate;

    public function mount()
    {
        $this->user = session('user');
        $this->office = $this->user['office']['id'];

        /** Filter Records last 30 days */
        $this->startDate = Carbon::now()->subMonth(1)->format('Y-m-d');
        $this->endDate = Carbon::now()->format('Y-m-d');

        $this->checkApiConnection();

    }

    public function checkApiConnection()
    {
        /** API */
        $officeResponse = Http::get(config('services.api.base_url') . 'public/get-offices');

        if(!$officeResponse->ok())
        {
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

        $employeeResponse = Http::get(config('services.api.base_url') . 'public/get-employees');
        if ($employeeResponse->ok()) {
            $this->employees = collect($employeeResponse->json()['employeesList'] ?? [])
                ->keyBy('id')
                ->toArray();
        }

        return true;
    }

    public function updatedStartDate()
    {
        $this->resetPage();
    }

    public function updatedEndDate()
    {
        $this->resetPage();
    }

    public function getOfficeName($officeId): string
    {
        $found = collect($this->offices)->firstWhere('id', $officeId);
        return $found['officeName'] ?? '—';
    }

    public function getEmployeeName($userId): string
    {
        if (! $userId || ! isset($this->employees[$userId])) {
            return '—';
        }

        $employee = $this->employees[$userId];
        return trim(($employee['firstName'] ?? '') . ' ' . ($employee['lastName'] ?? '') . ' ' . ($employee['suffix'] ?? '')) ?: '—';
    }

    /**
     * Determine which Day column (3, 7, or 20) a document belongs to based on
     * its citizen charter's required days, and its tracking state for the
     * legend colors. Documents without a charter default to 20 days.
     *
     * Complete  = document is closed
     * Due       = deadline is today or within the next 2 days
     * Overdue   = past the deadline and not yet closed
     * Pending   = still in process, deadline not yet near
     */
    public function trackingStatus(Document $document): array
    {
        $turnaround = $document->citizencharter->required_days ?? 20;

        if ($turnaround <= 3) {
            $column = 3;
        } elseif ($turnaround <= 7) {
            $column = 7;
        } else {
            $column = 20;
        }

        $dueDate = $document->created_at->copy()->startOfDay()->addDays($turnaround);
        $today = Carbon::today();

        if ($document->status === 'Closed') {
            $state = 'complete';
            $label = 'Complete';
        } elseif ($dueDate->lt($today)) {
            $state = 'overdue';
            $label = 'Overdue';
        } elseif ($dueDate->lte($today->copy()->addDays(2))) {
            $state = 'due';
            $daysLeft = $today->diffInDays($dueDate);
            $label = $daysLeft === 0 ? 'Due today' : 'Due in ' . $daysLeft . ' ' . ($daysLeft === 1 ? 'day' : 'days');
        } else {
            $state = 'pending';
            $label = 'Pending';
        }

        return [
            'column' => $column,
            'state' => $state,
            'label' => $label,
        ];
    }

    /**
     * The first office the document was routed to after encoding, taken from
     * the earliest "For Receiving" log. The "Forwarded" log is skipped because
     * its assigned_to points to the sending office, not the destination.
     */
    public function firstDestination(Document $document): string
    {
        $forReceivingActionId = Cache::rememberForever('action_id_for_receiving', fn() => Action::where('name', 'For Receiving')->value('id'));

        $log = $document->logs
            ->where('action_id', $forReceivingActionId)
            ->whereNotNull('assigned_to')
            ->sortBy('created_at')
            ->first();

        return $log ? $this->getOfficeName($log->assigned_to) : '—';
    }

    /**
     * The office currently holding the document. Falls back to the
     * originating office when it has not been forwarded yet.
     */
    public function currentLocation(Document $document): string
    {
        return $this->getOfficeName($document->assigned_to ?? $document->office_id);
    }

    public function latestRemarks(Document $document): string
    {
        $log = $document->logs
            ->whereNotNull('remarks')
            ->sortByDesc('created_at')
            ->first();

        return $log->remarks ?? '';
    }

    public function render()
    {
        $documents = Document::with(['logs', 'citizencharter'])
            ->where('source', 'external')
            ->where('office_id', $this->office)
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('livewire.report.external-documents', [
            'documents' => $documents
        ]);
    }
}
