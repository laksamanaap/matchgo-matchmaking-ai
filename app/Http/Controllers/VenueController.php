<?php

namespace App\Http\Controllers;

use App\Models\FutsalMatch;
use App\Models\MatchRequest;
use App\Models\Venue;
use App\Models\VenueSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VenueController extends Controller
{
    public function index(Request $request): View
    {
        $query = Venue::query()
            ->where('is_active', true)
            ->withCount(['venueSchedules as available_slots' => fn ($q) => $q->where('is_booked', false)->where('date', '>=', today())]);

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        if ($request->filled('date')) {
            $query->whereHas('venueSchedules', fn ($q) =>
                $q->where('date', $request->date)->where('is_booked', false)
            );
        }

        $venues  = $query->orderBy('name')->paginate(9)->withQueryString();
        $cities  = Venue::where('is_active', true)->distinct()->pluck('city')->sort()->values();

        // Carry match_id through to venue detail links
        $matchId = $request->integer('match_id') ?: null;

        return view('venue.index', compact('venues', 'cities', 'matchId'));
    }

    public function show(int $id, Request $request): View
    {
        $venue = Venue::where('is_active', true)->findOrFail($id);

        $teamIds = auth()->user()->ownedTeams()->pluck('id');

        // If arriving from pertandingan page with a specific match pre-selected
        $preselectedMatchId = $request->integer('match_id') ?: null;

        // Default to preferred_date of the linked match so the user picks the right day
        $defaultDate = today()->toDateString();
        if ($preselectedMatchId) {
            $linked = MatchRequest::find($preselectedMatchId);
            if ($linked && $linked->preferred_date >= today()->toDateString()) {
                $defaultDate = $linked->preferred_date;
            }
        }
        $date = $request->get('date', $defaultDate);

        $slots = VenueSchedule::where('venue_id', $id)
            ->where('date', $date)
            ->orderBy('start_time')
            ->get();

        // Fixed query — group the OR so status/doesntHave filters apply to both sides
        $myAcceptedMatches = MatchRequest::where(function ($q) use ($teamIds) {
                $q->whereIn('requester_team_id', $teamIds)
                  ->orWhereIn('opponent_team_id', $teamIds);
            })
            ->where('status', 'accepted')
            ->whereDoesntHave('futsalMatch')
            ->with(['requesterTeam', 'opponentTeam'])
            ->get();

        return view('venue.show', compact('venue', 'slots', 'date', 'myAcceptedMatches', 'preselectedMatchId'));
    }

    public function book(int $venueId, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'venue_schedule_id' => ['required', 'exists:venue_schedules,id'],
            'match_request_id'  => ['nullable', 'exists:match_requests,id'],
        ]);

        $slot = VenueSchedule::where('venue_id', $venueId)
            ->where('is_booked', false)
            ->findOrFail($validated['venue_schedule_id']);

        // Verify ownership of the match request if provided
        if (! empty($validated['match_request_id'])) {
            $matchReq = MatchRequest::findOrFail($validated['match_request_id']);

            $teamIds = auth()->user()->ownedTeams()->pluck('id');
            $owns = $teamIds->contains($matchReq->requester_team_id)
                 || $teamIds->contains($matchReq->opponent_team_id);

            if (! $owns) {
                abort(403, 'Kamu tidak terlibat dalam pertandingan ini.');
            }

            if ($matchReq->status !== 'accepted') {
                return back()->with('error', 'Pertandingan ini belum diterima oleh kedua pihak.');
            }

            $slot->update(['is_booked' => true]);

            FutsalMatch::create([
                'match_request_id' => $matchReq->id,
                'venue_id'         => $venueId,
                'team_a_id'        => $matchReq->requester_team_id,
                'team_b_id'        => $matchReq->opponent_team_id,
                'match_date'       => $slot->date,
                'start_time'       => $slot->start_time,
                'duration_minutes' => (int) ((strtotime($slot->end_time) - strtotime($slot->start_time)) / 60),
                'status'           => 'scheduled',
            ]);

            return redirect()->route('match.index')
                ->with('success', 'Lapangan berhasil dipesan! Pertandingan sudah terjadwal.');
        }

        // Booking without linking to a match (just reserve the slot)
        $slot->update(['is_booked' => true]);

        return redirect()->route('venue.index')
            ->with('success', 'Slot lapangan berhasil dipesan.');
    }
}
