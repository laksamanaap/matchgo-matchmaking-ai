<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,          // 1. Users (admin + 15 players)
            VenueSeeder::class,         // 2. Venues (7 aktif, 1 nonaktif)
            TeamSeeder::class,          // 3. Teams + TeamStats (auto Observer) + Members + Schedules
            VenueScheduleSeeder::class, // 4. Venue Schedules (slot 2 minggu ke depan)
            MatchRequestSeeder::class,  // 5. Match Requests (accepted/pending/rejected/cancelled)
            MatchSeeder::class,         // 6. Matches + MatchPlayers + MatchCosts
        ]);
    }
}
