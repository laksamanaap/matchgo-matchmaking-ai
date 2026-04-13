<?php

namespace Database\Seeders;

use App\Models\FutsalMatch;
use App\Models\MatchScoreAudit;
use App\Models\User;
use Illuminate\Database\Seeder;

class MatchScoreAuditSeeder extends Seeder
{
    public function run(): void
    {
        $auditor        = User::where('role', 'auditor')->first();
        $completedMatches = FutsalMatch::where('status', 'completed')
            ->orderBy('id')
            ->get();

        if ($completedMatches->isEmpty()) {
            return;
        }

        // Match 1: Garuda FC 5-3 Rajawali United → audit PENDING
        // (stats NOT updated — awaiting auditor review)
        MatchScoreAudit::create([
            'match_id'   => $completedMatches[0]->id,
            'status'     => 'pending',
            'auditor_id' => null,
        ]);

        // Match 2: Elang Muda 2-2 Meteor Depok → audit APPROVED
        // (stats already updated in MatchSeeder to reflect this approval)
        if ($completedMatches->count() >= 2) {
            MatchScoreAudit::create([
                'match_id'   => $completedMatches[1]->id,
                'status'     => 'approved',
                'auditor_id' => $auditor->id,
            ]);
        }
    }
}
