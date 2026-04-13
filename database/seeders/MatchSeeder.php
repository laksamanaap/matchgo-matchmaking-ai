<?php

namespace Database\Seeders;

use App\Models\FutsalMatch;
use App\Models\MatchCost;
use App\Models\MatchPlayer;
use App\Models\MatchRequest;
use App\Models\Team;
use App\Models\User;
use App\Models\Venue;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MatchSeeder extends Seeder
{
    public function run(): void
    {
        $teams   = Team::with('teamMembers')->get();
        $venues  = Venue::where('is_active', true)->get();
        $players = User::where('role', 'player')->pluck('id')->toArray();

        // Match yang sudah selesai (completed) — ambil dari accepted request id=1
        $acceptedRequest1 = MatchRequest::where('status', 'accepted')->first();

        $match1 = FutsalMatch::create([
            'match_request_id' => $acceptedRequest1?->id,
            'venue_id'         => $venues[0]->id,
            'team_a_id'        => $teams[0]->id,
            'team_b_id'        => $teams[1]->id,
            'match_date'       => Carbon::today()->subDays(7)->toDateString(),
            'start_time'       => '18:00:00',
            'duration_minutes' => 90,
            'score_a'          => 5,
            'score_b'          => 3,
            'status'           => 'completed',
        ]);

        $this->seedMatchPlayers($match1, $teams[0], $teams[1]);
        $this->seedMatchCost($match1);

        // Match yang sudah selesai #2
        $match2 = FutsalMatch::create([
            'match_request_id' => null,
            'venue_id'         => $venues[2]->id,
            'team_a_id'        => $teams[2]->id,
            'team_b_id'        => $teams[3]->id,
            'match_date'       => Carbon::today()->subDays(3)->toDateString(),
            'start_time'       => '09:00:00',
            'duration_minutes' => 90,
            'score_a'          => 2,
            'score_b'          => 2,
            'status'           => 'completed',
        ]);

        $this->seedMatchPlayers($match2, $teams[2], $teams[3]);
        $this->seedMatchCost($match2);

        // Match sedang berlangsung (ongoing)
        $match3 = FutsalMatch::create([
            'match_request_id' => null,
            'venue_id'         => $venues[1]->id,
            'team_a_id'        => $teams[4]->id,
            'team_b_id'        => $teams[5]->id,
            'match_date'       => Carbon::today()->toDateString(),
            'start_time'       => '19:00:00',
            'duration_minutes' => 60,
            'score_a'          => null,
            'score_b'          => null,
            'status'           => 'ongoing',
        ]);

        $this->seedMatchPlayers($match3, $teams[4], $teams[5]);

        // Match terjadwal (scheduled) — dari accepted request id=2
        $acceptedRequest2 = MatchRequest::where('status', 'accepted')->skip(1)->first();

        FutsalMatch::create([
            'match_request_id' => $acceptedRequest2?->id,
            'venue_id'         => $venues[3]->id,
            'team_a_id'        => $teams[0]->id,
            'team_b_id'        => $teams[2]->id,
            'match_date'       => Carbon::today()->addDays(3)->toDateString(),
            'start_time'       => '18:00:00',
            'duration_minutes' => 90,
            'score_a'          => null,
            'score_b'          => null,
            'status'           => 'scheduled',
        ]);

        // Match terjadwal #2
        FutsalMatch::create([
            'match_request_id' => null,
            'venue_id'         => $venues[4]->id,
            'team_a_id'        => $teams[1]->id,
            'team_b_id'        => $teams[3]->id,
            'match_date'       => Carbon::today()->addDays(5)->toDateString(),
            'start_time'       => '20:00:00',
            'duration_minutes' => 90,
            'score_a'          => null,
            'score_b'          => null,
            'status'           => 'scheduled',
        ]);

        // Match dibatalkan
        FutsalMatch::create([
            'match_request_id' => null,
            'venue_id'         => $venues[5]->id,
            'team_a_id'        => $teams[2]->id,
            'team_b_id'        => $teams[5]->id,
            'match_date'       => Carbon::today()->subDays(1)->toDateString(),
            'start_time'       => '15:00:00',
            'duration_minutes' => 60,
            'score_a'          => null,
            'score_b'          => null,
            'status'           => 'cancelled',
        ]);

        // Update TeamStats HANYA untuk match yang audit-nya sudah approved (match2)
        // match1 (Garuda FC vs Rajawali) audit masih PENDING → stats belum diupdate
        $this->updateTeamStats($teams[2], 1, 0, 0, 1, 2, 2); // Elang Muda   (match2 draw)
        $this->updateTeamStats($teams[3], 1, 0, 0, 1, 2, 2); // Meteor Depok (match2 draw)
    }

    private function seedMatchPlayers(FutsalMatch $match, Team $teamA, Team $teamB): void
    {
        $teamAMembers = $teamA->teamMembers()->take(5)->get();
        foreach ($teamAMembers as $member) {
            MatchPlayer::create([
                'match_id' => $match->id,
                'user_id'  => $member->user_id,
                'team_id'  => $teamA->id,
                'attended' => in_array($match->status, ['completed', 'ongoing']),
            ]);
        }

        $teamBMembers = $teamB->teamMembers()->take(5)->get();
        foreach ($teamBMembers as $member) {
            MatchPlayer::firstOrCreate(
                ['match_id' => $match->id, 'user_id' => $member->user_id],
                [
                    'team_id'  => $teamB->id,
                    'attended' => in_array($match->status, ['completed', 'ongoing']),
                ]
            );
        }
    }

    private function seedMatchCost(FutsalMatch $match): void
    {
        $match->loadMissing('venue');
        $totalCost    = (int) ($match->venue->price_per_hour * ($match->duration_minutes / 60));
        $costPerTeam  = (int) ($totalCost / 2);
        $playerCount  = $match->matchPlayers()->where('team_id', $match->team_a_id)->count();
        $costPerPlayer = $playerCount > 0 ? (int) ($costPerTeam / $playerCount) : 0;

        MatchCost::create([
            'match_id'        => $match->id,
            'total_cost'      => $totalCost,
            'cost_per_team'   => $costPerTeam,
            'cost_per_player' => $costPerPlayer,
        ]);
    }

    private function updateTeamStats(
        Team $team,
        int $total, int $wins, int $losses, int $draws,
        int $goalsScored, int $goalsConceded
    ): void {
        $team->teamStats()->update([
            'total_matches'  => $total,
            'wins'           => $wins,
            'losses'         => $losses,
            'draws'          => $draws,
            'goals_scored'   => $goalsScored,
            'goals_conceded' => $goalsConceded,
        ]);
    }
}
