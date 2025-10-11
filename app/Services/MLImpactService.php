<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MLImpactService
{
    private string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = env('ML_IMPACT_API_URL', 'http://127.0.0.1:8085/predict_impact');
    }

    public function predictImpact(float $quantity, int $recyclabilityScore, string $category): ?array
    {
        try {
            $response = Http::timeout(30)
                ->retry(3, 100)
                ->post($this->apiUrl, [
                    'quantity' => $quantity,
                    'recyclability_score' => $recyclabilityScore,
                    'category' => $category,
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('ML API request failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('ML API connection failed', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
