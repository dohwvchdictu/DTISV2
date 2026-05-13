<?php

namespace App\Livewire\Inbox;

use Livewire\Attributes\Title;
use Livewire\Component;

class MyBundles extends Component
{
    #[Title('My Bundles | Document Tracking Information System')]
    public function render()
    {
        return view('livewire.inbox.my-bundles');
    }
}
