<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venue extends Model
{
    protected $fillable = [
        'name', 'images', 'address', 'city', 'latitude', 'longitude',
        'price_per_hour', 'contact_phone', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'latitude'  => 'decimal:8',
            'longitude' => 'decimal:8',
            'is_active' => 'boolean',
            'images'    => 'array',
        ];
    }

    protected static function booted(): void
    {
        // Sinkronkan venue ke tabel fields (sumber data matchmaking + halaman detail publik)
        static::saved(function (Venue $venue) {
            Field::updateOrCreate(
                ['name' => $venue->name],
                [
                    'image'          => $venue->images[0] ?? null,
                    'images'         => $venue->images,
                    'address'        => $venue->address,
                    'city'           => $venue->city,
                    'latitude'       => $venue->latitude,
                    'longitude'      => $venue->longitude,
                    'price_per_hour' => $venue->price_per_hour,
                    'contact_phone'  => $venue->contact_phone,
                    'is_available'   => $venue->is_active,
                ]
            );
        });
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
