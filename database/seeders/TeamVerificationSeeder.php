<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\TeamVerification;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeamVerificationSeeder extends Seeder
{
    public function run(): void
    {
        $auditor = User::where('role', 'auditor')->first();
        $teams   = Team::all();

        // Index reference (matches TeamSeeder order):
        // [0] Garuda FC       → verified
        // [1] Rajawali United → verified
        // [2] Elang Muda      → verified
        // [3] Meteor Depok    → verified
        // [4] Tangerang Warriors → pending  (no changes needed)
        // [5] BSD Stars       → rejected

        $verified = [$teams[0], $teams[1], $teams[2], $teams[3]];

        foreach ($verified as $team) {
            TeamVerification::where('team_id', $team->id)->update([
                'status'     => 'verified',
                'auditor_id' => $auditor->id,
            ]);
            $team->update(['verification_status' => 'verified']);
        }

        // BSD Stars → rejected
        TeamVerification::where('team_id', $teams[5]->id)->update([
            'status'     => 'rejected',
            'notes'      => 'Dokumen pendaftaran tidak lengkap dan data anggota belum memenuhi syarat.',
            'auditor_id' => $auditor->id,
        ]);
        $teams[5]->update(['verification_status' => 'rejected']);

        // Tangerang Warriors → still pending (observer already set this, no update needed)
    }
}
