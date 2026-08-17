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
                // The API signals business errors (bad password, locked/disabled
                // account, etc.) via a `message` in the body — capture it so it
                // can be logged and shown to the user instead of a generic line.
                // Always normalised to a string: validation errors come back as
                // an array of messages, which would otherwise blow up when the
                // view echoes it.
                $apiMessage = self::normalizeApiMessage(is_array($body) ? ($body['message'] ?? null) : null);
                $bodyStatus = is_array($body) ? ($body['statusCode'] ?? null) : null;

                // Success. `employee` is required as well as `token`: without
                // it the caller has no identity to put in the session and would
                // fatal on the missing key.
                if ($statusCode === 200 && isset($body['token'], $body['employee'])) {
                    return [
                        'success' => true,
                        'data' => $body,
                    ];
                }

                // Token but no employee profile — a response-shape problem on
                // the API side, not something a retry or the user can fix.
                if ($statusCode === 200 && isset($body['token'])) {
                    \Log::error('Login succeeded but returned no employee profile', [
                        'attempt' => $attempt,
                        'body_keys' => is_array($body) ? array_keys($body) : null,
                    ]);

                    return [
                        'success' => false,
                        'error' => 'api_error',
                        'message' => 'Your account signed in but no employee profile was returned. Please contact the DTIS administrator.',
                    ];
                }

                // 200 but no token: a response-shape mismatch, not transient.
                // Retrying won't conjure a token, so return immediately.
                if ($statusCode === 200) {
                    \Log::warning('Login returned 200 without a token', [
                        'attempt' => $attempt,
                        'body_status' => $bodyStatus,
                        'api_message' => $apiMessage,
                    ]);

                    return [
                        'success' => false,
                        'error' => 'api_error',
                        'message' => $apiMessage ?? 'An unexpected error occurred during login.',
                        'api_message' => $apiMessage,
                    ];
                }

                // Real credential rejection — do not retry.
                if ($statusCode === 401) {
                    \Log::warning('Login rejected (401)', [
                        'attempt' => $attempt,
                        'api_message' => $apiMessage,
                    ]);

                    return [
                        'success' => false,
                        'error' => 'invalid_credentials',
                        'message' => $apiMessage ?? 'Invalid credentials provided.',
                        'api_message' => $apiMessage,
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
                        'api_message' => $apiMessage,
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
                        'body_status' => $bodyStatus,
                        'api_message' => $apiMessage,
                    ]);

                    return [
                        'success' => false,
                        'error' => 'api_error',
                        'message' => $apiMessage ?? 'An unexpected error occurred during login.',
                        'api_message' => $apiMessage,
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
                    'body_status' => $bodyStatus,
                    'api_message' => $apiMessage,
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
     * Reduce whatever the API put in `message` to a single display string.
     *
     * Most errors send a plain string ("Your account has been temporarily
     * locked", "User not found"). Validation failures send an array of
     * messages instead, and passing that straight to a Blade echo is a fatal
     * TypeError — so flatten it here, once, rather than guarding at every
     * call site.
     */
    protected static function normalizeApiMessage($message): ?string
    {
        if (is_string($message)) {
            return trim($message) !== '' ? trim($message) : null;
        }

        if (is_array($message)) {
            $parts = array_filter(array_map(
                fn ($part) => is_scalar($part) ? trim((string) $part) : null,
                $message
            ));

            return $parts ? implode('. ', $parts) : null;
        }

        return is_scalar($message) ? (string) $message : null;
    }

    /**
     * Look a signed-in user up in the employee directory so their access can
     * be re-checked mid-session.
     *
     * The distinction between "the directory says this person is gone" and
     * "we could not reach the directory" matters — the first should end the
     * session, the second must never do so. Hence the explicit `available`
     * flag rather than a bare null.
     *
     * @return array{available: bool, employee: array|null}
     */
    public function lookupEmployee($employeeId): array
    {
        if ($employeeId === null || $employeeId === '') {
            return ['available' => false, 'employee' => null];
        }

        $data = $this->getEmployeesData();

        // API unreachable or the cache could not be warmed — fail open.
        if (!is_array($data) || !isset($data['employeesList'])) {
            return ['available' => false, 'employee' => null];
        }

        foreach ($data['employeesList'] as $employee) {
            if (isset($employee['id']) && (string) $employee['id'] === (string) $employeeId) {
                return ['available' => true, 'employee' => $employee];
            }
        }

        return ['available' => true, 'employee' => null];
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
            return $this->fetchDirectory('public/get-employees');
        });
    }

    public function getOfficesData(): ?array
    {
        return Cache::remember('api.offices', now()->addMinutes(self::DIRECTORY_CACHE_MINUTES), function () {
            return $this->fetchDirectory('public/get-offices');
        });
    }

    /**
     * Fetch a directory endpoint, returning null on any failure.
     *
     * A connection error throws out of the HTTP client, so without this the
     * "return null when the lookup fails" contract every caller relies on was
     * only honoured for HTTP error responses — an API that was down produced
     * an uncaught ConnectionException and a 500 instead. Null is not
     * effectively cached (Cache::remember re-runs the callback for a null
     * value), so recovery is automatic once the API answers again.
     */
    protected function fetchDirectory(string $path): ?array
    {
        try {
            $response = Http::timeout(10)->get(config('services.api.base_url') . $path);

            if (!$response->ok()) {
                \Log::warning('Directory lookup failed', [
                    'path' => $path,
                    'status' => $response->status(),
                ]);

                return null;
            }

            return $response->json();
        } catch (\Throwable $e) {
            \Log::warning('Directory lookup unreachable', [
                'path' => $path,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
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
