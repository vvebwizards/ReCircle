<?php

namespace App\Services;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class ObjectDetectionService
{
    protected Client $client;

    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.ml_service.url', 'http://localhost:8000');
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
        ]);
    }

    /**
     * Detect materials in an uploaded image
     *
     * @return array|null Array of detected materials or null if detection failed
     */
    public function detectMaterials(UploadedFile $image): ?array
    {
        try {
            $response = $this->client->post('/detect_materials', [
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => fopen($image->getPathname(), 'r'),
                        'filename' => $image->getClientOriginalName(),
                    ],
                ],
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            return $result['materials'] ?? [];
        } catch (Exception $e) {
            Log::error('Material detection failed: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Convert detected materials to tag names
     *
     * @param  array  $materials  Detected materials from the ML service
     * @return array Tag data with name and confidence
     */
    public function materialsToTags(array $materials): array
    {
        Log::info('Converting materials to tags', ['materials_count' => count($materials)]);

        $tags = [];

        foreach ($materials as $material) {
            $tags[] = [
                'name' => $material['name'],
                'auto_generated' => true,
                'confidence' => $material['confidence'] / 100, // Convert percentage to decimal
            ];
        }

        Log::info('Generated tags', ['tags_count' => count($tags), 'tags' => $tags]);

        return $tags;
    }
}
