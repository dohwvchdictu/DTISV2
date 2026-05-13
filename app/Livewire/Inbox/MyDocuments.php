<?php

namespace App\Livewire\Inbox;


use Livewire\Attributes\Title;
use Livewire\Component;

class MyDocuments extends Component
{
    #[Title('My Documents | Document Tracking Information System')]

    public function render()
    {
        return view('livewire.inbox.my-documents');
    }
}
