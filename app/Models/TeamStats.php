<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamStats extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'team_id', 'total_matches', 'wins', 'losses',
        'draws', 'goals_scored', 'goals_conceded',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
