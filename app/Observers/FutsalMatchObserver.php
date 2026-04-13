<?php

namespace App\Observers;

use App\Models\FutsalMatch;
use App\Models\MatchScoreAudit;

class FutsalMatchObserver
{
    /**
     * Auto-create MatchScoreAudit (status=pending) setiap kali match selesai.
     */
    public function updated(FutsalMatch $match): void
    {
        if ($match->wasChanged('status') && $match->status === 'completed') {
            MatchScoreAudit::firstOrCreate(
                ['match_id' => $match->id],
                ['status' => 'pending']
            );
        }
    }
}
