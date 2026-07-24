<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ApiService
{
    protected $client;
    protected $refreshThreshold;

    public function __construct()
    {
        $this->refreshThreshold = config('services.api.refresh_threshold', 240);

        $this->client = new Client([
            'base_uri' => config('services.api.base_url'),
            'timeout'  => 10.0,
        ]);
    }

    /**
     * Authenticate against the external API.
     *
     * The API is a separate local service that is intermittently slow or
     * unavailable on a cold first hit — that is why a login would fail once
     * and then succeed on an unchanged second attempt. We therefore retry
     * transient failures (connection drops, timeouts, 5xx and other
     * unexpected responses) a couple of times before giving up. A genuine 401
     * is a real credential rejection, so it returns immediately without
     * retrying (retrying it is pointless and could trip API rate limiting).
     *
     * Every failed attempt is logged so the underlying cause is visible
     * instead of being swallowed behind a generic message.
     *
     * @param  array  $credentials
     * @param  int    $maxAttempts  Total tries, including the first.
     * @return array
     */
    public function login($credentials, int $maxAttempts = 2)
    {
        $lastResult = [
            'success' => false,
            'error' => 'unexpected_error',
            'message' => 'An unexpected error occurred during authentication.',
        ];

        for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
            try {
                // http_errors => false so 4xx/5xx come back as normal
                // responses and are classified explicitly below.
                $response = $this->client->post('auth/login', [
                    'json' => $credentials,
                    'http_errors' => false,
                ]);

                $statusCode = $response->getStatusCode();
                $body = json_decode($response->getBody()->getContents(), true);

                // Success.
                if ($statusCode === 200 && isset($body['token'])) {
                    return [
                        'success' => true,
                        'data' => $body,
                    ];
                }

                // Real credential rejection — do not retry.
                if ($statusCode === 401) {
                    return [
                        'success' => false,
                        'error' => 'invalid_credentials',
                        'message' => 'Invalid credentials provided.',
                    ];
                }

                // Anything else is treated as transient and retried.
                $lastResult = [
                    'success' => false,
                    'error' => $statusCode >= 500 ? 'server_error' : 'api_error',
                    'message' => $statusCode >= 500
                        ? 'Authentication server is currently unavailable.'
                        : 'An unexpected error occurred during login.',
                ];

                \Log::warning('Login attempt failed', [
                    'attempt' => $attempt,
                    'status' => $statusCode,
                    'has_token' => isset($body['token']),
                ]);
            } catch (\GuzzleHttp\Exception\ConnectException $e) {
                // Connection refused / DNS / timeout (cURL maps timeouts here).
                $lastResult = [
                    'success' => false,
                    'error' => 'connection_error',
                    'message' => 'Unable to connect to the authentication server.',
                ];

                \Log::warning('Login connection error', [
                    'attempt' => $attempt,
                    'message' => $e->getMessage(),
                ]);
            } catch (\Exception $e) {
                $lastResult = [
                    'success' => false,
                    'error' => 'unexpected_error',
                    'message' => 'An unexpected error occurred during authentication.',
                ];

                \Log::warning('Login unexpected error', [
                    'attempt' => $attempt,
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                ]);
            }

            // Brief backoff before retrying, but not after the final attempt.
            if ($attempt < $maxAttempts) {
                usleep(250000); // 250ms
            }
        }

        return $lastResult;
    }

    /**
     * Make sure the session JWT is younger than the refresh threshold,
     * silently re-authenticating with the stored credentials when it isn't.
     * Call this before any request that sends the Bearer token.
     */
    public function ensureTokenIsFresh(): bool
    {
        $token = session('jwt_token');
        $tokenAge = time() - session('token_created_at', 0);

        if ($token && $tokenAge < $this->refreshThreshold) {
            return true;
        }

        return $this->refreshWithStoredCredentials();
    }

    /**
     * The API has no working token-refresh endpoint, so a "refresh" is a
     * re-login with the credentials captured at sign-in.
     */
    protected function refreshWithStoredCredentials(): bool
    {
        $credentials = session('login_credentials');

        if (!$credentials || empty($credentials['email'])) {
            return false;
        }

        $response = $this->login($credentials);

        if (isset($response['success']) && $response['success'] === true) {
            session([
                'jwt_token' => $response['data']['token'],
                'token_created_at' => time(),
            ]);
            return true;
        }

        \Log::warning('Token refresh via re-login failed', [
            'error' => $response['error'] ?? 'unknown',
        ]);

        return false;
    }

    /**
     * Office/employee directory data barely changes but was previously
     * fetched fresh from the external API on every module load. Cached so
     * only the first request per window pays the network round-trip; a
     * failed lookup is not cached, so the next request retries automatically.
     */
    /** Directory data changes rarely, so cache for hours to avoid frequent cold fetches. */
    protected const DIRECTORY_CACHE_MINUTES = 360;

    public function getEmployeesData(): ?array
    {
        return Cache::remember('api.employees', now()->addMinutes(self::DIRECTORY_CACHE_MINUTES), function () {
            $response = Http::timeout(10)->get(config('services.api.base_url') . 'public/get-employees');
            return $response->ok() ? $response->json() : null;
        });
    }

    public function getOfficesData(): ?array
    {
        return Cache::remember('api.offices', now()->addMinutes(self::DIRECTORY_CACHE_MINUTES), function () {
            $response = Http::timeout(10)->get(config('services.api.base_url') . 'public/get-offices');
            return $response->ok() ? $response->json() : null;
        });
    }

    /**
     * Offices the API flags as active (status !== false), sorted by name —
     * the list dropdowns and report office listings should present. Lookups
     * that resolve an office id on historical documents must keep using the
     * full officeList so deactivated offices still resolve to a name.
     * Offices without a status field (older API) are treated as active.
     */
    public function getActiveOffices(?array $officesData = null): array
    {
        $officesData ??= $this->getOfficesData();

        return collect($officesData['officeList'] ?? [])
            ->filter(fn ($office) => $office['status'] ?? true)
            ->sortBy('officeName')
            ->values()
            ->all();
    }
}
