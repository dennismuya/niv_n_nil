<?php

namespace App\Events;

use App\Models\Store;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockShare implements  ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $store;


    /**
     * Create a new event instance.
     */
    public function __construct($store)
    {
        $this->store = $store;

        //

    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn()
    {
        return new Channel('store_'.$this->store['id']);

    }
    public function broadcastWith()
    {
        return $this->store;
    }

        public function broadcastAs(): string
    {
        return 'stock.share';
    }
}


