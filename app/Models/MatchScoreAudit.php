<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchScoreAudit extends Model
{
    protected $fillable = [
        'match_id',
        'auditor_id',
        'status',
        'notes',
    ];

    public function futsalMatch(): BelongsTo
    {
        return $this->belongsTo(FutsalMatch::class, 'match_id');
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }
}
