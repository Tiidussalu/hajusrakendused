<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalApiController extends Controller
{
    /**
     * Proxy request to external sharks API
     */
    public function getSharks(Request $request)
    {
        $apiKey = $request->query('api_key') ?? $request->header('X-API-Key');

        if (!$apiKey) {
            return response()->json(['error' => 'API key is required'], 400);
        }

        try {
            Log::info('External API request', [
                'url' => 'https://hajusrakendused-main-pm8ido.laravel.cloud/api/sharks',
                'api_key_prefix' => substr($apiKey, 0, 5) . '...',
            ]);

            // Build query parameters
            $queryParams = [];
            if ($request->has('search')) {
                $queryParams['search'] = $request->query('search');
            }
            if ($request->has('danger_level')) {
                $queryParams['danger_level'] = $request->query('danger_level');
            }
            if ($request->has('limit')) {
                $queryParams['limit'] = $request->query('limit');
            }

            $response = Http::timeout(30)->withHeaders([
                'X-API-Key' => $apiKey,
                'Accept' => 'application/json',
                'User-Agent' => 'Laravel-App/1.0',
            ])->get('https://hajusrakendused-main-pm8ido.laravel.cloud/api/sharks', $queryParams);

            Log::info('External API response', [
                'status' => $response->status(),
                'body_length' => strlen($response->body()),
            ]);

            if ($response->failed()) {
                $errorMsg = $response->json('error') ?? $response->json('message') ?? "External API error: {$response->status()}";
                return response()->json(['error' => $errorMsg], $response->status());
            }

            return response()->json($response->json());
        } catch (\Exception $e) {
            Log::error('External API error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['error' => 'Network error: ' . $e->getMessage()], 500);
        }
    }
}
