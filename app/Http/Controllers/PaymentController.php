<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\Payment;
use App\Models\Booking;
use App\Notifications\PaymentNotification;
use App\Services\PaymentAmountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $team = Auth::user()->team;

        if (! $team) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'You must create a team first.'], 422);
            }

            return redirect()->route('teams.index')->withErrors(['message' => 'Buat tim terlebih dahulu.']);
        }

        $payments = Payment::with('booking.match')
            ->whereHas('booking.match', function ($query) use ($team) {
                $query->where('team_a_id', $team->id)
                    ->orWhere('team_b_id', $team->id);
            })
            ->get();

        if ($request->wantsJson()) {
            return response()->json($payments);
        }

        $matches = $team->matchesAsTeamA()
            ->with(['teamA', 'teamB', 'field', 'booking.payments', 'matchCost'])
            ->get()
            ->merge(
                $team->matchesAsTeamB()
                    ->with(['teamA', 'teamB', 'field', 'booking.payments', 'matchCost'])
                    ->get()
            )
            ->sortByDesc('match_date')
            ->values();

        return view('payments.index', [
            'team' => $team,
            'matches' => $matches,
        ]);
    }

    public function store(Request $request, PaymentAmountService $paymentAmounts)
    {
        $data = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'payment_method' => ['required', 'in:bank_transfer,e-wallet,cash'],
        ]);

        $team = Auth::user()->team;

        if (! $team) {
            abort(403);
        }

        $booking = Booking::with('match.matchCost')->findOrFail($data['booking_id']);

        if (! $this->authorizeBooking($booking)) {
            abort(403);
        }

        $matchCost = $booking->match->matchCost;
        $amount = (float) $paymentAmounts->amountForMatch($booking->match, $matchCost);

        $payment = Payment::updateOrCreate(
            [
                'booking_id' => $booking->id,
                'team_id' => $team->id,
            ],
            [
                'amount' => $amount,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'paid',
            ]
        );

        $booking->match->teamA->owner->notify(new PaymentNotification(
            'payment_created',
            "Payment of {$payment->amount} has been recorded for booking {$booking->id}.",
            $payment->id
        ));

        $booking->match->teamB?->owner?->notify(new PaymentNotification(
            'payment_created',
            "Payment of {$payment->amount} has been recorded for booking {$booking->id}.",
            $payment->id
        ));

        if ($request->wantsJson()) {
            return response()->json(['payment' => $payment], 201);
        }

        return redirect()->route('payments.index')->with('success', 'Pembayaran berhasil dicatat.');
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
