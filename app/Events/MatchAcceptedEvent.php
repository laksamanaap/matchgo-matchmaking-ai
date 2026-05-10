<?php

namespace App\Events;

use App\Models\MatchmakingMatch;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MatchAcceptedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public MatchmakingMatch $match,
        public int $teamId,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel("team.{$this->teamId}");
    }

    public function broadcastAs(): string
    {
        return 'match.accepted';
    }

    public function broadcastWith(): array
    {
        return [
            'match_id'         => $this->match->id,
            'match_request_id' => $this->match->match_request_id,
            'fully_accepted'   => $this->match->isFullyAccepted(),
        ];
    }
}
