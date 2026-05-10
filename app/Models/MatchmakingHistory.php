<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchmakingHistory extends Model
{
    protected $table = 'matchmaking_history';

    protected $fillable = [
        'team_id', 'matched_team_id',
        'compatibility_score', 'queue_duration_seconds',
        'result',
    ];

    protected function casts(): array
    {
        return [
            'compatibility_score' => 'decimal:2',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function matchedTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'matched_team_id');
    }
}
