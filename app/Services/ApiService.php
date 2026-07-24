<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ApiService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('services.api.base_url'),
            'timeout'  => 10.0,
        ]);
    }

    /** Once the API rate-limits our (shared) source IP, stop sending
     *  login/refresh calls for this long so the lockout window can clear.
     *  A Retry-After header from the API overrides it. */
    protected const LOGIN_COOLDOWN_SECONDS = 30;

    /** Cache flag backing the login cooldown above. */
    protected const LOGIN_COOLDOWN_KEY = 'api.login_cooldown';

    /**
     * Authenticate against the external API.
     *
     * The API is a separate local service that is intermittently slow or
     * unavailable on a cold first hit, so genuinely transient failures
     * (connection drops, timeouts, 5xx) are retried a couple of times before
     * giving up. Definitive answers are NOT retried: a 401 is a real
     * credential rejection, and a 429 means we have already hit the API's
     * login rate limit — retrying either is pointless, and retrying the 429
     * only extends the lockout. Other 4xx responses won't change on retry.
     *
     * Because every user's login is proxied through this server, all of them
     * share one source IP against the API's (per-IP) login rate limit. So a
     * single 429 starts a short app-wide cooldown, during which we stop
     * sending login/refresh calls entirely and let the window clear instead
     * of hammering it.
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
        // We very recently hit the API's login rate limit; failing fast here
        // is what lets the window clear instead of piling on more requests.
        if (Cache::has(self::LOGIN_COOLDOWN_KEY)) {
            return [
                'success' => false,
                'error' => 'rate_limited',
                'message' => 'Too many login attempts. Please wait a moment and try again.',
            ];
        }

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

                // 200 but no token: a response-shape mismatch, not transient.
                // Retrying won't conjure a token, so return immediately.
                if ($statusCode === 200) {
                    \Log::warning('Login returned 200 without a token', [
                        'attempt' => $attempt,
                    ]);

                    return [
                        'success' => false,
                        'error' => 'api_error',
                        'message' => 'An unexpected error occurred during login.',
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

                // Rate limited — never retry (that only extends the lockout).
                // Start a short cooldown so the rest of the app stops calling
                // the endpoint too until the window clears.
                if ($statusCode === 429) {
                    $retryAfter = (int) $response->getHeaderLine('Retry-After');
                    $cooldown = $retryAfter > 0 ? $retryAfter : self::LOGIN_COOLDOWN_SECONDS;
                    Cache::put(self::LOGIN_COOLDOWN_KEY, true, now()->addSeconds($cooldown));

                    \Log::warning('Login rate limited (429)', [
                        'attempt' => $attempt,
                        'retry_after' => $retryAfter ?: null,
                        'cooldown_seconds' => $cooldown,
                    ]);

                    return [
                        'success' => false,
                        'error' => 'rate_limited',
                        'message' => 'Too many login attempts. Please wait a moment and try again.',
                    ];
                }

                // Other client errors (4xx) won't succeed on retry either.
                if ($statusCode >= 400 && $statusCode < 500) {
                    \Log::warning('Login attempt failed', [
                        'attempt' => $attempt,
                        'status' => $statusCode,
                    ]);

                    return [
                        'success' => false,
                        'error' => 'api_error',
                        'message' => 'An unexpected error occurred during login.',
                    ];
                }

                // 5xx (or any other unexpected status) is treated as transient
                // and retried.
                $lastResult = [
                    'success' => false,
                    'error' => 'server_error',
                    'message' => 'Authentication server is currently unavailable.',
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
