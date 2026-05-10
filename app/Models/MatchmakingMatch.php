<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchmakingMatch extends Model
{
    protected $fillable = [
        'queue_a_id', 'queue_b_id',
        'compatibility_score',
        'accepted_by_a', 'accepted_by_b',
        'status', 'match_request_id',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'compatibility_score' => 'decimal:2',
            'accepted_by_a'       => 'boolean',
            'accepted_by_b'       => 'boolean',
            'expires_at'          => 'datetime',
        ];
    }

    public function queueA(): BelongsTo
    {
        return $this->belongsTo(MatchmakingQueue::class, 'queue_a_id');
    }

    public function queueB(): BelongsTo
    {
        return $this->belongsTo(MatchmakingQueue::class, 'queue_b_id');
    }

    public function matchRequest(): BelongsTo
    {
        return $this->belongsTo(MatchRequest::class);
    }

    public function isFullyAccepted(): bool
    {
        return $this->accepted_by_a && $this->accepted_by_b;
    }
}
