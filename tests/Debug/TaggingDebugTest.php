<?php

namespace Tests\Debug;

use App\Models\Tag;
use App\Models\WasteItem;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TaggingDebugTest extends TestCase
{
    public function test_attach_tags()
    {
        // Create a waste item for testing
        $wasteItem = WasteItem::first();

        if (! $wasteItem) {
            $this->markTestSkipped('No waste items found in the database');
        }

        echo "Testing with waste item ID: {$wasteItem->id}\n";

        // Clear any existing tags for this waste item
        DB::table('taggables')
            ->where('taggable_id', $wasteItem->id)
            ->where('taggable_type', WasteItem::class)
            ->delete();

        echo "Cleared existing tags\n";

        // Test manual tag attachment
        $tagNames = ['test-tag', 'plastic', 'wood'];
        echo 'Attaching manual tags: '.implode(', ', $tagNames)."\n";

        $wasteItem->attachTags($tagNames);

        // Check if tags were attached
        $attachedTags = $wasteItem->tags()->get();
        echo "Attached tags count: {$attachedTags->count()}\n";

        foreach ($attachedTags as $tag) {
            echo "- Tag: {$tag->name} (ID: {$tag->id})\n";
        }

        // Test auto-generated tags with confidence
        $autoTagNames = ['metal'];
        echo "\nAttaching auto-generated tag with confidence score\n";

        $wasteItem->attachTags($autoTagNames, true, 0.95);

        // Check if auto-tag was attached
        $wasteItem->load('tags'); // Refresh relation
        $autoTag = $wasteItem->tags()
            ->where('name', 'metal')
            ->first();

        if ($autoTag) {
            echo "Auto tag attached: {$autoTag->name}\n";
            echo 'Is auto-generated: '.($autoTag->pivot->is_auto_generated ? 'Yes' : 'No')."\n";
            echo 'Confidence: '.($autoTag->pivot->confidence ?? 'null')."\n";
        } else {
            echo "Failed to attach auto tag\n";
        }

        // Check raw database entries
        echo "\nRaw database entries:\n";
        $entries = DB::table('taggables')
            ->where('taggable_id', $wasteItem->id)
            ->where('taggable_type', WasteItem::class)
            ->get();

        foreach ($entries as $entry) {
            echo "- Tag ID: {$entry->tag_id}, Auto: ".($entry->is_auto_generated ? 'Yes' : 'No').
                ', Confidence: '.($entry->confidence ?? 'null')."\n";
        }
    }
}
