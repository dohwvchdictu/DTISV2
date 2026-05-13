<?php

namespace App\Livewire\Documents;

use Livewire\Attributes\Title;
use Livewire\Component;

class NewBundle extends Component
{
    #[Title('DTIS - New Bundle')]
    public function render()
    {
        return view('livewire.documents.new-bundle');
    }
}
