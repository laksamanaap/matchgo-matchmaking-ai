<?php

namespace Database\Seeders;

use App\Models\Venue;
use App\Models\VenueSchedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class VenueScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $venues = Venue::where('is_active', true)->get();

        // Generate slot jadwal 2 minggu ke depan untuk setiap lapangan aktif
        $today = Carbon::today();

        foreach ($venues as $venue) {
            for ($day = 0; $day < 14; $day++) {
                $date = $today->copy()->addDays($day);

                // Buat slot per 2 jam mulai pukul 07:00 sampai 22:00
                $slots = [
                    ['07:00', '09:00'],
                    ['09:00', '11:00'],
                    ['11:00', '13:00'],
                    ['13:00', '15:00'],
                    ['15:00', '17:00'],
                    ['17:00', '19:00'],
                    ['19:00', '21:00'],
                    ['21:00', '23:00'],
                ];

                foreach ($slots as [$start, $end]) {
                    // Random sebagian sudah dipesan supaya realistis
                    $isBooked = rand(0, 4) === 0; // ~20% sudah dipesan

                    VenueSchedule::create([
                        'venue_id'   => $venue->id,
                        'date'       => $date->toDateString(),
                        'start_time' => $start,
                        'end_time'   => $end,
                        'is_booked'  => $isBooked,
                    ]);
                }
            }
        }
    }
}
