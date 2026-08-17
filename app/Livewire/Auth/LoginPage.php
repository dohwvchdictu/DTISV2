<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Services\ApiService;
use App\Support\EmployeePhoto;
use Illuminate\Support\Facades\RateLimiter;
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

        // Throttle before the request leaves this server. Every user's login
        // is proxied from one IP, so without this one person's repeated bad
        // password trips the API's per-IP limit and locks out everybody.
        if ($this->isThrottled()) {
            return;
        }

        $credentials = [
            'email' => $this->email,
            'password' => $this->password,
        ];

        $response = $apiService->login($credentials);

        // Check if the response is valid
        if (!$response || !is_array($response)) {
            RateLimiter::hit($this->throttleKey(), config('session.login_decay_seconds', 60));
            $this->errorMessage = 'An unexpected error occurred. Please try again.';
            return;
        }

        // Handle successful authentication
        if (isset($response['success']) && $response['success'] === true) {
            $data = $response['data'];

            RateLimiter::clear($this->throttleKey());

            // Issue a fresh session id now that this session is becoming
            // privileged, so a session id fixed before login cannot be reused
            // afterwards. Existing session data (including url.intended) is
            // carried over to the new id.
            session()->regenerate();

            session([
                'jwt_token' => $data['token'],
                'user' => $data['employee'],
                'auth_email' => $this->email,
                'token_created_at' => time(),
                'revalidated_at' => time(),
            ]);

            // If this employee's photo was cached by an earlier login it can
            // be resolved right away, at no cost.
            if ($cached = EmployeePhoto::cachedUrl($data['employee'])) {
                session(['user_photo' => $cached]);
            }

            // Otherwise fetch it after the response has been sent. The token
            // is only good for five minutes and is used for nothing else, so
            // it has to happen now — but it must not sit between the user and
            // their dashboard. The navbar reads the cached file from disk, so
            // no session write is needed here (and none would survive).
            $this->cacheEmployeePhotoAfterResponse($data['employee'], $data['token']);

            $this->dispatch('save-login-email', email: $this->email);
            $intended = session()->pull('url.intended', route('dashboard'));
            return redirect()->to($intended);
        }

        // Count rejected credentials, but not the API being unreachable —
        // nobody should be locked out of retrying because of an outage.
        if (!in_array($response['error'] ?? null, ['connection_error', 'server_error', 'rate_limited'], true)) {
            RateLimiter::hit($this->throttleKey(), config('session.login_decay_seconds', 60));
        }

        // Handle specific error cases with user-friendly messages
        if (isset($response['error'])) {
            // When the API returned its own message (e.g. "Your account is
            // locked"), show that verbatim; otherwise fall back to friendly,
            // mapped text for transport-level failures that have no useful body.
            if (!empty($response['api_message'])) {
                $this->errorMessage = $response['api_message'];
            } else {
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
                    default:
                        $this->errorMessage = 'An error occurred during authentication. Please try again.';
                        break;
                }
            }
        } else {
            // Fallback error message
            $this->errorMessage = 'Authentication failed. Please verify your credentials and try again.';
        }
    }

    /**
     * Rate-limiter key for this attempt. Scoped to the email *and* the client
     * IP so that one workstation hammering an account cannot lock that account
     * out from everywhere else, and one person cannot exhaust the budget for
     * the whole office.
     */
    protected function throttleKey(): string
    {
        return 'login|' . sha1(mb_strtolower(trim((string) $this->email))) . '|' . request()->ip();
    }

    /**
     * True when this email/IP pair has failed too often recently. Sets the
     * message and leaves the caller to return.
     */
    protected function isThrottled(): bool
    {
        $maxAttempts = (int) config('session.login_max_attempts', 5);

        if ($maxAttempts <= 0 || !RateLimiter::tooManyAttempts($this->throttleKey(), $maxAttempts)) {
            return false;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        \Log::warning('Login throttled locally', [
            'email' => $this->email,
            'ip' => request()->ip(),
            'available_in' => $seconds,
        ]);

        $this->errorMessage = 'Too many failed sign-in attempts. Please wait '
            . ($seconds > 60 ? ceil($seconds / 60) . ' minute(s)' : $seconds . ' second(s)')
            . ' and try again.';

        return true;
    }

    /**
     * Cache the profile photo once the response has already been sent, so a
     * slow or unresponsive image endpoint costs the user nothing at sign-in.
     *
     * Runs during the framework's terminate phase, which is *after* the
     * session has been written — so this writes to disk only. The navbar
     * resolves the file from disk on the next request.
     */
    protected function cacheEmployeePhotoAfterResponse(array $employee, ?string $token): void
    {
        app()->terminating(function () use ($employee, $token) {
            EmployeePhoto::cache($employee, $token);
        });
    }

    public function render()
    {
        return view('livewire.auth.login-page');
    }
}
