<?php

namespace App\Livewire\Partials;

use App\Models\Document;
use Livewire\Component;

class Sidebar extends Component
{
    public $statusCount;
    public $user = [];
    public $office;

    public function mount()
    {
        /** User Information */
        $this->user = session('user', []);
        
        // Check if user has office information
        if (!isset($this->user['office']['id'])) {
            $this->office = null;
            return;
        }
        
        $this->office = $this->user['office']['id'];
        /** End User Information */
    }

    public function render()
    {
        $this->statusCount = Document::where('assigned_to', $this->office)->whereIn('status', ['For Receiving', 'On Process', 'Returned'])->get();

        return view('livewire.partials.sidebar');
    }
}
