<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Field extends Model
{
    protected $fillable = [
        'name',
        'image',
        'images',
        'address',
        'city',
        'latitude',
        'longitude',
        'price_per_hour',
        'contact_phone',
        'open_time',
        'close_time',
        'is_available',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'price_per_hour' => 'integer',
            'is_available' => 'boolean',
            'images' => 'array',
        ];
    }

    public function getImageUrlAttribute(): string
    {
        return $this->gallery_urls[0];
    }

    /**
     * Always returns exactly 5 image URLs for the gallery.
     * Real images first (from `images` array + legacy `image`), remaining slots filled with the placeholder.
     */
    public function getGalleryUrlsAttribute(): array
    {
        $disk = \Illuminate\Support\Facades\Storage::disk('public');
        $placeholder = asset('placeholder-image.png');

        $paths = collect([$this->image])
            ->merge($this->images ?? [])
            ->filter()
            ->unique()
            ->filter(fn ($path) => $disk->exists($path))
            ->map(fn ($path) => asset('storage/' . $path))
            ->values()
            ->all();

        $paths = array_slice($paths, 0, 5);

        while (count($paths) < 5) {
            $paths[] = $placeholder;
        }

        return $paths;
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(FutsalMatch::class);
    }
}
