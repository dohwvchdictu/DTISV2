<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiController extends Controller
{
    public function fetchOfficeData()
    {
        // API URL
        $url = 'http://192.168.100.162:8081/public/get-offices';

        try {
            // Send GET request
            $response = Http::get($url);

            // Check if the request was successful
            if ($response->successful()) {
                // Get the response data
                $data = $response->json();

                // Return the data or pass it to a view
                return response()->json([
                    'success' => true,
                    'data' => $data,
                ]);
            }

            // Handle failure response
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch data.',
                'status_code' => $response->status(),
            ], $response->status());
        } catch (\Exception $e) {
            // Handle exceptions
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage(),
            ], 500);
        }
    }
}
