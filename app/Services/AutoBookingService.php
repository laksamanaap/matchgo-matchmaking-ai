<?php

namespace App\Services;

use App\Models\FutsalMatch;
use App\Models\MatchRequest;
use App\Models\Team;
use App\Models\VenueSchedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AutoBookingService
{
    /**
     * Minimum delay before the auto-booked match starts.
     */
    public const MIN_LEAD_HOURS = 2;

    public function __construct(private CompatibilityEngine $compat) {}

    /**
     * Auto-book a venue + 2-hour slot for a freshly accepted matchmaking pair.
     *
     * Strategy:
     *   1. Target start = now() + 2 hours.
     *   2. Pick the soonest unbooked 2h slot at or after target.
     *   3. Among slots tied on date/time, prefer the venue closest to the
     *      midpoint between the two teams.
     *   4. Mark the slot booked and create a FutsalMatch (status=scheduled).
     *
     * Returns the created FutsalMatch, or null if no slot is available
     * anywhere in the next 4 weeks (caller can fall back to manual booking).
     */
    public function autoBook(MatchRequest $matchRequest, Team $teamA, Team $teamB): ?FutsalMatch
    {
        $target  = now()->addHours(self::MIN_LEAD_HOURS);
        $midLat  = ((float) $teamA->latitude  + (float) $teamB->latitude)  / 2;
        $midLon  = ((float) $teamA->longitude + (float) $teamB->longitude) / 2;

        return DB::transaction(function () use ($matchRequest, $teamA, $teamB, $target, $midLat, $midLon) {
            $slot = $this->findBestSlot($target, $midLat, $midLon);

            if (! $slot) {
                return null;
            }

            $slot->update(['is_booked' => true]);

            $start = Carbon::parse($slot->start_time);
            $end   = Carbon::parse($slot->end_time);
            $duration = max(60, (int) $start->diffInMinutes($end));

            $futsalMatch = FutsalMatch::create([
                'match_request_id' => $matchRequest->id,
                'venue_id'         => $slot->venue_id,
                'team_a_id'        => $teamA->id,
                'team_b_id'        => $teamB->id,
                'match_date'       => $slot->date,
                'start_time'       => $slot->start_time,
                'duration_minutes' => $duration,
                'status'           => 'scheduled',
            ]);

            $matchRequest->update([
                'preferred_date' => $slot->date,
            ]);

            return $futsalMatch->fresh(['venue', 'teamA', 'teamB']);
        });
    }

    /**
     * Locate the soonest available unbooked slot >= $target, preferring the
     * venue closest to the team midpoint when multiple slots tie on time.
     */
    private function findBestSlot(Carbon $target, float $midLat, float $midLon): ?VenueSchedule
    {
        $targetDate = $target->toDateString();
        $targetTime = $target->format('H:i:s');

        // Eager-load with venue so we can rank by distance without N+1.
        $candidates = VenueSchedule::with('venue')
            ->where('is_booked', false)
            ->whereHas('venue', fn ($q) => $q->where('is_active', true))
            ->where(function ($q) use ($targetDate, $targetTime) {
                $q->where('date', '>', $targetDate)
                  ->orWhere(function ($q2) use ($targetDate, $targetTime) {
                      $q2->where('date', $targetDate)
                         ->where('start_time', '>=', $targetTime);
                  });
            })
            ->orderBy('date')
            ->orderBy('start_time')
            ->limit(50)
            ->get();

        if ($candidates->isEmpty()) {
            return null;
        }

        // Group by (date, start_time) — keep the earliest, then pick closest venue.
        $earliest = $candidates->first();
        $earliestKey = $earliest->date . ' ' . $earliest->start_time;

        $tiedAtEarliest = $candidates->filter(
            fn ($s) => ($s->date . ' ' . $s->start_time) === $earliestKey
        );

        if ($tiedAtEarliest->count() === 1) {
            return $earliest;
        }

        return $tiedAtEarliest
            ->sortBy(fn ($s) => $this->compat->haversine(
                $midLat, $midLon,
                (float) $s->venue->latitude, (float) $s->venue->longitude,
            ))
            ->first();
    }
}
