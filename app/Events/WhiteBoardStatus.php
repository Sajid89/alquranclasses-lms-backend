<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class WhiteBoardStatus implements ShouldBroadcast
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
        return new Channel('whiteboard-share');
    }

    public function broadcastAs()
    {
        return 'WhiteboardShareStatus';
    }

    public function broadcastWith()
    {
        return [
            'status' => $this->status,
            'userId' => $this->userId,
        ];
    }
}
