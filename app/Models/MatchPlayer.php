<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchPlayer extends Model
{
    protected $fillable = ['match_id', 'user_id', 'team_id', 'attended'];

    protected function casts(): array
    {
        return [
            'attended' => 'boolean',
        ];
    }

    public function futsalMatch(): BelongsTo
    {
        return $this->belongsTo(FutsalMatch::class, 'match_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
