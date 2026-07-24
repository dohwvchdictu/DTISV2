<?php

namespace App\Livewire\Partials;

use Illuminate\Http\Request;
use Livewire\Component;
use Illuminate\Support\Facades\Storage;

class Navbar extends Component
{
    public $user = [];
    public $office;
    public $photoUrl = 'https://images.unsplash.com/photo-1568602471122-7832951cc4c5?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=facearea&facepad=2&w=300&h=300&q=80';
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

        // The photo is fetched and cached at login; here we only read that
        // cached copy — no API call and no token required.
        $this->image = $this->resolveCachedPhoto();
    }

    /**
     * Resolve the avatar from what was cached at login (or a copy stored by a
     * previous login), falling back to the default avatar. Never calls the
     * API, so it needs no token and cannot stall the page.
     */
    private function resolveCachedPhoto(): string
    {
        // Preferred: whatever login cached for this session.
        if ($cached = session('user_photo')) {
            return $cached;
        }

        // Fall back to a file cached under photos/ by an earlier login.
        $photoUrl = $this->user['photoUrl'] ?? null;
        if ($photoUrl && !filter_var($photoUrl, FILTER_VALIDATE_URL)) {
            $imagePath = 'photos/' . basename($photoUrl);
            if (Storage::disk('public')->exists($imagePath)) {
                return asset('storage/' . $imagePath);
            }
        }

        // Nothing cached — use an external URL as-is, else the default avatar.
        return $photoUrl && filter_var($photoUrl, FILTER_VALIDATE_URL)
            ? $photoUrl
            : $this->photoUrl;
    }

    public function getEmployeePhoto(Request $request, string $filename)
    {
        $filename = basename($filename);
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            abort(404);
        }

        $imagePath = 'photos/' . $filename;

        if (!Storage::disk('public')->exists($imagePath)) {
            abort(404);
        }

        return response(Storage::disk('public')->get($imagePath))
            ->header('Content-Type', Storage::disk('public')->mimeType($imagePath))
            ->header('Cache-Control', 'public, max-age=86400');
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
