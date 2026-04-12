<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FutsalMatch extends Model
{
    // "match" is a PHP reserved keyword — model named FutsalMatch, table stays "matches"
    protected $table = 'matches';

    protected $fillable = [
        'match_request_id', 'venue_id', 'team_a_id', 'team_b_id',
        'match_date', 'start_time', 'duration_minutes',
        'score_a', 'score_b', 'status',
    ];

    protected function casts(): array
    {
        return [
            'match_date' => 'date',
        ];
    }

    public function matchRequest(): BelongsTo
    {
        return $this->belongsTo(MatchRequest::class, 'match_request_id');
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function teamA(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_a_id');
    }

    public function teamB(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_b_id');
    }

    public function matchCost(): HasOne
    {
        return $this->hasOne(MatchCost::class, 'match_id');
    }

    public function matchPlayers(): HasMany
    {
        return $this->hasMany(MatchPlayer::class, 'match_id');
    }
}
