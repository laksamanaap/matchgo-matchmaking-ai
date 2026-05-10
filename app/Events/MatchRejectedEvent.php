<?php

namespace App\Events;

use App\Models\MatchmakingMatch;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MatchRejectedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  string  $reason  'rejected' | 'timeout'
     */
    public function __construct(
        public MatchmakingMatch $match,
        public string $reason = 'rejected',
    ) {}

    /**
     * Broadcast on BOTH teams' private channels.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [];

        if ($teamAId = $this->match->queueA?->team_id) {
            $channels[] = new PrivateChannel("team.{$teamAId}");
        }
        if ($teamBId = $this->match->queueB?->team_id) {
            $channels[] = new PrivateChannel("team.{$teamBId}");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'match.rejected';
    }

    public function broadcastWith(): array
    {
        return [
            'match_id' => $this->match->id,
            'reason'   => $this->reason,
        ];
    }
}
