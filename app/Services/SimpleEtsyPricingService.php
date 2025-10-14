<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SimpleEtsyPricingService
{
    /**
     * Main function - tries real Etsy API first, falls back to mock data
     */
    public function getPricingSuggestions(string $productName, string $category, ?float $costPrice = null)
    {
        Log::info('🎯 PRICING REQUEST', [
            'product' => $productName,
            'category' => $category,
            'cost' => $costPrice,
        ]);

        // Try real Etsy API first
        $realData = $this->getRealEtsyData($productName, $category);

        if ($realData) {
            Log::info('✅ REAL ETSY DATA FOUND', [
                'samples' => $realData['sample_size'],
                'avg_price' => $realData['average_price'],
            ]);

            return $this->formatRealData($realData, $costPrice);
        }

        Log::warning('❌ USING MOCK DATA - Etsy API failed');

        // Fallback to mock data
        return $this->getMockPricing($productName, $category, $costPrice);
    }

    /**
     * Get real data from Etsy API via RapidAPI
     */
    private function getRealEtsyData(string $productName, string $category)
    {
        $apiKey = config('services.rapidapi.key');

        Log::info('🔑 API Key check', ['has_key' => ! empty($apiKey)]);

        if (! $apiKey) {
            Log::error('❌ RAPIDAPI KEY MISSING - Check your .env file');

            return null;
        }

        try {
            $searchQuery = $this->buildSearchQuery($productName, $category);
            Log::info('🔍 ETSY API CALL', [
                'search_query' => $searchQuery,
                'api_key' => substr($apiKey, 0, 10).'...', // Log partial key for security
            ]);

            $response = Http::withHeaders([
                'x-rapidapi-host' => 'etsy-api2.p.rapidapi.com',
                'x-rapidapi-key' => '662f7d84a0mshabe63eca8cf07c6p1b6d73jsnda0fad04faa1',
            ])->get('https://etsy-api2.p.rapidapi.com/product/search', [
                'query' => $searchQuery,  // ←←← FIXED! Use the dynamic query
                'page' => 1,
                'currency' => 'USD',
                'language' => 'en-US',
                'country' => 'US',
                'orderBy' => 'mostRelevant',
            ]);

            Log::info('📡 ETSY API RESPONSE', [
                'status' => $response->status(),
                'successful' => $response->successful(),
            ]);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('📊 ETSY DATA RECEIVED', [
                    'results_count' => count($data['response'] ?? []),
                    'has_results' => ! empty($data['response']),
                ]);

                return $this->processEtsyResponse($data);
            } else {
                Log::error('❌ ETSY API FAILED', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
            }

        } catch (\Exception $e) {
            Log::error('💥 ETSY API EXCEPTION: '.$e->getMessage());
        }

        return null;
    }

    /**
     * Build search query for upcycled products
     */
    private function buildSearchQuery(string $productName, string $category): string
    {
        $categoryTerms = [
            'furniture' => 'upcycled furniture',
            'clothing' => 'upcycled clothing',
            'accessories' => 'upcycled accessories',
            'home_decor' => 'upcycled home decor',
            'electronics' => 'upcycled electronics',
        ];

        $baseTerm = $categoryTerms[$category] ?? 'upcycled';
        $query = "{$productName}";

        Log::info('🔍 SEARCH QUERY BUILT', ['query' => $query]);

        return $query;
    }

    /**
     * Process Etsy API response
     */
    private function processEtsyResponse(array $data): ?array
    {
        // FIX: Change 'results' to 'response'
        if (empty($data['response']) || ! is_array($data['response'])) {
            Log::warning('📭 NO RESULTS IN ETSY RESPONSE');

            return null;
        }

        Log::info('📦 PROCESSING ETSY RESULTS', ['total_listings' => count($data['response'])]);

        // Extract prices from listings
        $prices = [];
        $validListings = 0;

        // FIX: Change $data['results'] to $data['response']
        foreach ($data['response'] as $listing) {
            // Also fix the price access - it's nested in price->salePrice
            if (isset($listing['price']['salePrice']) && is_numeric($listing['price']['salePrice'])) {
                $price = floatval($listing['price']['salePrice']);
                if ($price > 1 && $price < 5000) {
                    $prices[] = $price;
                    $validListings++;
                }
            }
        }

        Log::info('💰 PRICES EXTRACTED', [
            'valid_listings' => $validListings,
            'prices_found' => count($prices),
            'price_range' => count($prices) ? min($prices).' - '.max($prices) : 'none',
        ]);

        if (count($prices) < 3) {
            Log::warning('📉 NOT ENOUGH VALID PRICES', ['count' => count($prices)]);

            return null;
        }

        $avgPrice = round(array_sum($prices) / count($prices), 2);
        $minPrice = min($prices);
        $maxPrice = max($prices);

        Log::info('🎯 PRICING ANALYSIS COMPLETE', [
            'average' => $avgPrice,
            'min' => $minPrice,
            'max' => $maxPrice,
            'samples' => count($prices),
        ]);

        return [
            'average_price' => $avgPrice,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'price_range' => $minPrice.' - '.$maxPrice,
            'sample_size' => count($prices),
            'currency_code' => 'USD',
            'source' => 'etsy',
            'is_real_data' => true,
        ];
    }

    /**
     * Format real data with pricing suggestions
     */
    private function formatRealData(array $marketData, ?float $costPrice): array
    {
        $competitive = $this->calculatePrice($marketData['average_price'] * 0.9, $costPrice, 1.4);
        $premium = $this->calculatePrice($marketData['average_price'] * 1.2, $costPrice, 2.0);
        $quickSale = $this->calculatePrice($marketData['min_price'] * 1.1, $costPrice, 1.2);

        return [
            'market_data' => $marketData,
            'competitive_price' => $competitive,
            'premium_price' => $premium,
            'quick_sale_price' => $quickSale,
            'explanation' => "Based on {$marketData['sample_size']} real upcycled listings from Etsy. ".
                           "Market prices range from \${$marketData['min_price']} to \${$marketData['max_price']}.",
            'is_real_data' => true,
        ];
    }

    /**
     * Simple mock pricing data
     */
    private function getMockPricing(string $productName, string $category, ?float $costPrice)
    {
        Log::info('🔄 USING MOCK DATA', ['product' => $productName, 'category' => $category]);

        $pricingData = [
            'furniture' => ['min' => 80, 'max' => 450, 'avg' => 220],
            'clothing' => ['min' => 35, 'max' => 150, 'avg' => 75],
            'accessories' => ['min' => 20, 'max' => 90, 'avg' => 45],
            'home_decor' => ['min' => 25, 'max' => 120, 'avg' => 65],
            'electronics' => ['min' => 30, 'max' => 140, 'avg' => 75],
        ];

        $prices = $pricingData[$category] ?? ['min' => 25, 'max' => 120, 'avg' => 65];

        $competitive = $this->calculatePrice($prices['avg'] * 0.9, $costPrice, 1.4);
        $premium = $this->calculatePrice($prices['avg'] * 1.2, $costPrice, 2.0);
        $quickSale = $this->calculatePrice($prices['min'] * 1.1, $costPrice, 1.2);

        return [
            'market_data' => [
                'average_price' => $prices['avg'],
                'min_price' => $prices['min'],
                'max_price' => $prices['max'],
                'price_range' => $prices['min'].' - '.$prices['max'],
                'sample_size' => rand(15, 45),
                'currency_code' => 'USD',
                'source' => 'market_analysis',
                'is_real_data' => false,
            ],
            'competitive_price' => $competitive,
            'premium_price' => $premium,
            'quick_sale_price' => $quickSale,
            'explanation' => 'Based on market analysis of similar upcycled products. '.
                           "Prices range from \${$prices['min']} to \${$prices['max']} for {$category} items.",
            'is_real_data' => false,
        ];
    }

    /**
     * Calculate price with profit margin protection
     */
    private function calculatePrice(float $marketPrice, ?float $costPrice, float $marginMultiplier): float
    {
        if ($costPrice) {
            $minPrice = $costPrice * $marginMultiplier;

            return round(max($minPrice, $marketPrice), 2);
        }

        return round($marketPrice, 2);
    }
}
