<?php

namespace App\Events;

use App\Models\MatchmakingMatch;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MatchFoundEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @param  string  $side  'a' or 'b' — which side this broadcast is for.
     */
    public function __construct(
        public MatchmakingMatch $match,
        public string $side,
    ) {}

    public function broadcastOn(): PrivateChannel
    {
        $teamId = $this->side === 'a'
            ? $this->match->queueA?->team_id
            : $this->match->queueB?->team_id;

        return new PrivateChannel("team.{$teamId}");
    }

    public function broadcastAs(): string
    {
        return 'match.found';
    }

    public function broadcastWith(): array
    {
        $myQueue       = $this->side === 'a' ? $this->match->queueA : $this->match->queueB;
        $opponentQueue = $this->side === 'a' ? $this->match->queueB : $this->match->queueA;

        return [
            'match_id'            => $this->match->id,
            'compatibility_score' => (float) $this->match->compatibility_score,
            'expires_at'          => $this->match->expires_at?->toIso8601String(),
            'my_team' => [
                'id'   => $myQueue?->team_id,
                'name' => $myQueue?->team?->name,
            ],
            'opponent' => [
                'id'          => $opponentQueue?->team_id,
                'name'        => $opponentQueue?->team?->name,
                'city'        => $opponentQueue?->team?->city,
                'skill_level' => $opponentQueue?->skill_level,
                'logo_url'    => $opponentQueue?->team?->logo_url,
            ],
        ];
    }
}
