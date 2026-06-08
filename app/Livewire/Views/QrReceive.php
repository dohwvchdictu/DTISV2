<?php

namespace App\Livewire\Views;

use App\Models\Action;
use App\Models\Document;
use App\Models\Log;
use Illuminate\Support\Facades\Http;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class QrReceive extends Component
{
    use LivewireAlert;

    #[Layout('components.layouts.app')]
    #[Title('Receive Document | DTIS')]

    public Document $document;
    public $user = [];
    public $office;
    public $officeName = '';
    public $state = 'confirm'; // confirm | already_received | wrong_office | closed | done

    protected $listeners = ['confirmReceive'];

    public function mount(string $control_no): void
    {
        $this->user   = session('user');
        $this->office = $this->user['office']['id'];

        $doc = Document::with('category')->where('control_no', $control_no)->first();

        if (! $doc) {
            $this->state = 'not_found';
            return;
        }

        $this->document = $doc;

        if ($doc->status === 'Closed') {
            $this->state = 'closed';
            return;
        }

        if ((int) $doc->assigned_to !== (int) $this->office) {
            $this->state = 'wrong_office';
            return;
        }

        if (! in_array($doc->status, ['For Receiving', 'Returned'])) {
            $this->state = 'already_received';
            return;
        }

        $this->officeName = $this->user['office']['officeName'] ?? '';
    }

    public function promptConfirm(): void
    {
        $this->alert('info', 'Confirm receipt of this document?', [
            'position'          => 'center',
            'toast'             => false,
            'timer'             => null,
            'showConfirmButton' => true,
            'confirmButtonText' => 'Yes, Receive',
            'onConfirmed'       => 'confirmReceive',
            'confirmButtonColor'=> '#059669',
            'showCancelButton'  => true,
            'cancelButtonText'  => 'Cancel',
        ]);
    }

    public function confirmReceive(): void
    {
        // Re-validate state in case of concurrent actions
        $this->document->refresh();

        if (! in_array($this->document->status, ['For Receiving', 'Returned'])) {
            $this->state = 'already_received';
            return;
        }

        $this->document->update([
            'assigned_to' => $this->office,
            'status'      => 'On Process',
        ]);

        $docType = $this->document->is_bundle ? 'Bundle' : 'Document';

        Log::create([
            'action_id'   => Action::firstWhere('name', 'Received')->id,
            'document_id' => $this->document->id,
            'user_id'     => $this->user['id'],
            'office_id'   => $this->office,
            'assigned_to' => $this->office,
            'endorsed_to' => $this->document->endorsed_to,
            'description' => $docType . ' (' . $this->document->control_no . ') has been received via QR scan and is being processed by ' . $this->officeName . '.',
        ]);

        // Also receive any documents attached to this bundle
        $attached = Document::where('bundle_id', $this->document->id)
            ->whereIn('status', ['For Receiving', 'Returned'])
            ->get();

        foreach ($attached as $attachment) {
            $attachment->update([
                'assigned_to' => $this->office,
                'status'      => 'On Process',
            ]);

            Log::create([
                'action_id'   => Action::firstWhere('name', 'Received')->id,
                'document_id' => $attachment->id,
                'bundle_id'   => $this->document->id,
                'user_id'     => $this->user['id'],
                'office_id'   => $attachment->office_id,
                'assigned_to' => $this->office,
                'endorsed_to' => $this->document->endorsed_to,
                'description' => $docType . ' (' . $this->document->control_no . ') has been received via QR scan and is being processed by ' . $this->officeName . '.',
            ]);
        }

        $this->state = 'done';
    }

    public function render()
    {
        return view('livewire.views.qr-receive');
    }
}
