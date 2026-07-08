<?php

namespace App\Services;

use GuzzleHttp\Client;
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
}
