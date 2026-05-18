<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoMatchmakingQueue extends Model
{
    protected $fillable = [
        'team_id',
        'match_id',
        'match_date',
        'start_time',
        'duration_minutes',
        'radius_km',
        'skill_level',
        'status',
        'matched_at',
        'expired_at',
        'last_checked_at',
    ];

    protected function casts(): array
    {
        return [
            'match_date' => 'date',
            'duration_minutes' => 'integer',
            'radius_km' => 'integer',
            'matched_at' => 'datetime',
            'expired_at' => 'datetime',
            'last_checked_at' => 'datetime',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(FutsalMatch::class, 'match_id');
    }
}
