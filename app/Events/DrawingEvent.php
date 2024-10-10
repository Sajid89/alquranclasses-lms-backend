<?php
namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DrawingEvent implements ShouldBroadcastNow
{
    use InteractsWithSockets, SerializesModels;

    private $status;
    private $type;
    private $pos;
    private $user_id;

    public function __construct($status, $type, $pos, $user_id)
    {
        $this->status = $status;
        $this->type = $type;
        $this->pos = $pos;
        $this->user_id = $user_id;
    }

    public function broadcastOn()
    {
        Log::info('Broadcasting on DrawingEvent');
        return new Channel('drawing-share');
    }

    public function broadcastAs()
    {
        Log::info('Broadcasting as DrawingEvent');
        return 'DrawingShareEvent';
    }

    public function broadcastWith()
    {
        Log::info('Broadcasted DrawingEvent');
        return [
            'status' => $this->status,
            'type' => $this->type,
            'pos' => $this->pos,
            'user_id' => $this->user_id,
        ];
    }
}