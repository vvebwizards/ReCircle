<?php

namespace App\Events;

use App\Models\Bid;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BidSubmitted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The bid data.
     *
     * @var \App\Models\Bid|array
     */
    public $bid;

    /**
     * Whether this is a test event.
     *
     * @var bool
     */
    private $isTest = false;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Bid|array  $bid
     */
    public function __construct($bid)
    {
        $this->bid = $bid;
        $this->isTest = is_array($bid);
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        if ($this->isTest) {
            $data = $this->bid;
            $data['debug_info'] = [
                'time' => now()->toIso8601String(),
                'channel' => 'waste-item.'.($data['waste_item_id'] ?? 1).'.bids',
                'event' => 'bid-submitted',
                'isTest' => true,
            ];
        } else {
            $data = [
                'id' => $this->bid->id,
                'amount' => $this->bid->amount,
                'currency' => $this->bid->currency ?? 'EUR',
                'waste_item_id' => $this->bid->waste_item_id,
                'status' => $this->bid->status,
                'created_at' => $this->bid->created_at->toIso8601String(),
                'maker' => [
                    'name' => $this->bid->maker ? $this->bid->maker->name : 'Unknown',
                ],
                'debug_info' => [
                    'time' => now()->toIso8601String(),
                    'channel' => 'waste-item.'.$this->bid->waste_item_id.'.bids',
                    'event' => 'bid-submitted',
                ],
            ];
        }

        // Log the event data for debugging
        \Log::info('Broadcasting BidSubmitted event', $data);

        return $data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        if ($this->isTest) {
            return [
                new Channel('waste-item.'.($this->bid['waste_item_id'] ?? 1).'.bids'),
                new PrivateChannel('user.'.($this->bid['user_id'] ?? 1).'.bids'),
            ];
        }

        return [
            new Channel('waste-item.'.$this->bid->waste_item_id.'.bids'),
            new PrivateChannel('user.'.$this->bid->maker_id.'.bids'),
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'bid-submitted';
    }
}
