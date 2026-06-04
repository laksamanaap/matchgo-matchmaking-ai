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

        $malangFields = [
            [
                'name' => 'Viva Futsal Malang',
                'address' => 'Jl. Bunga Andong, Jatimulyo, Kec. Lowokwaru, Kota Malang, Jawa Timur 65141',
                'city' => 'Malang',
                'latitude' => -7.9449,
                'longitude' => 112.6220,
                'price_per_hour' => 120000,
                'contact_phone' => '0341-484377',
                'open_time' => '06:00:00',
                'close_time' => '02:00:00',
            ],
            [
                'name' => 'Angkasa Futsal Malang',
                'address' => 'Jl. Papa Kuning No.40, Tulusrejo, Kec. Lowokwaru, Kota Malang, Jawa Timur 65141',
                'city' => 'Malang',
                'latitude' => -7.9410,
                'longitude' => 112.6360,
                'price_per_hour' => 100000,
                'contact_phone' => '0823-3520-6077',
                'open_time' => '08:00:00',
                'close_time' => '22:00:00',
            ],
            [
                'name' => 'Lapangan Futsal UIN Malang',
                'address' => 'Kampus 1 UIN Maulana Malik Ibrahim Malang, Kota Malang',
                'city' => 'Malang',
                'latitude' => -7.9517,
                'longitude' => 112.6071,
                'price_per_hour' => 100000,
                'contact_phone' => null,
                'open_time' => '08:00:00',
                'close_time' => '22:00:00',
            ],
            [
                'name' => 'Buana Futsal Malang',
                'address' => 'Tlogosari 36 A, Kota Malang, Jawa Timur',
                'city' => 'Malang',
                'latitude' => -7.9870,
                'longitude' => 112.6350,
                'price_per_hour' => 100000,
                'contact_phone' => '0341-7744778',
                'open_time' => '08:00:00',
                'close_time' => '23:00:00',
            ],
            [
                'name' => 'Bima Sakti Futsal Malang',
                'address' => 'Sukun, Kota Malang, Jawa Timur 65147',
                'city' => 'Malang',
                'latitude' => -7.9970,
                'longitude' => 112.6110,
                'price_per_hour' => 90000,
                'contact_phone' => null,
                'open_time' => '08:00:00',
                'close_time' => '23:00:00',
            ],
        ];

        foreach ($malangFields as $field) {
            Field::updateOrCreate(
                ['name' => $field['name']],
                array_merge($field, [
                    'is_available' => true,
                ])
            );
        }
    }
}
