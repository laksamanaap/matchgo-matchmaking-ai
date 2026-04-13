<?php

namespace App\Observers;

use App\Models\Team;
use App\Models\TeamStats;
use App\Models\TeamVerification;

class TeamObserver
{
    /**
     * Auto-create record TeamStats kosong setiap kali Team baru dibuat.
     */
    public function created(Team $team): void
    {
        TeamStats::create([
            'team_id'        => $team->id,
            'total_matches'  => 0,
            'wins'           => 0,
            'losses'         => 0,
            'draws'          => 0,
            'goals_scored'   => 0,
            'goals_conceded' => 0,
        ]);

        TeamVerification::create([
            'team_id' => $team->id,
            'status'  => 'pending',
        ]);

        $team->update(['verification_status' => 'pending']);
    }
}
