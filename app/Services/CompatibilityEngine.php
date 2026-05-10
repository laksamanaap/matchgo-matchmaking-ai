<?php

namespace App\Services;

use App\Models\FutsalMatch;
use App\Models\MatchmakingQueue;

class CompatibilityEngine
{
    public const MATCH_THRESHOLD = 0.60;

    /**
     * Compute compatibility score between two waiting queues.
     * Returns null if hard-incompatible (level diff or distance over tolerance).
     *
     * compatibility_score =
     *   level_similarity * 0.4
     * + distance_score   * 0.2
     * + activity_score   * 0.1
     * + waiting_priority * 0.3
     */
    public function score(MatchmakingQueue $a, MatchmakingQueue $b): ?float
    {
        if ($a->team_id === $b->team_id) {
            return null;
        }

        $levelDiff = abs($a->skillLevelAsInt() - $b->skillLevelAsInt());
        $maxLevelTolerance = max($a->level_tolerance, $b->level_tolerance);
        if ($levelDiff > $maxLevelTolerance) {
            return null;
        }

        $distanceKm = $this->haversine(
            (float) $a->latitude, (float) $a->longitude,
            (float) $b->latitude, (float) $b->longitude
        );
        $maxRange = max($a->search_range_km, $b->search_range_km);
        if ($distanceKm > $maxRange) {
            return null;
        }

        $levelSimilarity = 1 - ($levelDiff / 3);
        $distanceScore   = 1 - min($distanceKm / max($maxRange, 1), 1);
        $activityScore   = $this->activityScore($a) + $this->activityScore($b);
        $activityScore   = min($activityScore / 2, 1);

        $waitSecs = max($a->waitingSeconds(), $b->waitingSeconds());
        $waitingPriority = min($waitSecs / 120, 1);

        $score = ($levelSimilarity * 0.4)
               + ($distanceScore   * 0.2)
               + ($activityScore   * 0.1)
               + ($waitingPriority * 0.3);

        return round($score, 4);
    }

    /**
     * Distance between two coordinates in kilometres using Haversine formula.
     */
    public function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
           + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
           * sin($dLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }

    /**
     * Score (0..1) from recent match activity. More recent matches = more active.
     */
    private function activityScore(MatchmakingQueue $queue): float
    {
        $recent = FutsalMatch::query()
            ->where(function ($q) use ($queue) {
                $q->where('team_a_id', $queue->team_id)
                  ->orWhere('team_b_id', $queue->team_id);
            })
            ->where('created_at', '>=', now()->subDays(30))
            ->count();

        return min($recent / 10, 1);
    }
}
