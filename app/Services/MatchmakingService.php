<?php

namespace App\Services;

use App\Models\Team;
use App\Models\Venue;
use Illuminate\Support\Collection;

class MatchmakingService
{
    private const LEVEL_WEIGHT    = 0.5;
    private const DISTANCE_WEIGHT = 0.3;
    private const ACTIVITY_WEIGHT = 0.2;

    private const PROXIMITY_MAX_KM   = 50.0;
    private const ACTIVITY_MAX_MATCH = 20;
    private const MIN_SCORE          = 0.3;

    private const LEVEL_TIERS = [
        'casual'      => 0,
        'semi_pro'    => 1,
        'competitive' => 2,
    ];

    /**
     * Cari lawan berdasarkan level yang sama + overlap jadwal (filter lama, masih bisa digunakan).
     */
    public function findOpponent(Team $team): Collection
    {
        if ($team->verification_status !== 'verified') {
            throw new \RuntimeException('Tim kamu belum diverifikasi. Tunggu proses audit.');
        }

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
     * Cari lawan dengan skor kompatibilitas tertimbang.
     *
     * compatibility_score =
     *   (0.5 × level_similarity)
     * + (0.3 × proximity_score)
     * + (0.2 × activity_score)
     *
     * Hanya mengembalikan kandidat dengan skor ≥ MIN_SCORE (0.3), diurutkan tertinggi dahulu.
     */
    public function findOpponentWithScore(Team $team): Collection
    {
        if ($team->verification_status !== 'verified') {
            throw new \RuntimeException('Tim kamu belum diverifikasi. Tunggu proses audit.');
        }

        $candidates = Team::query()
            ->where('id', '!=', $team->id)
            ->where('verification_status', 'verified')
            ->with(['teamStats', 'teamSchedules', 'owner'])
            ->get();

        $myTier = self::LEVEL_TIERS[$team->skill_level] ?? 0;

        return $candidates
            ->map(function (Team $candidate) use ($team, $myTier) {
                $levelScore    = $this->levelSimilarity($myTier, self::LEVEL_TIERS[$candidate->skill_level] ?? 0);
                $proximityScore = $this->proximityScore($team, $candidate);
                $activityScore  = $this->activityScore($candidate);

                $candidate->compatibility_score =
                    (self::LEVEL_WEIGHT    * $levelScore)
                    + (self::DISTANCE_WEIGHT * $proximityScore)
                    + (self::ACTIVITY_WEIGHT * $activityScore);

                $candidate->distance_km = $this->haversineDistance(
                    (float) $team->latitude,
                    (float) $team->longitude,
                    (float) $candidate->latitude,
                    (float) $candidate->longitude,
                );

                return $candidate;
            })
            ->filter(fn (Team $c) => $c->compatibility_score >= self::MIN_SCORE)
            ->sortByDesc('compatibility_score')
            ->values();
    }

    /**
     * Cari venue terdekat dari titik tengah dua tim.
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

    // ─── Private helpers ────────────────────────────────────────────────────

    /**
     * level_similarity:
     *   same tier → 1.0 | differ by 1 → 0.5 | differ by 2 → 0.0
     */
    private function levelSimilarity(int $myTier, int $opponentTier): float
    {
        $diff = abs($myTier - $opponentTier);

        return match ($diff) {
            0       => 1.0,
            1       => 0.5,
            default => 0.0,
        };
    }

    /**
     * proximity_score = max(0, 1 - distance_km / MAX_KM)
     */
    private function proximityScore(Team $a, Team $b): float
    {
        if (! $a->latitude || ! $a->longitude || ! $b->latitude || ! $b->longitude) {
            return 0.5;
        }

        $km = $this->haversineDistance(
            (float) $a->latitude, (float) $a->longitude,
            (float) $b->latitude, (float) $b->longitude,
        );

        return max(0.0, 1.0 - $km / self::PROXIMITY_MAX_KM);
    }

    /**
     * activity_score = min(1, total_matches / ACTIVITY_MAX_MATCH)
     */
    private function activityScore(Team $team): float
    {
        $totalMatches = $team->teamStats?->total_matches ?? 0;

        return min(1.0, $totalMatches / self::ACTIVITY_MAX_MATCH);
    }

    /**
     * Hitung jarak antara dua titik koordinat menggunakan formula Haversine (dalam km).
     */
    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
