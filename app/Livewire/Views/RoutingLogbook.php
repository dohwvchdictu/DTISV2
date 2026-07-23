<?php

namespace App\Livewire\Views;

use App\Models\Action;
use App\Models\Document;
use App\Models\Log;
use App\Services\ApiService;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class RoutingLogbook extends Component
{
    #[Layout('components.layouts.app')]
    #[Title('Routing Logbook | DTIS')]

    public $user   = [];
    public $office;
    /**
     * Large, rarely-changing directory data. Kept protected so it is NOT
     * serialized into the Livewire snapshot on every request; reloaded from
     * cache each request via boot().
     */
    protected $offices = [];
    protected $employees = [];
    public $from;
    public $to;

    /**
     * Runs on every request (before mount and before public-prop hydration).
     * Reloads the protected directory data from cache so it is available for
     * render and helper methods without bloating the Livewire snapshot.
     */
    public function boot(): void
    {
        $officeData = app(ApiService::class)->getOfficesData();
        if ($officeData) {
            $this->offices = $officeData['officeList'] ?? [];
        }

        $employeeData = app(ApiService::class)->getEmployeesData();
        if ($employeeData) {
            $this->employees = collect($employeeData['employeesList'] ?? [])
                ->keyBy('id')
                ->toArray();
        }
    }

    public function mount(): void
    {
        $this->user   = session('user');
        $this->office = $this->user['office']['id'];
        $this->from   = now()->subDay()->toDateString();
        $this->to     = now()->toDateString();
    }

    public function resetDates(): void
    {
        $this->from = now()->subDay()->toDateString();
        $this->to   = now()->toDateString();
    }

    public function getOffice(int $officeId): array
    {
        $found = collect($this->offices)->firstWhere('id', $officeId);

        return [
            'code' => $found['officeCode'] ?? '—',
            'name' => $found['officeName'] ?? '—',
        ];
    }

    public function getReceiverName($userId): string
    {
        if (! $userId || ! isset($this->employees[$userId])) {
            return '—';
        }

        $employee = $this->employees[$userId];
        return trim(($employee['firstName'] ?? '') . ' ' . ($employee['lastName'] ?? '') . ' ' . ($employee['suffix'] ?? '')) ?: '—';
    }

    public function render()
    {
        $forReceivingActionId = Cache::rememberForever('action_id_for_receiving', fn() => Action::where('name', 'For Receiving')->value('id'));
        $receivedActionId     = Cache::rememberForever('action_id_received', fn() => Action::where('name', 'Received')->value('id'));

        $logs = Log::where('office_id', $this->office)
            ->where('action_id', $forReceivingActionId)
            ->whereDate('created_at', '>=', $this->from)
            ->whereDate('created_at', '<=', $this->to)
            ->orderByDesc('created_at')
            ->get();

        $documentIds = $logs->pluck('document_id')->filter()->unique();

        $docs = Document::with('category')
            ->whereIn('id', $documentIds)
            ->get()
            ->keyBy('id');

        // Key by composite document_id + office_id so each forwarding event
        // matches its own received log, even if a document was forwarded multiple times.
        $receivedLogs = Log::whereIn('document_id', $documentIds)
            ->where('action_id', $receivedActionId)
            ->get()
            ->keyBy(fn($rl) => $rl->document_id . '_' . $rl->office_id);

        return view('livewire.views.routing-logbook', [
            'logs'         => $logs,
            'docs'         => $docs,
            'receivedLogs' => $receivedLogs,
        ]);
    }
}
