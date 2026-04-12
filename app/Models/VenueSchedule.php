<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VenueSchedule extends Model
{
    protected $fillable = ['venue_id', 'date', 'start_time', 'end_time', 'is_booked'];

    protected function casts(): array
    {
        return [
            'date'      => 'date',
            'is_booked' => 'boolean',
        ];
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }
}
