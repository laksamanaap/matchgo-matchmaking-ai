<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,              // 1. Users (super_admin + admin + auditor + 15 players)
            VenueSeeder::class,             // 2. Venues (7 aktif, 1 nonaktif)
            FieldSeeder::class,             // 2b. Fields untuk booking user-facing
            TeamSeeder::class,              // 3. Teams + TeamStats (auto Observer) + Members + Schedules
            VenueScheduleSeeder::class,     // 4. Venue Schedules (slot 2 minggu ke depan)
            TeamVerificationSeeder::class,  // 5. Team Verifications (verified/pending/rejected)
            MatchRequestSeeder::class,      // 6. Match Requests (accepted/pending/rejected/cancelled)
            MatchSeeder::class,             // 7. Matches + MatchPlayers + MatchCosts
            MatchScoreAuditSeeder::class,   // 8. Match Score Audits (pending/approved)
        ]);
    }
}
