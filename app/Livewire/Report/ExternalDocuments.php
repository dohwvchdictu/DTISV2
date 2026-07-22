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

    public function getOfficeShortName($officeId): string
    {
        $found = collect($this->offices)->firstWhere('id', $officeId);
        return $found['officeCode'] ?? ($found['officeName'] ?? '—');
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
     * Working-day countdown to a document's deadline, based on its category's
     * required days. Documents without a category default to 20 days. The
     * deadline is the created date plus that many working days (Mon–Fri;
     * weekends excluded, holidays not accounted for).
     *
     * The signed `remaining` is the number of working days left (negative when
     * overdue, null when already closed) and drives the state used for the
     * legend colors:
     *
     * Complete  = document is closed
     * Due       = deadline is today or within the next 2 working days
     * Overdue   = past the deadline and not yet closed
     * Pending   = still in process, deadline not yet near
     */
    public function trackingStatus(Document $document): array
    {
        $requiredDays = (int) ($document->category->required_days ?? 20);

        $dueDate = $document->created_at->copy()->startOfDay()->addWeekdays($requiredDays);
        $today = Carbon::today();

        if ($document->status === 'Closed') {
            return [
                'required_days' => $requiredDays,
                'remaining' => null,
                'state' => 'complete',
                'label' => 'Completed',
            ];
        }

        /** Signed working days between today and the deadline: >0 left, <0 overdue */
        $remaining = (int) $today->diffInWeekdays($dueDate, false);

        if ($remaining < 0) {
            $overdue = abs($remaining);
            $state = 'overdue';
            $label = $overdue . ' ' . ($overdue === 1 ? 'day' : 'days') . ' overdue';
        } elseif ($remaining === 0) {
            $state = 'due';
            $label = 'Due today';
        } elseif ($remaining <= 2) {
            $state = 'due';
            $label = 'Due in ' . $remaining . ' ' . ($remaining === 1 ? 'day' : 'days');
        } else {
            $state = 'pending';
            $label = $remaining . ' days left';
        }

        return [
            'required_days' => $requiredDays,
            'remaining' => $remaining,
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

        return $log ? $this->getOfficeShortName($log->assigned_to) : '—';
    }

    /**
     * The office currently holding the document. Falls back to the
     * originating office when it has not been forwarded yet.
     */
    public function currentLocation(Document $document): string
    {
        return $this->getOfficeShortName($document->assigned_to ?? $document->office_id);
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
        $documents = Document::with(['logs', 'category'])
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
