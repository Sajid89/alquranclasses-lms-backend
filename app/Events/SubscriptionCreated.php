<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class SubscriptionCreated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public function broadcastOn()
    {
        return new Channel('new-subscription');
    }
}