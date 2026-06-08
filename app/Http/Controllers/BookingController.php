<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Models\Field;
use App\Models\FutsalMatch;
use App\Notifications\BookingNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function index()
    {
        $team = Auth::user()->team;

        if (! $team) {
            return response()->json(['message' => 'You must create a team first.'], 422);
        }

        $bookings = Booking::with(['field', 'match'])
            ->whereHas('match', function ($query) use ($team) {
                $query->where('team_a_id', $team->id)
                    ->orWhere('team_b_id', $team->id);
            })
            ->get();

        return response()->json($bookings);
    }

    public function store(BookingRequest $request)
    {
        $team = Auth::user()->team;

        if (! $team) {
            return response()->json(['message' => 'You must create a team first.'], 422);
        }

        $match = FutsalMatch::findOrFail($request->match_id);

        if (! in_array($team->id, [$match->team_a_id, $match->team_b_id], true)) {
            abort(403);
        }

        $field = Field::findOrFail($request->field_id);
        $start = Carbon::parse($request->start_at);
        $duration = $request->duration_hours;

        if (! $this->isFieldAvailable($field, $start, $duration)) {
            return response()->json(['message' => 'Field is not available for the selected slot.'], 422);
        }

        $booking = Booking::create([
            'field_id' => $field->id,
            'match_id' => $match->id,
            'start_at' => $start,
            'duration_hours' => $duration,
            'status' => 'confirmed',
        ]);

        $match->teamA->owner->notify(new BookingNotification(
            'booking_created',
            "Booking created for match {$match->id} at {$field->name}.",
            $booking->id
        ));

        $match->teamB->owner->notify(new BookingNotification(
            'booking_created',
            "Booking created for match {$match->id} at {$field->name}.",
            $booking->id
        ));

        return response()->json(['booking' => $booking], 201);
    }

    public function show(Booking $booking)
    {
        $this->authorizeBooking($booking);

        return response()->json($booking->load(['field', 'match.payment']));
    }

    public function cancel(Booking $booking)
    {
        $this->authorizeBooking($booking);

        $booking->update(['status' => 'cancelled']);

        $booking->match->teamA->owner->notify(new BookingNotification(
            'booking_cancelled',
            "Booking for match {$booking->match->id} has been cancelled.",
            $booking->id
        ));

        $booking->match->teamB->owner->notify(new BookingNotification(
            'booking_cancelled',
            "Booking for match {$booking->match->id} has been cancelled.",
            $booking->id
        ));

        return response()->json(['message' => 'Booking cancelled successfully']);
    }

    protected function authorizeBooking(Booking $booking): void
    {
        $team = Auth::user()->team;

        if (! $team || ! in_array($team->id, [$booking->match->team_a_id, $booking->match->team_b_id], true)) {
            abort(403);
        }
    }

    protected function isFieldAvailable(Field $field, Carbon $start, int $duration): bool
    {
        $end = $start->copy()->addHours($duration);

        $conflict = Booking::query()
            ->where('field_id', $field->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($start, $end) {
                $query->whereBetween('start_at', [$start, $end])
                    ->orWhere(function ($query) use ($start, $end) {
                        $query->where('start_at', '<', $start)
                            ->whereRaw('DATE_ADD(start_at, INTERVAL duration_hours HOUR) > ?', [$start]);
                    });
            })
            ->exists();

        return ! $conflict;
    }
}
