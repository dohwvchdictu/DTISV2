<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Services\ApiService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

class LoginPage extends Component
{
    public $email;
    public $password;
    public $errorMessage;

    protected $rules = [
        'email' => 'required|email',
        'password' => 'required|min:6',
    ];

    #[Layout('components.layouts.login')]
    #[Title('DTIS | Document Tracking Information System')]

    public function mount()
    {
        $user = session('user');

        // Identity now lives in the Laravel session (SESSION_LIFETIME), not
        // the 5-minute API token, so an existing session is enough to skip
        // the login page — no token refresh needed.
        if (session('jwt_token') && isset($user['office']['id'])) {
            return redirect()->route('dashboard');
        }
    }

    public function authenticate(ApiService $apiService)
    {
        $this->validate();

        $this->errorMessage = null;

        $credentials = [
            'email' => $this->email,
            'password' => $this->password,
        ];

        $response = $apiService->login($credentials);

        // Check if the response is valid
        if (!$response || !is_array($response)) {
            $this->errorMessage = 'An unexpected error occurred. Please try again.';
            return;
        }

        // Handle successful authentication
        if (isset($response['success']) && $response['success'] === true) {
            $data = $response['data'];
            session([
                'jwt_token' => $data['token'],
                'user' => $data['employee'],
                'auth_email' => $this->email,
                'token_created_at' => time(),
            ]);

            // The API token is only ever used to fetch the profile photo, and
            // it expires in 5 minutes — so fetch and cache the photo now,
            // while it is fresh. After this the app never needs the token
            // again for the rest of the session.
            $this->cacheEmployeePhoto($data['employee'], $data['token']);

            $this->dispatch('save-login-email', email: $this->email);
            $intended = session()->pull('url.intended', route('dashboard'));
            return redirect()->to($intended);
        }

        // Handle specific error cases with user-friendly messages
        if (isset($response['error'])) {
            switch ($response['error']) {
                case 'connection_error':
                    $this->errorMessage = 'Unable to connect to the authentication server. Please check your internet connection and try again.';
                    break;
                case 'invalid_credentials':
                    $this->errorMessage = 'The provided credentials do not match our records. Please check your email and password.';
                    break;
                case 'server_error':
                    $this->errorMessage = 'The authentication server is currently unavailable. Please try again later.';
                    break;
                case 'rate_limited':
                    $this->errorMessage = $response['message'] ?? 'Too many login attempts. Please wait a moment and try again.';
                    break;
                case 'client_error':
                case 'api_error':
                case 'unexpected_error':
                default:
                    $this->errorMessage = 'An error occurred during authentication. Please try again.';
                    break;
            }
        } else {
            // Fallback error message
            $this->errorMessage = 'Authentication failed. Please verify your credentials and try again.';
        }
    }

    /**
     * Fetch the employee's profile photo while the just-issued API token is
     * still valid and cache it locally, storing the public URL in the session
     * as 'user_photo'. The photo is the only thing the token is used for, so
     * doing it here means the app never re-authenticates mid-session just to
     * show an avatar. Failures are non-fatal — the navbar falls back to a
     * default avatar.
     */
    protected function cacheEmployeePhoto(array $employee, ?string $token): void
    {
        $photoUrl = $employee['photoUrl'] ?? null;

        // Nothing to fetch, or it is already a full external URL.
        if (!$photoUrl || filter_var($photoUrl, FILTER_VALIDATE_URL)) {
            if ($photoUrl) {
                session(['user_photo' => $photoUrl]);
            }
            return;
        }

        $imagePath = 'photos/' . basename($photoUrl);

        // Reuse an already-cached copy from a previous login.
        if (Storage::disk('public')->exists($imagePath)) {
            session(['user_photo' => asset('storage/' . $imagePath)]);
            return;
        }

        try {
            $response = Http::withToken($token)
                ->timeout(10)
                ->get(config('services.api.base_url') . 'employee/image/' . urlencode($photoUrl));

            if ($response->successful() && strlen($response->body()) > 100) {
                Storage::disk('public')->put($imagePath, $response->body());
                session(['user_photo' => asset('storage/' . $imagePath)]);
            }
        } catch (\Exception $e) {
            \Log::warning('Could not cache employee photo at login', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.auth.login-page');
    }
}
