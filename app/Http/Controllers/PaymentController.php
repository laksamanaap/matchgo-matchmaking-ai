<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\Payment;
use App\Models\Booking;
use App\Notifications\PaymentNotification;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index()
    {
        $team = Auth::user()->team;

        if (! $team) {
            return response()->json(['message' => 'You must create a team first.'], 422);
        }

        $payments = Payment::with('booking.match')
            ->whereHas('booking.match', function ($query) use ($team) {
                $query->where('team_a_id', $team->id)
                    ->orWhere('team_b_id', $team->id);
            })
            ->get();

        return response()->json($payments);
    }

    public function store(PaymentRequest $request)
    {
        $booking = Booking::findOrFail($request->booking_id);

        if (! $this->authorizeBooking($booking)) {
            abort(403);
        }

        $payment = Payment::create($request->validated());

        $booking->match->teamA->owner->notify(new PaymentNotification(
            'payment_created',
            "Payment of {$payment->amount} has been recorded for booking {$booking->id}.",
            $payment->id
        ));

        $booking->match->teamB->owner->notify(new PaymentNotification(
            'payment_created',
            "Payment of {$payment->amount} has been recorded for booking {$booking->id}.",
            $payment->id
        ));

        return response()->json(['payment' => $payment], 201);
    }

    public function show(Payment $payment)
    {
        if (! $this->authorizeBooking($payment->booking)) {
            abort(403);
        }

        return response()->json($payment);
    }

    public function update(PaymentRequest $request, Payment $payment)
    {
        if (! $this->authorizeBooking($payment->booking)) {
            abort(403);
        }

        $payment->update($request->validated());

        return response()->json(['payment' => $payment]);
    }

    protected function authorizeBooking(Booking $booking): bool
    {
        $team = Auth::user()->team;

        return $team && in_array($team->id, [$booking->match->team_a_id, $booking->match->team_b_id], true);
    }
}
