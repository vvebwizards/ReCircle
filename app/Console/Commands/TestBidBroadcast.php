<?php

namespace App\Console\Commands;

use App\Events\BidSubmitted;
use App\Models\Bid;
use App\Models\WasteItem;
use Illuminate\Console\Command;

class TestBidBroadcast extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:bid {waste_item_id? : The waste item ID to test with} {--amount=250 : The bid amount} {--user_id=1 : The user ID making the bid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test broadcasting a bid event for a specific waste item';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $wasteItemId = $this->argument('waste_item_id');
        $amount = $this->option('amount');
        $userId = $this->option('user_id');
        
        if (!$wasteItemId) {
            // Find a random waste item or use ID 1
            $wasteItem = WasteItem::inRandomOrder()->first();
            $wasteItemId = $wasteItem ? $wasteItem->id : 1;
        }
        
        $this->info("Sending test bid broadcast for waste item #{$wasteItemId}");

        // Create a test bid for broadcasting
        $bid = [
            'id' => rand(1000, 9999),
            'amount' => $amount,
            'currency' => 'EUR',
            'status' => 'pending',
            'waste_item_id' => $wasteItemId,
            'user_id' => $userId,
            'maker' => [
                'name' => 'Test User'
            ],
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
            'test' => true
        ];
        
        // Broadcast the event
        event(new BidSubmitted($bid));
        
        $this->info('Event broadcasted successfully!');
        $this->line("Channel: waste-item.{$wasteItemId}.bids");
        $this->line("Event: bid-submitted");
        $this->line("Data: " . json_encode($bid, JSON_PRETTY_PRINT));
        
        return 0;
    }
}