<?php

namespace Database\Seeders;

use App\Models\Field;
use App\Models\Venue;
use Illuminate\Database\Seeder;

class FieldSeeder extends Seeder
{
    public function run(): void
    {
        Venue::query()
            ->where('is_active', true)
            ->get()
            ->each(function (Venue $venue) {
                Field::updateOrCreate(
                    ['name' => $venue->name],
                    [
                        'address' => $venue->address,
                        'city' => $venue->city,
                        'latitude' => $venue->latitude,
                        'longitude' => $venue->longitude,
                        'price_per_hour' => $venue->price_per_hour,
                        'contact_phone' => $venue->contact_phone,
                        'is_available' => true,
                    ]
                );
            });
    }
}
