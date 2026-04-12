<?php

namespace App\Services;

use App\Models\Team;
use App\Models\Venue;
use Illuminate\Support\Collection;

class MatchmakingService
{
    /**
     * Cari lawan yang cocok berdasarkan:
     * - skill_level sama
     * - memiliki jadwal (team_schedules) yang overlap dengan jadwal $team
     * - bukan tim yang sama
     */
    public function findOpponent(Team $team): Collection
    {
        $teamScheduleDays = $team->teamSchedules()
            ->where('is_active', true)
            ->pluck('day_of_week');

        return Team::query()
            ->where('id', '!=', $team->id)
            ->where('skill_level', $team->skill_level)
            ->whereHas('teamSchedules', function ($query) use ($teamScheduleDays) {
                $query->where('is_active', true)
                      ->whereIn('day_of_week', $teamScheduleDays);
            })
            ->with(['teamStats', 'owner'])
            ->get();
    }

    /**
     * Cari venue terdekat dari titik tengah dua tim.
     * - Hitung midpoint koordinat kedua tim
     * - Cari venue aktif yang tidak ada slot is_booked=true yang overlap $date & $time
     * - Urutkan dari terdekat ke terjauh
     */
    public function findVenue(Team $teamA, Team $teamB, string $date, string $time): Collection
    {
        $midLat = ((float) $teamA->latitude + (float) $teamB->latitude) / 2;
        $midLon = ((float) $teamA->longitude + (float) $teamB->longitude) / 2;

        $venues = Venue::query()
            ->where('is_active', true)
            ->whereDoesntHave('venueSchedules', function ($query) use ($date, $time) {
                $query->where('date', $date)
                      ->where('is_booked', true)
                      ->where('start_time', '<=', $time)
                      ->where('end_time', '>', $time);
            })
            ->get();

        return $venues
            ->map(function (Venue $venue) use ($midLat, $midLon) {
                $venue->distance_km = $this->haversineDistance(
                    $midLat, $midLon,
                    (float) $venue->latitude,
                    (float) $venue->longitude
                );
                return $venue;
            })
            ->sortBy('distance_km')
            ->values();
    }

    /**
     * Hitung jarak antara dua titik koordinat menggunakan formula Haversine (dalam km).
     */
    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
