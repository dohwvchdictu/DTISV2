<?php

namespace App\Livewire\Partials;

use App\Models\Document;
use Illuminate\Support\Facades\Http;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;

class Navbar extends Component
{
    public $user = [];
    public $office;
    public $photoUrl = 'https://images.unsplash.com/photo-1568602471122-7832951cc4c5?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=facearea&facepad=2&w=300&h=300&q=80';
    public $jwtToken;
    public $image;
    public $documents = [];


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
        
        // Validate photoUrl exists before using it
        if (!isset($this->user['photoUrl']) || empty($this->user['photoUrl'])) {
            // Use default photo URL if not available
            $this->image = $this->photoUrl;
            return;
        }
        
        $this->photoUrl = $this->user['photoUrl'];
        /** End User Information */

        try {
            // Define the API endpoint
            $apiEndpoint = config('services.api.base_url') . 'employee/image/' . $this->user['photoUrl'];
            // Your JWT token (e.g., retrieved from a logged-in user or storage)
            $this->jwtToken = session('jwt_token');

            // Make the request
            $response = Http::withToken($this->jwtToken)
                ->get($apiEndpoint);

            // Check if the request was successful
            if ($response->successful()) {
                // Assume the API returns the photo URL or binary data
                $imageContent = $response->getBody();
                $imagePath = 'photos/' . $this->user['photoUrl'];
                Storage::disk('public')->put($imagePath, $imageContent);
                $this->image = asset('storage/' . $imagePath);
                return $this->image;
            } else {
                // Handle error responses - use default photo
                $this->image = $this->photoUrl;
                session()->flash('error', 'Failed to fetch user photo: ' . $response->body());
            }
        } catch (\Exception $e) {
            // Handle exceptions - use default photo
            $this->image = $this->photoUrl;
            session()->flash('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function completeName()
    {
        return $this->user['firstName'] . ' ' . $this->user['lastName'] . $this->user['suffix'];
    }

    public function render()
    {
        return view('livewire.partials.navbar');
    }
}
