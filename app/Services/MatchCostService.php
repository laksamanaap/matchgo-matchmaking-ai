<?php

namespace App\Services;

use App\Models\FutsalMatch;
use App\Models\MatchCost;

class MatchCostService
{
    /**
     * Hitung dan simpan biaya pertandingan.
     *
     * total_cost     = venue/field.price_per_hour * (duration_minutes / 60)
     * cost_per_team  = total_cost / 2
     * cost_per_player = cost_per_team / jumlah pemain yang attended=true (fallback: semua terdaftar → team.player_count)
     */
    public function calculate(FutsalMatch $match): MatchCost
    {
        $match->loadMissing(['venue', 'field', 'matchPlayers', 'teamA']);

        $place = $match->field ?? $match->venue;

        if (! $place) {
            throw new \RuntimeException('Pertandingan belum memiliki lapangan untuk menghitung biaya.');
        }

        $totalCost   = (int) ($place->price_per_hour * ($match->duration_minutes / 60));
        $costPerTeam = (int) ($totalCost / 2);
        $dpPerTeam = $match->isAutoMatch()
            ? $costPerTeam
            : $costPerTeam;
        $handlingFee = (int) ceil($dpPerTeam * 0.1);

        // Prioritas: pemain yang hadir (attended=true) → semua terdaftar → fallback team.player_count
        $attendedCount = $match->matchPlayers
            ->where('team_id', $match->team_a_id)
            ->where('attended', true)
            ->count();

        $teamAPlayerCount = $attendedCount > 0
            ? $attendedCount
            : ($match->matchPlayers->where('team_id', $match->team_a_id)->count() ?: ($match->teamA->player_count ?? 5));

        $teamAPlayerCount = max(1, (int) $teamAPlayerCount);
        $costPerPlayer = (int) ($costPerTeam / $teamAPlayerCount);

        return MatchCost::updateOrCreate(
            ['match_id' => $match->id],
            [
                'total_cost'     => $totalCost,
                'cost_per_team'  => $costPerTeam,
                'dp_per_team' => $dpPerTeam,
                'handling_fee' => $handlingFee,
                'cost_per_player' => $costPerPlayer,
                'payment_notes' => $match->isAutoMatch()
                    ? 'AutoMatching wajib lunas 100% dari biaya per tim ditambah biaya admin 10%.'
                    : 'DP 50% dari harga lapangan wajib dibayar saat pertandingan dibuat. Biaya pengelola web 10% dari DP. Refund maksimal 6 jam setelah pertandingan dibuat.',
            ]
        );
    }
}
