<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use App\Models\TrialClass;

class TrialClassCreated implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $trialClass;

    public function __construct(TrialClass $trialClass)
    {
        $this->trialClass = $trialClass;
    }

    public function broadcastOn()
    {
        return new Channel('trial-class');
    }
}