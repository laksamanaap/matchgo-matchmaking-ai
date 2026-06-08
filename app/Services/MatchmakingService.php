<?php

namespace App\Services;

use App\Models\AutoMatchmakingQueue;
use App\Models\Booking;
use App\Models\Field;
use App\Models\FutsalMatch;
use App\Models\MatchCost;
use App\Models\Payment;
use App\Models\Team;
use App\Models\Venue;
use App\Notifications\MatchNotification;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MatchmakingService
{
    private const AUTO_MATCH_START_HOUR = 7;
    private const AUTO_MATCH_EARLIEST_MATCH_HOUR = 10;
    private const AUTO_MATCH_LATEST_MATCH_HOUR = 22;

    public function defaultMatchTime(?Carbon $now = null): Carbon
    {
        $matchTime = ($now?->copy() ?? now())->addHours(3);

        if ($matchTime->minute !== 0 || $matchTime->second !== 0 || $matchTime->micro !== 0) {
            $matchTime->addHour();
        }

        return $matchTime->startOfHour();
    }

    public function autoMatchTime(?Carbon $now = null): Carbon
    {
        return $this->defaultMatchTime($now);
    }

    /**
     * @return array{available: bool, message: string|null, match_time: Carbon}
     */
    public function autoMatchAvailability(?Carbon $now = null): array
    {
        $now = $now?->copy() ?? now();
        $matchTime = $this->autoMatchTime($now);
        $usageStart = $now->copy()->setTime(self::AUTO_MATCH_START_HOUR, 0, 0);
        $earliestMatch = $matchTime->copy()->setTime(self::AUTO_MATCH_EARLIEST_MATCH_HOUR, 0, 0);
        $latestMatch = $matchTime->copy()->setTime(self::AUTO_MATCH_LATEST_MATCH_HOUR, 0, 0);

        if ($now->lt($usageStart)) {
            return [
                'available' => false,
                'message' => 'AutoMatching baru bisa digunakan mulai pukul 07:00.',
                'match_time' => $matchTime,
            ];
        }

        if ($matchTime->lt($earliestMatch)) {
            return [
                'available' => false,
                'message' => 'Pertandingan AutoMatching hanya tersedia mulai pukul 10:00.',
                'match_time' => $matchTime,
            ];
        }

        if ($matchTime->gt($latestMatch)) {
            return [
                'available' => false,
                'message' => 'Pertandingan AutoMatching terakhir dijadwalkan mulai pukul 22:00. Silakan cari lagi besok mulai pukul 07:00.',
                'match_time' => $matchTime,
            ];
        }

        return [
            'available' => true,
            'message' => null,
            'match_time' => $matchTime,
        ];
    }

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

    public function recommendOpponents(Team $team): Collection
    {
        $levelWeights = [
            'casual' => 1,
            'semi_pro' => 2,
            'competitive' => 3,
        ];

        $sourceLevel = $levelWeights[$team->skill_level] ?? 1;
        $preferredDays = $team->teamSchedules()->where('is_active', true)->pluck('day_of_week')->unique();

        return Team::query()
            ->where('id', '!=', $team->id)
            ->where('verification_status', 'verified')
            ->with(['teamStats', 'owner', 'teamSchedules'])
            ->get()
            ->map(function (Team $candidate) use ($team, $sourceLevel, $levelWeights, $preferredDays) {
                $candidateLevel = $levelWeights[$candidate->skill_level] ?? 1;
                $levelDiff = abs($sourceLevel - $candidateLevel);
                $compatibility = max(0, 100 - ($levelDiff * 30));

                $commonDays = $preferredDays
                    ->intersect($candidate->teamSchedules->pluck('day_of_week'))
                    ->count();

                $compatibility += min(20, $commonDays * 5);
                $compatibility += $candidate->teamStats?->rating ?? 0;

                $candidate->distance_km = $this->haversineDistance(
                    (float) $team->latitude,
                    (float) $team->longitude,
                    (float) $candidate->latitude,
                    (float) $candidate->longitude
                );

                $distanceScore = max(0, 20 - $candidate->distance_km);
                $compatibility += $distanceScore;

                $candidate->compatibility_score = min(100, max(0, $compatibility));

                return $candidate;
            })
            ->sortByDesc('compatibility_score')
            ->values();
    }

    public function recommendAutoMatches(Team $team, string $date, string $time, int $durationMinutes = 90): Collection
    {
        $durationHours = (int) ceil($durationMinutes / 60);

        return Team::query()
            ->where('id', '!=', $team->id)
            ->where('skill_level', $team->skill_level)
            ->where('verification_status', 'verified')
            ->with(['teamStats', 'owner'])
            ->get()
            ->map(function (Team $candidate) use ($team, $date, $time, $durationHours) {
                $midpoint = $this->midpoint($team, $candidate);
                $field = $this->findNearestAvailableField($team, $candidate, $date, $time, $durationHours);

                if (! $field) {
                    return null;
                }

                $candidate->recommended_field = $field;
                $candidate->midpoint_latitude = $midpoint['latitude'];
                $candidate->midpoint_longitude = $midpoint['longitude'];
                $candidate->field_distance_from_midpoint_km = $field->distance_from_midpoint_km;
                $candidate->team_distance_km = $this->haversineDistance(
                    (float) $team->latitude,
                    (float) $team->longitude,
                    (float) $candidate->latitude,
                    (float) $candidate->longitude
                );
                $candidate->estimated_total_cost = $field->price_per_hour * $durationHours;
                $candidate->estimated_cost_per_team = (int) round($candidate->estimated_total_cost / 2);

                return $candidate;
            })
            ->filter()
            ->sortBy([
                ['field_distance_from_midpoint_km', 'asc'],
                ['team_distance_km', 'asc'],
            ])
            ->values();
    }

    public function startAutoSearch(Team $team, int $durationMinutes = 60, int $radiusKm = 10): AutoMatchmakingQueue
    {
        return DB::transaction(function () use ($team, $durationMinutes, $radiusKm) {
            AutoMatchmakingQueue::query()
                ->where('team_id', $team->id)
                ->whereIn('status', ['waiting', 'searching'])
                ->update(['status' => 'cancelled']);

            $availability = $this->autoMatchAvailability();

            if (! $availability['available']) {
                throw new \RuntimeException($availability['message'] ?? 'AutoMatching belum tersedia saat ini.');
            }

            $matchTime = $availability['match_time'];

            $queue = AutoMatchmakingQueue::create([
                'team_id' => $team->id,
                'match_date' => $matchTime->toDateString(),
                'start_time' => $matchTime->format('H:i:s'),
                'duration_minutes' => $durationMinutes,
                'radius_km' => $radiusKm,
                'skill_level' => $team->skill_level,
                'status' => 'searching',
                'expired_at' => now()->addMinutes(5),
            ]);

            return $queue->fresh(['match.teamA', 'match.teamB', 'match.field', 'match.matchCost']);
        });
    }

    public function processQueue(AutoMatchmakingQueue $queue): ?FutsalMatch
    {
        return DB::transaction(function () use ($queue) {
            $queue = AutoMatchmakingQueue::query()
                ->whereKey($queue->id)
                ->lockForUpdate()
                ->with('team.owner')
                ->first();

            if (! $queue || ! in_array($queue->status, ['waiting', 'searching'], true)) {
                return $queue?->match;
            }

            if ($queue->expired_at && $queue->expired_at->isPast()) {
                $queue->update(['status' => 'expired', 'last_checked_at' => now()]);
                $queue->team?->owner?->notify(new MatchNotification(
                    'auto_match_expired',
                    'Lawan tidak ditemukan. Silakan cari ulang saat tim kamu siap bermain.',
                    $queue->id
                ));

                return null;
            }

            $team = $queue->team;
            $durationHours = (int) ceil($queue->duration_minutes / 60);
            $matchDate = $queue->match_date?->toDateString() ?? $this->autoMatchTime()->toDateString();
            $startTime = $queue->start_time
                ? Carbon::parse($queue->start_time)->format('H:i:s')
                : $this->autoMatchTime()->format('H:i:s');

            $candidates = AutoMatchmakingQueue::query()
                ->whereIn('status', ['waiting', 'searching'])
                ->where('id', '!=', $queue->id)
                ->where('team_id', '!=', $team->id)
                ->where('skill_level', $queue->skill_level)
                ->where('duration_minutes', $queue->duration_minutes)
                ->whereDate('match_date', $matchDate)
                ->whereTime('start_time', $startTime)
                ->where(function ($query) {
                    $query->whereNull('expired_at')
                        ->orWhere('expired_at', '>', now());
                })
                ->whereHas('team', fn ($query) => $query
                    ->where('verification_status', 'verified')
                    ->has('players', '>=', 4))
                ->with('team.owner')
                ->oldest()
                ->lockForUpdate()
                ->get();

            foreach ($candidates as $candidateQueue) {
                $opponent = $candidateQueue->team;
                $teamDistanceKm = $this->teamDistanceKm($team, $opponent);
                $allowedRadiusKm = min($queue->radius_km, $candidateQueue->radius_km);

                if ($teamDistanceKm > $allowedRadiusKm) {
                    continue;
                }

                $field = $this->findNearestAvailableField($team, $opponent, $matchDate, $startTime, $durationHours);

                if (! $field) {
                    continue;
                }

                return $this->createPendingAutoMatch($queue, $candidateQueue, $field, $matchDate, $startTime);
            }

            $queue->update(['last_checked_at' => now()]);

            return null;
        });
    }

    public function expireStaleQueues(): int
    {
        $expiredQueues = AutoMatchmakingQueue::query()
            ->whereIn('status', ['waiting', 'searching'])
            ->whereNotNull('expired_at')
            ->where('expired_at', '<=', now())
            ->with('team.owner')
            ->get();

        foreach ($expiredQueues as $queue) {
            $queue->update(['status' => 'expired', 'last_checked_at' => now()]);
            $queue->team?->owner?->notify(new MatchNotification(
                'auto_match_expired',
                'Lawan tidak ditemukan. Silakan cari ulang.',
                $queue->id
            ));
        }

        return $expiredQueues->count();
    }

    public function teamDistanceKm(Team $teamA, Team $teamB): float
    {
        return $this->haversineDistance(
            (float) $teamA->latitude,
            (float) $teamA->longitude,
            (float) $teamB->latitude,
            (float) $teamB->longitude
        );
    }

    public function findNearestAvailableField(Team $teamA, Team $teamB, string $date, string $time, int $durationHours = 2): ?Field
    {
        $midpoint = $this->midpoint($teamA, $teamB);
        $start = Carbon::parse("{$date} {$time}");
        $end = $start->copy()->addHours($durationHours);

        return Field::query()
            ->where('is_available', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->filter(fn (Field $field): bool => $this->isFieldOpen($field, $start, $end) && $this->isFieldAvailable($field, $start, $end))
            ->map(function (Field $field) use ($midpoint) {
                $field->distance_from_midpoint_km = $this->haversineDistance(
                    $midpoint['latitude'],
                    $midpoint['longitude'],
                    (float) $field->latitude,
                    (float) $field->longitude
                );

                return $field;
            })
            ->sortBy('distance_from_midpoint_km')
            ->first();
    }

    public function midpoint(Team $teamA, Team $teamB): array
    {
        return [
            'latitude' => ((float) $teamA->latitude + (float) $teamB->latitude) / 2,
            'longitude' => ((float) $teamA->longitude + (float) $teamB->longitude) / 2,
        ];
    }

    private function isFieldAvailable(Field $field, Carbon $start, Carbon $end): bool
    {
        return ! Booking::query()
            ->where('field_id', $field->id)
            ->where('status', '!=', 'cancelled')
            ->where('start_at', '<', $end)
            ->whereRaw('DATE_ADD(start_at, INTERVAL duration_hours HOUR) > ?', [$start])
            ->exists();
    }

    private function isFieldOpen(Field $field, Carbon $start, Carbon $end): bool
    {
        if (! $field->open_time || ! $field->close_time) {
            return true;
        }

        $open = Carbon::parse($start->toDateString().' '.$field->open_time);
        $close = Carbon::parse($start->toDateString().' '.$field->close_time);

        if ($close->lessThanOrEqualTo($open)) {
            $close->addDay();
        }

        return $start->greaterThanOrEqualTo($open) && $end->lessThanOrEqualTo($close);
    }

    private function createPendingAutoMatch(
        AutoMatchmakingQueue $queue,
        AutoMatchmakingQueue $candidateQueue,
        Field $field,
        string $matchDate,
        string $startTime
    ): FutsalMatch {
        $team = $queue->team;
        $opponent = $candidateQueue->team;
        $durationHours = (int) ceil($queue->duration_minutes / 60);
        $bookingStartTime = Carbon::parse("{$matchDate} {$startTime}");
        $midpoint = $this->midpoint($team, $opponent);

        $match = FutsalMatch::create([
            'match_request_id' => null,
            'venue_id' => null,
            'field_id' => $field->id,
            'team_a_id' => $opponent->id,
            'team_b_id' => $team->id,
            'match_date' => $matchDate,
            'start_time' => $startTime,
            'duration_minutes' => $queue->duration_minutes,
            'status' => 'pending',
        ]);

        $booking = Booking::create([
            'field_id' => $field->id,
            'match_id' => $match->id,
            'start_at' => $bookingStartTime,
            'duration_hours' => $durationHours,
            'status' => 'pending',
        ]);

        $totalCost = $field->price_per_hour * $durationHours;
        $costPerTeam = (int) round($totalCost / 2);

        $matchCost = MatchCost::create([
            'match_id' => $match->id,
            'total_cost' => $totalCost,
            'cost_per_team' => $costPerTeam,
            'dp_per_team' => $costPerTeam,
            'handling_fee' => (int) ceil($costPerTeam * 0.1),
            'cost_per_player' => (int) round($totalCost / max(1, $team->player_count ?: 1)),
            'payment_notes' => "AutoMatching: level {$queue->skill_level}, midpoint {$midpoint['latitude']}, {$midpoint['longitude']}, radius {$queue->radius_km} km, lapangan {$field->name}. Pembayaran wajib lunas 100% dari biaya per tim ditambah biaya admin 10%.",
        ]);

        $amount = (int) ($matchCost->cost_per_team + $matchCost->handling_fee);

        foreach ([$team, $opponent] as $payingTeam) {
            Payment::updateOrCreate([
                'booking_id' => $booking->id,
                'team_id' => $payingTeam->id,
            ], [
                'amount' => $amount,
                'payment_method' => 'bank_transfer',
                'payment_status' => 'pending',
            ]);
        }

        $now = now();
        $queue->update([
            'match_id' => $match->id,
            'match_date' => $matchDate,
            'start_time' => $startTime,
            'status' => 'matched',
            'matched_at' => $now,
            'last_checked_at' => $now,
        ]);
        $candidateQueue->update([
            'match_id' => $match->id,
            'match_date' => $matchDate,
            'start_time' => $startTime,
            'status' => 'matched',
            'matched_at' => $now,
            'last_checked_at' => $now,
        ]);

        $opponent->owner?->notify(new MatchNotification(
            'auto_match_found',
            "AutoMatching menemukan lawan: {$team->name}. Kick-off otomatis {$bookingStartTime->format('H:i')} di {$field->name}.",
            $match->id
        ));

        $team->owner?->notify(new MatchNotification(
            'auto_match_found',
            "AutoMatching menemukan lawan: {$opponent->name}. Kick-off otomatis {$bookingStartTime->format('H:i')} di {$field->name}.",
            $match->id
        ));

        return $match->load(['teamA', 'teamB', 'field', 'matchCost']);
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
