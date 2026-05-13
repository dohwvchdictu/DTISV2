<?php

namespace App\Livewire\Partials;

use App\Models\Document;
use App\Models\Log;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Title;
use Livewire\Component;

class Logbook extends Component
{
    #[Title('Generate Logbook')]
    
    public $selectedItems = [];
    public $documents = [];
    public $offices = [];

    public function mount()
    {
        // Get selected_items from query parameter
        $selectedItemsParam = request()->get('selected_items', '');
        
        if (!empty($selectedItemsParam)) {
            $this->selectedItems = explode(',', $selectedItemsParam);
            $this->loadDocuments();
            $this->loadOffices();
        }
    }

    public function loadDocuments()
    {
        if (!empty($this->selectedItems)) {
            $this->documents = Document::with(['category', 'logs' => function($query) {
                $query->with(['action', 'user'])->orderBy('created_at', 'asc');
            }])
            ->whereIn('id', $this->selectedItems)
            ->orderBy('created_at', 'desc')
            ->get();
        }
    }

    public function loadOffices()
    {
        try {
            $response = Http::get('http://192.168.100.162:8081/public/get-offices');
            
            if ($response->ok()) {
                $data = $response->json();
                $this->offices = collect($data['officeList'] ?? [])
                    ->keyBy('id')
                    ->toArray();
            }
        } catch (\Exception $e) {
            $this->offices = [];
        }
    }

    public function getOfficeName($officeId)
    {
        if (!$officeId || !isset($this->offices[$officeId])) {
            return 'N/A';
        }
        
        return $this->offices[$officeId]['officeName'] ?? 'Unknown Office';
    }

    public function render()
    {
        return view('livewire.partials.logbook');
    }
}
