<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use App\Models\TeamVerification;

class Team extends Model
{
    protected $fillable = [
        'owner_id', 'name', 'city', 'latitude', 'longitude',
        'skill_level', 'verification_status', 'player_count', 'logo_url',
        'description', 'contact_number',
    ];

    protected function casts(): array
    {
        return [
            'owner_id'     => 'integer',
            'player_count' => 'integer',
            'latitude'     => 'decimal:8',
            'longitude'    => 'decimal:8',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function teamStats(): HasOne
    {
        return $this->hasOne(TeamStats::class);
    }

    public function teamSchedules(): HasMany
    {
        return $this->hasMany(TeamSchedule::class);
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(TeamMember::class);
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_members')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function sentMatchRequests(): HasMany
    {
        return $this->hasMany(MatchRequest::class, 'requester_team_id');
    }

    public function receivedMatchRequests(): HasMany
    {
        return $this->hasMany(MatchRequest::class, 'opponent_team_id');
    }

    public function matchesAsTeamA(): HasMany
    {
        return $this->hasMany(FutsalMatch::class, 'team_a_id');
    }

    public function matchesAsTeamB(): HasMany
    {
        return $this->hasMany(FutsalMatch::class, 'team_b_id');
    }

    public function teamVerification(): HasOne
    {
        return $this->hasOne(TeamVerification::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function autoMatchmakingQueues(): HasMany
    {
        return $this->hasMany(AutoMatchmakingQueue::class);
    }

    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    public function hasMinimumPlayers(int $minimum = 5): bool
    {
        return $this->activePlayerCount() >= $minimum;
    }

    public function activePlayerCount(): int
    {
        return 1 + $this->players()->count();
    }
}
