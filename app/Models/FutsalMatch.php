<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\MatchScoreAudit;

class FutsalMatch extends Model
{
    // "match" is a PHP reserved keyword — model named FutsalMatch, table stays "matches"
    protected $table = 'matches';

    protected $fillable = [
        'match_request_id', 'venue_id', 'field_id', 'team_a_id', 'team_b_id',
        'match_date', 'start_time', 'duration_minutes',
        'score_a', 'score_b', 'status',
    ];

    protected function casts(): array
    {
        return [
            'match_request_id' => 'integer',
            'venue_id'         => 'integer',
            'field_id'         => 'integer',
            'team_a_id'        => 'integer',
            'team_b_id'        => 'integer',
            'duration_minutes' => 'integer',
            'score_a'          => 'integer',
            'score_b'          => 'integer',
            'match_date'       => 'date',
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

    public function field(): BelongsTo
    {
        return $this->belongsTo(Field::class);
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

    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class, 'match_id');
    }

    public function matchPlayers(): HasMany
    {
        return $this->hasMany(MatchPlayer::class, 'match_id');
    }

    public function matchScoreAudit(): HasOne
    {
        return $this->hasOne(MatchScoreAudit::class, 'match_id');
    }

    public function autoMatchmakingQueues(): HasMany
    {
        return $this->hasMany(AutoMatchmakingQueue::class, 'match_id');
    }

    public function isAutoMatch(): bool
    {
        return $this->autoMatchmakingQueues()->exists();
    }
}
