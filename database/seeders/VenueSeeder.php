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
                'name' => 'Viva Futsal Malang',
                'address' => 'Jl. Bunga Andong, Jatimulyo, Kec. Lowokwaru, Kota Malang, Jawa Timur 65141',
                'city' => 'Malang',
                'latitude' => -7.9449,
                'longitude' => 112.6220,
                'price_per_hour' => 120000,
                'contact_phone' => '0341-484377',
                'is_active' => true,
            ],
            [
                'name' => 'Angkasa Futsal Malang',
                'address' => 'Jl. Papa Kuning No.40, Tulusrejo, Kec. Lowokwaru, Kota Malang, Jawa Timur 65141',
                'city' => 'Malang',
                'latitude' => -7.9410,
                'longitude' => 112.6360,
                'price_per_hour' => 100000,
                'contact_phone' => '0823-3520-6077',
                'is_active' => true,
            ],
            [
                'name' => 'Lapangan Futsal UIN Malang',
                'address' => 'Kampus 1 UIN Maulana Malik Ibrahim Malang, Kota Malang',
                'city' => 'Malang',
                'latitude' => -7.9517,
                'longitude' => 112.6071,
                'price_per_hour' => 100000,
                'contact_phone' => '-',
                'is_active' => true,
            ],
            [
                'name' => 'Buana Futsal Malang',
                'address' => 'Tlogosari 36 A, Kota Malang, Jawa Timur',
                'city' => 'Malang',
                'latitude' => -7.9870,
                'longitude' => 112.6350,
                'price_per_hour' => 100000,
                'contact_phone' => '0341-7744778',
                'is_active' => true,
            ],
            [
                'name' => 'Bima Sakti Futsal Malang',
                'address' => 'Sukun, Kota Malang, Jawa Timur 65147',
                'city' => 'Malang',
                'latitude' => -7.9970,
                'longitude' => 112.6110,
                'price_per_hour' => 90000,
                'contact_phone' => '-',
                'is_active' => true,
            ],
            [
                'name' => 'Champion Futsal Malang',
                'address' => 'Jl. Soekarno Hatta No.9, Mojolangu, Kec. Lowokwaru, Kota Malang, Jawa Timur 65142',
                'city' => 'Malang',
                'latitude' => -7.9384,
                'longitude' => 112.6212,
                'price_per_hour' => 130000,
                'contact_phone' => '0341-477123',
                'is_active' => true,
            ],
            [
                'name' => 'Galaxy Futsal Malang',
                'address' => 'Jl. Sukarno Hatta, Jatimulyo, Kec. Lowokwaru, Kota Malang, Jawa Timur 65141',
                'city' => 'Malang',
                'latitude' => -7.9402,
                'longitude' => 112.6188,
                'price_per_hour' => 110000,
                'contact_phone' => '0341-401234',
                'is_active' => true,
            ],
            [
                'name' => 'Mitra Futsal Malang',
                'address' => 'Jl. Tlogomas No.24, Tlogomas, Kec. Lowokwaru, Kota Malang, Jawa Timur 65144',
                'city' => 'Malang',
                'latitude' => -7.9335,
                'longitude' => 112.5972,
                'price_per_hour' => 100000,
                'contact_phone' => '0341-565123',
                'is_active' => true,
            ],
            [
                'name' => 'Score Futsal Malang',
                'address' => 'Jl. Bendungan Sutami No.18, Sumbersari, Kec. Lowokwaru, Kota Malang, Jawa Timur 65145',
                'city' => 'Malang',
                'latitude' => -7.9573,
                'longitude' => 112.6101,
                'price_per_hour' => 120000,
                'contact_phone' => '0341-551789',
                'is_active' => true,
            ],
            [
                'name' => 'Araya Futsal Malang',
                'address' => 'Jl. Simpang Borobudur, Mojolangu, Kec. Lowokwaru, Kota Malang, Jawa Timur 65142',
                'city' => 'Malang',
                'latitude' => -7.9298,
                'longitude' => 112.6404,
                'price_per_hour' => 140000,
                'contact_phone' => '0341-491456',
                'is_active' => true,
            ],
            [
                'name' => 'Mandala Futsal Malang',
                'address' => 'Jl. Danau Toba, Sawojajar, Kec. Kedungkandang, Kota Malang, Jawa Timur 65139',
                'city' => 'Malang',
                'latitude' => -7.9762,
                'longitude' => 112.6582,
                'price_per_hour' => 95000,
                'contact_phone' => '0341-712345',
                'is_active' => true,
            ],
            [
                'name' => 'D\'Pin Futsal Malang',
                'address' => 'Jl. Raya Tlogomas No.45, Tlogomas, Kec. Lowokwaru, Kota Malang, Jawa Timur 65144',
                'city' => 'Malang',
                'latitude' => -7.9311,
                'longitude' => 112.6005,
                'price_per_hour' => 105000,
                'contact_phone' => '0852-3456-7890',
                'is_active' => true,
            ],
        ];

        foreach ($venues as $venue) {
            Venue::updateOrCreate(
                ['name' => $venue['name']],
                $venue
            );
        }
    }
}
