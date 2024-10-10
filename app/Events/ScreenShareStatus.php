<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\Log;

class ScreenShareStatus implements ShouldBroadcast
{
    use InteractsWithSockets, SerializesModels;

    public $status;
    public $userId;

    public function __construct($status, $userId)
    {
        $this->status = $status;
        $this->userId = $userId;
    }

    public function broadcastOn()
    {
        return new Channel('screen-share');
    }

    public function broadcastAs()
    {
        return 'ScreenShareStatus';
    }

    public function broadcastWith()
    {
        return [
            'status' => $this->status,
            'userId' => $this->userId,
        ];
    }
}
