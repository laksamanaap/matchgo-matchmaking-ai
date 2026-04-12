<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Team extends Model
{
    protected $fillable = [
        'owner_id', 'name', 'city', 'latitude', 'longitude',
        'skill_level', 'player_count', 'logo_url',
    ];

    protected function casts(): array
    {
        return [
            'latitude'  => 'decimal:8',
            'longitude' => 'decimal:8',
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
}
