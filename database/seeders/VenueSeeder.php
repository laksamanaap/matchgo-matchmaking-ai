<?php

namespace Database\Seeders;

use App\Models\Venue;
use Illuminate\Database\Seeder;

class VenueSeeder extends Seeder
{
    public function run(): void
    {
        $venues = [
            [
                'name'           => 'Lapangan Futsal Senayan Sport',
                'address'        => 'Jl. Asia Afrika, Gelora, Tanah Abang, Jakarta Pusat',
                'city'           => 'Jakarta',
                'latitude'       => -6.2183,
                'longitude'      => 106.8017,
                'price_per_hour' => 200000,
                'contact_phone'  => '021-5731234',
                'is_active'      => true,
            ],
            [
                'name'           => 'Arena Futsal Kemayoran',
                'address'        => 'Jl. Benyamin Sueb, Kemayoran, Jakarta Pusat',
                'city'           => 'Jakarta',
                'latitude'       => -6.1594,
                'longitude'      => 106.8451,
                'price_per_hour' => 175000,
                'contact_phone'  => '021-6541234',
                'is_active'      => true,
            ],
            [
                'name'           => 'Futsal Planet Bekasi',
                'address'        => 'Jl. Ahmad Yani No. 12, Bekasi Timur',
                'city'           => 'Bekasi',
                'latitude'       => -6.2383,
                'longitude'      => 107.0122,
                'price_per_hour' => 150000,
                'contact_phone'  => '021-8841234',
                'is_active'      => true,
            ],
            [
                'name'           => 'GOR Futsal Depok Jaya',
                'address'        => 'Jl. Margonda Raya No. 55, Depok',
                'city'           => 'Depok',
                'latitude'       => -6.3794,
                'longitude'      => 106.8314,
                'price_per_hour' => 130000,
                'contact_phone'  => '021-7771234',
                'is_active'      => true,
            ],
            [
                'name'           => 'Futsal Kingdom Tangerang',
                'address'        => 'Jl. MH Thamrin No. 8, Tangerang',
                'city'           => 'Tangerang',
                'latitude'       => -6.1783,
                'longitude'      => 106.6297,
                'price_per_hour' => 160000,
                'contact_phone'  => '021-5521234',
                'is_active'      => true,
            ],
            [
                'name'           => 'Lapangan Futsal BSD',
                'address'        => 'Jl. Pahlawan Seribu, BSD City, Tangerang Selatan',
                'city'           => 'Tangerang Selatan',
                'latitude'       => -6.3016,
                'longitude'      => 106.6529,
                'price_per_hour' => 180000,
                'contact_phone'  => '021-5381234',
                'is_active'      => true,
            ],
            [
                'name'           => 'Futsal Center Bogor',
                'address'        => 'Jl. Pajajaran No. 30, Bogor Tengah',
                'city'           => 'Bogor',
                'latitude'       => -6.5971,
                'longitude'      => 106.7960,
                'price_per_hour' => 120000,
                'contact_phone'  => '0251-8341234',
                'is_active'      => true,
            ],
            [
                'name'           => 'Arena Futsal Grogol (Nonaktif)',
                'address'        => 'Jl. S. Parman No. 3, Grogol, Jakarta Barat',
                'city'           => 'Jakarta',
                'latitude'       => -6.1676,
                'longitude'      => 106.7886,
                'price_per_hour' => 140000,
                'contact_phone'  => '021-5671234',
                'is_active'      => false,
            ],
        ];

        foreach ($venues as $venue) {
            Venue::create($venue);
        }
    }
}
