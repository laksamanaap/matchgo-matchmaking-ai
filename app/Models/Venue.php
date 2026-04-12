<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venue extends Model
{
    protected $fillable = [
        'name', 'address', 'city', 'latitude', 'longitude',
        'price_per_hour', 'contact_phone', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude'  => 'decimal:8',
            'longitude' => 'decimal:8',
            'is_active' => 'boolean',
        ];
    }

    public function venueSchedules(): HasMany
    {
        return $this->hasMany(VenueSchedule::class);
    }

    public function futsalMatches(): HasMany
    {
        return $this->hasMany(FutsalMatch::class, 'venue_id');
    }
}
