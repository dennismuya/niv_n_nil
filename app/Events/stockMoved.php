<?php /** @noinspection PhpUnused */

namespace App\Events;

use App\Models\Store;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class stockMoved implements shouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public  $store;
    public $stock;
    public $user;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, Store $store, $stock)
    {
        $this->store = $store;
        $this->stock = $stock;
        $this->user = $user;
    }


    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
//    public function broadcastWith(): array
//    {
////        return ['message' => $this->user->user_name . ' shared ' . count($this->stock) . ' stock with you'];
//        return ['mesg'=>'hello'];
//
//    }


    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public
    function broadcastOn()
    {
        return new PrivateChannel('store'.$this->store->id);

    }


    public function broadcastWith(){
        return ['holla'=>'hello'];
    }



//    /**
//     * The event's broadcast name.
//     */
//    public function broadcastAs(): string
//    {
//        return 'stock.moved';
//    }

}
