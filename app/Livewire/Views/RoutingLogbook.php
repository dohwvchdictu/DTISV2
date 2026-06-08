<?php

namespace App\Livewire\Views;

use App\Models\Action;
use App\Models\Document;
use App\Models\Log;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class RoutingLogbook extends Component
{
    #[Layout('components.layouts.app')]
    #[Title('Routing Logbook | DTIS')]

    public $user   = [];
    public $office;
    public $offices = [];
    public $from;
    public $to;

    public function mount(): void
    {
        $this->user   = session('user');
        $this->office = $this->user['office']['id'];
        $this->from   = now()->subDay()->toDateString();
        $this->to     = now()->toDateString();

        $response = Http::get(config('services.api.base_url') . 'public/get-offices');
        if ($response->ok()) {
            $this->offices = $response->json()['officeList'] ?? [];
        }
    }

    public function resetDates(): void
    {
        $this->from = now()->subDay()->toDateString();
        $this->to   = now()->toDateString();
    }

    public function getOfficeName(int $officeId): string
    {
        $found = collect($this->offices)->firstWhere('id', $officeId);
        return $found['officeName'] ?? '—';
    }

    public function render()
    {
        $forReceivingActionId = Action::where('name', 'For Receiving')->value('id');
        $receivedActionId     = Action::where('name', 'Received')->value('id');

        $logs = Log::where('office_id', $this->office)
            ->where('action_id', $forReceivingActionId)
            ->whereDate('created_at', '>=', $this->from)
            ->whereDate('created_at', '<=', $this->to)
            ->orderByDesc('created_at')
            ->get()
            ->unique('document_id')
            ->values();

        $documentIds   = $logs->pluck('document_id')->filter();
        $assignedToMap = $logs->pluck('assigned_to', 'document_id');

        $docs = Document::with('category')
            ->whereIn('id', $documentIds)
            ->get()
            ->keyBy('id');

        $receivedLogs = Log::whereIn('document_id', $documentIds)
            ->where('action_id', $receivedActionId)
            ->get()
            ->filter(fn($rl) => isset($assignedToMap[$rl->document_id]) && $rl->office_id == $assignedToMap[$rl->document_id])
            ->keyBy('document_id');

        return view('livewire.views.routing-logbook', [
            'logs'         => $logs,
            'docs'         => $docs,
            'receivedLogs' => $receivedLogs,
        ]);
    }
}
