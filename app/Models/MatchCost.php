<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchCost extends Model
{
    protected $fillable = [
        'match_id', 'total_cost', 'cost_per_team', 'dp_per_team', 'handling_fee', 'cost_per_player', 'payment_notes',
    ];

    public function futsalMatch(): BelongsTo
    {
        return $this->belongsTo(FutsalMatch::class, 'match_id');
    }
}
