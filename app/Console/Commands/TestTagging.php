<?php

namespace App\Console\Commands;

use App\Models\Tag;
use App\Models\WasteItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestTagging extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:tagging {waste_item_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the tagging functionality for waste items';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $wasteItemId = $this->argument('waste_item_id');

        if ($wasteItemId) {
            $wasteItem = WasteItem::find($wasteItemId);
        } else {
            $wasteItem = WasteItem::first();
        }

        if (! $wasteItem) {
            $this->error('No waste items found in the database');

            return 1;
        }

        $this->info("Testing with waste item ID: {$wasteItem->id}");

        // Check for existing tags
        $existingTags = $wasteItem->tags()->get();
        $this->info("Existing tags count: {$existingTags->count()}");

        foreach ($existingTags as $tag) {
            $this->line("- Tag: {$tag->name} (ID: {$tag->id})");
        }

        if ($this->confirm('Do you want to clear existing tags?', true)) {
            DB::table('taggables')
                ->where('taggable_id', $wasteItem->id)
                ->where('taggable_type', WasteItem::class)
                ->delete();

            $this->info('Cleared existing tags');
        }

        // Test manual tag attachment
        $tagNames = ['test-tag', 'plastic', 'wood'];
        $this->info('Attaching manual tags: '.implode(', ', $tagNames));

        $wasteItem->attachTags($tagNames);

        // Check if tags were attached
        $wasteItem = $wasteItem->fresh();
        $attachedTags = $wasteItem->tags()->get();
        $this->info("Attached tags count: {$attachedTags->count()}");

        foreach ($attachedTags as $tag) {
            $this->line("- Tag: {$tag->name} (ID: {$tag->id})");
        }

        // Test auto-generated tags with confidence
        $autoTagNames = ['metal'];
        $this->info('Attaching auto-generated tag with confidence score');

        $wasteItem->attachTags($autoTagNames, true, 0.95);

        // Check if auto-tag was attached
        $wasteItem = $wasteItem->fresh();
        $autoTag = $wasteItem->tags()
            ->where('name', 'metal')
            ->first();

        if ($autoTag) {
            $this->info("Auto tag attached: {$autoTag->name}");
            $this->line('Is auto-generated: '.($autoTag->pivot->is_auto_generated ? 'Yes' : 'No'));
            $this->line('Confidence: '.($autoTag->pivot->confidence ?? 'null'));
        } else {
            $this->error('Failed to attach auto tag');
        }

        // Check raw database entries
        $this->info('Raw database entries:');
        $entries = DB::table('taggables')
            ->where('taggable_id', $wasteItem->id)
            ->where('taggable_type', 'App\\Models\\WasteItem')
            ->get();

        foreach ($entries as $entry) {
            $this->line("- Tag ID: {$entry->tag_id}, Auto: ".($entry->is_auto_generated ? 'Yes' : 'No').
                ', Confidence: '.($entry->confidence ?? 'null'));
        }

        return 0;
    }
}
