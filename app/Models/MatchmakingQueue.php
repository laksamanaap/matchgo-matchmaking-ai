<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MatchmakingQueue extends Model
{
    protected $fillable = [
        'uuid', 'team_id', 'captain_id',
        'skill_level', 'latitude', 'longitude',
        'search_range_km', 'level_tolerance',
        'status', 'queued_at', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude'   => 'decimal:8',
            'longitude'  => 'decimal:8',
            'queued_at'  => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $queue) {
            $queue->uuid     ??= (string) Str::uuid();
            $queue->queued_at  ??= now();
            $queue->expires_at ??= now()->addMinutes(10);
        });
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function captain(): BelongsTo
    {
        return $this->belongsTo(User::class, 'captain_id');
    }

    public function matchesAsA(): HasMany
    {
        return $this->hasMany(MatchmakingMatch::class, 'queue_a_id');
    }

    public function matchesAsB(): HasMany
    {
        return $this->hasMany(MatchmakingMatch::class, 'queue_b_id');
    }

    public function isWaiting(): bool
    {
        return $this->status === 'waiting';
    }

    public function waitingSeconds(): int
    {
        return $this->queued_at?->diffInSeconds(now()) ?? 0;
    }

    public function skillLevelAsInt(): int
    {
        return match ($this->skill_level) {
            'casual'      => 1,
            'semi_pro'    => 2,
            'competitive' => 3,
            default       => 1,
        };
    }
}
