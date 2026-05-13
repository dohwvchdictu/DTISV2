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
        $this->photoUrl = $this->user['photoUrl'] ?? $this->photoUrl;
        /** End User Information */

        // Only fetch photo if we have a valid photoUrl and it's not the default
        if ($this->photoUrl && $this->photoUrl !== 'https://images.unsplash.com/photo-1568602471122-7832951cc4c5?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=facearea&facepad=2&w=300&h=300&q=80') {
            $this->fetchAndStorePhoto();
        }
    }

    private function fetchAndStorePhoto()
    {
        // Get JWT token
        $this->jwtToken = session('jwt_token');

    // Extract filename from photoUrl - handle both full URLs and just filenames
        $photoIdentifier = $this->photoUrl;
        
        // If photoUrl is a full URL, extract just the filename/identifier
        if (filter_var($this->photoUrl, FILTER_VALIDATE_URL)) {
            // If it's a URL, use the default photo directly (external URL)
            $this->image = $this->photoUrl;
            return;
        }
        
        // If it's not a URL, treat it as a filename/identifier for the API
        $filename = basename($photoIdentifier);
        if (empty($filename) || !pathinfo($filename, PATHINFO_EXTENSION)) {
            $filename = 'photo_' . ($this->user['id'] ?? 'unknown') . '.jpg';
        }

        $imagePath = 'photos/' . $filename;
        
        // Check if photo already exists locally
        if (Storage::disk('public')->exists($imagePath)) {
            $this->image = asset('storage/' . $imagePath);
            return;
        }

        // Define the API endpoint - fix the URL construction
        $apiEndpoint = 'http://192.168.100.162:8081/employee/image/' . urlencode($this->photoUrl);

        try {
            // Make the request with timeout
            $response = Http::withToken($this->jwtToken)
                ->timeout(10)
                ->get($apiEndpoint);

            // Check if the request was successful
            if ($response->successful()) {
                $imageContent = $response->body();
                
                // Validate that we received actual image content
                if (!empty($imageContent) && strlen($imageContent) > 100) {
                    // Ensure the photos directory exists
                    if (!Storage::disk('public')->exists('photos')) {
                        Storage::disk('public')->makeDirectory('photos');
                    }
                    
                    // Store the image
                    Storage::disk('public')->put($imagePath, $imageContent);
                    $this->image = asset('storage/' . $imagePath);
                } else {
                    // Invalid image content, use default
                    $this->image = $this->photoUrl;
                }
            } else {
                // API call failed, use default photo
                $this->image = $this->photoUrl;
                \Log::warning('Failed to fetch user photo', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'user_id' => $this->user['id'] ?? 'unknown'
                ]);
            }
        } catch (\Exception $e) {
            // Handle exceptions, use default photo
            $this->image = $this->photoUrl;
            \Log::error('Error fetching user photo', [
                'message' => $e->getMessage(),
                'user_id' => $this->user['id'] ?? 'unknown',
                'photo_url' => $this->photoUrl
            ]);
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
