<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Services\ApiService;
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

    public function mount(ApiService $apiService)
    {
        $token = session('jwt_token');
        $user  = session('user');

        if ($token && $user && isset($user['office']['id']) && $apiService->ensureTokenIsFresh()) {
            return redirect()->route('dashboard');
        }
    }

    public function authenticate(ApiService $apiService)
    {
        $this->validate();

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
                // Kept for silent re-login when the 5-minute token ages out
                // (the API has no working refresh endpoint). Requires
                // SESSION_ENCRYPT=true so the password never sits on disk
                // in plain text.
                'login_credentials' => $credentials,
            ]);
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

    public function render()
    {
        return view('livewire.auth.login-page');
    }
}
