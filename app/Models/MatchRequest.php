<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MatchRequest extends Model
{
    protected $fillable = [
        'requester_team_id', 'opponent_team_id', 'status', 'preferred_date', 'preferred_location',
    ];

    protected function casts(): array
    {
        return [
            'requester_team_id' => 'integer',
            'opponent_team_id'  => 'integer',
            'preferred_date'    => 'date',
        ];
    }

    public function requesterTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'requester_team_id');
    }

    public function opponentTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'opponent_team_id');
    }

    public function futsalMatch(): HasOne
    {
        return $this->hasOne(FutsalMatch::class, 'match_request_id');
    }
}
