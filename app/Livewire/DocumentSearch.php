<?php

namespace App\Livewire;

use App\Models\Document;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

class DocumentSearch extends Component
{
    public $searchQuery = '';
    public $searchResults = [];
    public $isLoading = false;
    public $selectedDocument = null;
    public $showTrackingModal = false;

    protected $listeners = ['clearSearch'];

    public function updatedSearchQuery()
    {
        if (strlen($this->searchQuery) < 2) {
            $this->searchResults = [];
            return;
        }

        $this->searchDocuments();
    }

    public function searchDocuments()
    {
        $this->isLoading = true;
        
        try {
            $results = Document::where('subject', 'like', '%' . $this->searchQuery . '%')
                ->orWhere('control_no', 'like', '%' . $this->searchQuery . '%')
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();
            
            $this->searchResults = $results->toArray();
        } catch (\Exception $e) {
            $this->searchResults = [];
            session()->flash('error', 'Search failed: ' . $e->getMessage());
        } finally {
            $this->isLoading = false;
        }
    }

    public function trackDocument($documentId)
    {
        $this->selectedDocument = collect($this->searchResults)
            ->firstWhere('id', $documentId);

        if ($this->selectedDocument) {
            $this->showTrackingModal = true;
        }
    }

    public function updatedShowTrackingModal()
    {
        if ($this->showTrackingModal) {
            // Dispatch browser event after component renders
            $this->dispatch('open-tracking-modal');
        }
    }

    public function closeTrackingModal()
    {
        $this->showTrackingModal = false;
        $this->selectedDocument = null;
    }

    public function clearSearch()
    {
        $this->searchQuery = '';
        $this->searchResults = [];
        $this->showTrackingModal = false;
        $this->selectedDocument = null;
    }

    public function render()
    {
        return view('livewire.document-search');
    }
}
