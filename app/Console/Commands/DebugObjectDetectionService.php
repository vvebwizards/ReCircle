<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DebugObjectDetectionService extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:object-detection';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing ObjectDetectionService...');

        $objectDetectionService = app(\App\Services\ObjectDetectionService::class);
        $this->info('Service instantiated successfully');

        // Create a test file to simulate an upload
        $testFilePath = storage_path('app/test-image.jpg');
        if (! file_exists($testFilePath)) {
            // Copy a test image if it doesn't exist
            $sourceImage = public_path('images/default-material.png');
            if (file_exists($sourceImage)) {
                copy($sourceImage, $testFilePath);
                $this->info("Test file created at: $testFilePath");
            } else {
                $this->error("Source image not found at: $sourceImage");

                return 1;
            }
        }

        // Create an uploaded file instance
        $uploadedFile = new \Illuminate\Http\UploadedFile(
            $testFilePath,
            'test-wood-furniture.jpg',
            'image/jpeg',
            null,
            true
        );

        $this->info('Testing material detection...');
        $detectedMaterials = $objectDetectionService->detectMaterials($uploadedFile);

        if ($detectedMaterials) {
            $this->info('Materials detected: '.count($detectedMaterials));
            foreach ($detectedMaterials as $material) {
                $this->line("- {$material['name']} ({$material['confidence']}%)");
            }

            $this->info('Converting to tags...');
            $tags = $objectDetectionService->materialsToTags($detectedMaterials);

            foreach ($tags as $tag) {
                $this->line("- Tag: {$tag['name']}, Confidence: {$tag['confidence']}");
            }

            // Try attaching these tags to a waste item
            $wasteItem = \App\Models\WasteItem::first();

            if ($wasteItem) {
                $this->info("Testing tag attachment to waste item ID: {$wasteItem->id}");

                // First clear existing tags
                \DB::table('taggables')
                    ->where('taggable_id', $wasteItem->id)
                    ->where('taggable_type', 'App\\Models\\WasteItem')
                    ->delete();

                $this->info('Cleared existing tags');

                // Try attaching each tag
                foreach ($tags as $tag) {
                    $this->line("Attaching tag: {$tag['name']} with confidence {$tag['confidence']}");
                    $wasteItem->attachTags([$tag['name']], true, $tag['confidence']);
                }

                // Verify tags were attached
                $wasteItem = $wasteItem->fresh();
                $attachedTags = $wasteItem->tags;

                $this->info("Number of attached tags: {$attachedTags->count()}");
                foreach ($attachedTags as $tag) {
                    $this->line("- Tag: {$tag->name} (ID: {$tag->id}), Auto: ".($tag->pivot->is_auto_generated ? 'Yes' : 'No').
                        ', Confidence: '.($tag->pivot->confidence ?? 'null'));
                }

                // Check raw database entries
                $this->info('Raw database entries:');
                $entries = \DB::table('taggables')
                    ->where('taggable_id', $wasteItem->id)
                    ->where('taggable_type', 'App\\Models\\WasteItem')
                    ->get();

                if ($entries->count() > 0) {
                    foreach ($entries as $entry) {
                        $this->line("- Tag ID: {$entry->tag_id}, Auto: ".($entry->is_auto_generated ? 'Yes' : 'No').
                            ', Confidence: '.($entry->confidence ?? 'null'));
                    }
                } else {
                    $this->error('No taggable entries found in database!');
                }
            } else {
                $this->error('No waste items found in the database');
            }
        } else {
            $this->error('No materials detected or an error occurred');

            return 1;
        }

        return 0;
    }
}
