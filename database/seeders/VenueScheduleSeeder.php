<?php

namespace Database\Seeders;

use App\Models\Field;
use App\Models\Venue;
use App\Models\VenueSchedule;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class VenueScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $venues = Venue::query()
            ->where('is_active', true)
            ->where('city', 'Malang')
            ->get();

        // Generate slot jadwal 2 minggu ke depan untuk setiap lapangan Malang.
        // Jam mengikuti data open_time/close_time di FieldSeeder.
        $today = Carbon::today();

        foreach ($venues as $venue) {
            $field = Field::query()
                ->where('name', $venue->name)
                ->where('city', 'Malang')
                ->first();

            if (! $field) {
                continue;
            }

            $slots = $this->slotsForField($field);

            for ($day = 0; $day < 14; $day++) {
                $date = $today->copy()->addDays($day);

                foreach ($slots as $index => [$start, $end]) {
                    // Deterministik agar seeding ulang konsisten: beberapa slot dibuat terisi.
                    $isBooked = (($venue->id + $day + $index) % 7) === 0;

                    VenueSchedule::updateOrCreate([
                        'venue_id'   => $venue->id,
                        'date'       => $date->toDateString(),
                        'start_time' => $start,
                        'end_time'   => $end,
                    ], [
                        'is_booked'  => $isBooked,
                    ]);
                }
            }
        }
    }

    private function slotsForField(Field $field): array
    {
        $openTime = $field->open_time ?? '08:00:00';
        $closeTime = $field->close_time ?? '22:00:00';

        $open = Carbon::parse('2026-01-01 ' . $openTime)->startOfMinute();
        $close = Carbon::parse('2026-01-01 ' . $closeTime)->startOfMinute();

        if ($close->lessThanOrEqualTo($open)) {
            $close->addDay();
        }

        $slots = [];
        $cursor = $open->copy();

        while ($cursor->copy()->addHours(2)->lessThanOrEqualTo($close)) {
            $start = $cursor->format('H:i');
            $end = $cursor->copy()->addHours(2)->format('H:i');

            $slots[] = [$start, $end];
            $cursor->addHours(2);
        }

        return $slots;
    }
}
