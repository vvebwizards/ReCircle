<?php

namespace App\Console\Commands;

use App\Events\BidSubmitted;
use App\Models\Bid;
use Illuminate\Console\Command;

class TestPusher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:pusher {bid_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Pusher broadcasting with a bid';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $bidId = $this->argument('bid_id');

        if ($bidId) {
            // Use an existing bid
            $bid = Bid::find($bidId);
            if (! $bid) {
                $this->error("Bid with ID {$bidId} not found");

                return 1;
            }
        } else {
            // Create a test bid
            $bid = new Bid;
            $bid->id = 999999;
            $bid->waste_item_id = 1;
            $bid->maker_id = 1;
            $bid->amount = 100.00;
            $bid->currency = 'USD';
            $bid->status = 'pending';
            $bid->created_at = now();
            $bid->updated_at = now();

            // We need to set up the maker relationship for the broadcast
            $bid->setRelation('maker', (object) ['name' => 'Test User']);
        }

        $this->info("Broadcasting test bid event for bid #{$bid->id}");

        // Broadcast the event
        event(new BidSubmitted($bid));

        $this->info('Event broadcasted successfully. Check your browser to see if it was received.');

        return 0;
    }
}
