<?php

namespace App\Events;

use App\Models\MatchmakingQueue;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class QueueTimeoutEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public MatchmakingQueue $queue) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("team.{$this->queue->team_id}");
    }

    public function broadcastAs(): string
    {
        return 'queue.timeout';
    }

    public function broadcastWith(): array
    {
        return [
            'queue_id' => $this->queue->id,
            'team_id'  => $this->queue->team_id,
            'message'  => 'Antrian matchmaking kedaluwarsa karena tidak ada lawan ditemukan.',
        ];
    }
}
