<?php

namespace Database\Seeders;

use App\Models\MatchRequest;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MatchRequestSeeder extends Seeder
{
    public function run(): void
    {
        $teams = Team::all();

        // Garuda FC (competitive) vs Rajawali United (competitive) — accepted
        MatchRequest::create([
            'requester_team_id' => $teams[0]->id,
            'opponent_team_id'  => $teams[1]->id,
            'status'            => 'accepted',
            'preferred_date'    => Carbon::today()->addDays(3)->toDateString(),
        ]);

        // Elang Muda (semi_pro) vs Meteor Depok (semi_pro) — accepted
        MatchRequest::create([
            'requester_team_id' => $teams[2]->id,
            'opponent_team_id'  => $teams[3]->id,
            'status'            => 'accepted',
            'preferred_date'    => Carbon::today()->addDays(5)->toDateString(),
        ]);

        // Tangerang Warriors (casual) vs BSD Stars (casual) — pending
        MatchRequest::create([
            'requester_team_id' => $teams[4]->id,
            'opponent_team_id'  => $teams[5]->id,
            'status'            => 'pending',
            'preferred_date'    => Carbon::today()->addDays(7)->toDateString(),
        ]);

        // Garuda FC vs Elang Muda — pending (beda level tapi boleh request)
        MatchRequest::create([
            'requester_team_id' => $teams[0]->id,
            'opponent_team_id'  => $teams[2]->id,
            'status'            => 'pending',
            'preferred_date'    => Carbon::today()->addDays(10)->toDateString(),
        ]);

        // Rajawali vs BSD Stars — rejected
        MatchRequest::create([
            'requester_team_id' => $teams[1]->id,
            'opponent_team_id'  => $teams[5]->id,
            'status'            => 'rejected',
            'preferred_date'    => Carbon::yesterday()->toDateString(),
        ]);

        // Meteor Depok vs Tangerang Warriors — cancelled
        MatchRequest::create([
            'requester_team_id' => $teams[3]->id,
            'opponent_team_id'  => $teams[4]->id,
            'status'            => 'cancelled',
            'preferred_date'    => Carbon::today()->subDays(2)->toDateString(),
        ]);
    }
}
