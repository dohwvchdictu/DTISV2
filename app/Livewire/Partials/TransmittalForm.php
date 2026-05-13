<?php

namespace App\Livewire\Partials;

use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Title;
use Livewire\Component;

class TransmittalForm extends Component
{
    #[Title('Print Transmittal Form')]


    public function render()
    {
        return view('livewire.partials.transmittal-form');
    }
}
