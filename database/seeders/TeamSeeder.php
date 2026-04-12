<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\TeamMember;
use App\Models\TeamSchedule;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil semua player (skip admin user id=1)
        $players = User::where('role', 'player')->pluck('id')->toArray();

        $teams = [
            [
                'name'        => 'Garuda FC',
                'city'        => 'Jakarta',
                'latitude'    => -6.2088,
                'longitude'   => 106.8456,
                'skill_level' => 'competitive',
                'player_count'=> 6,
                'owner_index' => 0, // budi
                'members'     => [0, 1, 2, 3, 4],
                'schedules'   => [
                    ['day' => 1, 'start' => '18:00', 'end' => '20:00'], // Senin
                    ['day' => 3, 'start' => '18:00', 'end' => '20:00'], // Rabu
                    ['day' => 6, 'start' => '08:00', 'end' => '10:00'], // Sabtu
                ],
            ],
            [
                'name'        => 'Rajawali United',
                'city'        => 'Jakarta',
                'latitude'    => -6.1944,
                'longitude'   => 106.8229,
                'skill_level' => 'competitive',
                'player_count'=> 6,
                'owner_index' => 5, // galih
                'members'     => [5, 6, 7, 8, 9],
                'schedules'   => [
                    ['day' => 2, 'start' => '19:00', 'end' => '21:00'], // Selasa
                    ['day' => 4, 'start' => '19:00', 'end' => '21:00'], // Kamis
                    ['day' => 6, 'start' => '10:00', 'end' => '12:00'], // Sabtu
                ],
            ],
            [
                'name'        => 'Elang Muda',
                'city'        => 'Bekasi',
                'latitude'    => -6.2383,
                'longitude'   => 106.9756,
                'skill_level' => 'semi_pro',
                'player_count'=> 5,
                'owner_index' => 10, // luthfi
                'members'     => [10, 11, 12, 13, 14],
                'schedules'   => [
                    ['day' => 0, 'start' => '08:00', 'end' => '10:00'], // Minggu
                    ['day' => 6, 'start' => '15:00', 'end' => '17:00'], // Sabtu
                ],
            ],
            [
                'name'        => 'Meteor Depok',
                'city'        => 'Depok',
                'latitude'    => -6.4025,
                'longitude'   => 106.7942,
                'skill_level' => 'semi_pro',
                'player_count'=> 5,
                'owner_index' => 1, // rizky
                'members'     => [1, 2, 10, 11, 12],
                'schedules'   => [
                    ['day' => 0, 'start' => '07:00', 'end' => '09:00'], // Minggu
                    ['day' => 3, 'start' => '20:00', 'end' => '22:00'], // Rabu
                ],
            ],
            [
                'name'        => 'Tangerang Warriors',
                'city'        => 'Tangerang',
                'latitude'    => -6.1783,
                'longitude'   => 106.6297,
                'skill_level' => 'casual',
                'player_count'=> 5,
                'owner_index' => 6, // hendra
                'members'     => [6, 7, 8, 13, 14],
                'schedules'   => [
                    ['day' => 5, 'start' => '17:00', 'end' => '19:00'], // Jumat
                    ['day' => 6, 'start' => '09:00', 'end' => '11:00'], // Sabtu
                ],
            ],
            [
                'name'        => 'BSD Stars',
                'city'        => 'Tangerang Selatan',
                'latitude'    => -6.3016,
                'longitude'   => 106.6529,
                'skill_level' => 'casual',
                'player_count'=> 5,
                'owner_index' => 13, // oscar
                'members'     => [13, 14, 3, 4, 5],
                'schedules'   => [
                    ['day' => 0, 'start' => '16:00', 'end' => '18:00'], // Minggu
                    ['day' => 5, 'start' => '19:00', 'end' => '21:00'], // Jumat
                ],
            ],
        ];

        foreach ($teams as $teamData) {
            $ownerUserId = $players[$teamData['owner_index']];

            $team = Team::create([
                'owner_id'    => $ownerUserId,
                'name'        => $teamData['name'],
                'city'        => $teamData['city'],
                'latitude'    => $teamData['latitude'],
                'longitude'   => $teamData['longitude'],
                'skill_level' => $teamData['skill_level'],
                'player_count'=> $teamData['player_count'],
            ]);

            // Tambah anggota tim (captain = owner, sisanya member)
            foreach ($teamData['members'] as $i => $playerIndex) {
                $userId = $players[$playerIndex];
                TeamMember::firstOrCreate(
                    ['team_id' => $team->id, 'user_id' => $userId],
                    ['role' => ($userId === $ownerUserId) ? 'captain' : 'member']
                );
            }

            // Tambah jadwal tim
            foreach ($teamData['schedules'] as $schedule) {
                TeamSchedule::create([
                    'team_id'    => $team->id,
                    'day_of_week'=> $schedule['day'],
                    'start_time' => $schedule['start'],
                    'end_time'   => $schedule['end'],
                    'is_active'  => true,
                ]);
            }
        }
    }
}
