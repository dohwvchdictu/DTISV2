<?php

namespace App\Services;

use GuzzleHttp\Client;
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

    public function refreshToken(array $credentials)
    {
        try {
            $response = $this->client->post('auth/refresh-token', [
                'json' => $credentials,
            ]);

            $body = json_decode($response->getBody()->getContents(), true);

            if ($response->getStatusCode() === 200 && isset($body['token'])) {
                return [
                    'success' => true,
                    'data' => $body,
                ];
            }

            return [
                'success' => false,
                'error' => 'token_refresh_failed',
                'message' => 'Unable to refresh authentication token.',
            ];
        } catch (\Exception $e) {
            \Log::warning('Token refresh failed', ['message' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => 'token_refresh_error',
                'message' => 'An error occurred while refreshing the token.',
            ];
        }
    }
}
