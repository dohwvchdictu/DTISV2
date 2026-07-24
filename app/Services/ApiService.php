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

    public function login($credentials)
    {
        try {
            $response = $this->client->post('auth/login', [
                'json' => $credentials,
            ]);

            $statusCode = $response->getStatusCode();
            $body = json_decode($response->getBody()->getContents(), true);

            // Handle successful response
            if ($statusCode === 200 && isset($body['token'])) {
                return [
                    'success' => true,
                    'data' => $body,
                ];
            }

            // Handle authentication failure
            if ($statusCode === 401) {
                return [
                    'success' => false,
                    'error' => 'invalid_credentials',
                    'message' => 'Invalid credentials provided.',
                ];
            }

            // Handle other HTTP errors
            return [
                'success' => false,
                'error' => 'api_error',
                'message' => 'An unexpected error occurred during login.',
            ];

        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            return [
                'success' => false,
                'error' => 'connection_error',
                'message' => 'Unable to connect to the authentication server.',
            ];
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getResponse() && $e->getResponse()->getStatusCode() === 401) {
                return [
                    'success' => false,
                    'error' => 'invalid_credentials',
                    'message' => 'Invalid credentials provided.',
                ];
            }
            return [
                'success' => false,
                'error' => 'client_error',
                'message' => 'Authentication request failed.',
            ];
        } catch (\GuzzleHttp\Exception\ServerException $e) {
            return [
                'success' => false,
                'error' => 'server_error',
                'message' => 'Authentication server is currently unavailable.',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'unexpected_error',
                'message' => 'An unexpected error occurred during authentication.',
            ];
        }
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
