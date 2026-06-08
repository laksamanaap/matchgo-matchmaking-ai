<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\Payment;
use App\Models\Booking;
use App\Notifications\PaymentNotification;
use App\Services\MidtransSnapService;
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
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Pembayaran hanya bisa dicatat melalui Midtrans.'], 422);
        }

        return back()->withErrors(['message' => 'Pembayaran hanya bisa dicatat melalui Midtrans.']);
    }

    public function midtransToken(Request $request, PaymentAmountService $paymentAmounts, MidtransSnapService $midtrans)
    {
        $data = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
        ]);

        $team = Auth::user()->team;

        if (! $team) {
            abort(403);
        }

        $booking = Booking::with(['match.matchCost', 'match.teamA.owner', 'match.teamB.owner', 'field'])->findOrFail($data['booking_id']);

        if (! $this->authorizeBooking($booking)) {
            abort(403);
        }

        if ($booking->payments()->where('team_id', $team->id)->where('payment_status', 'paid')->exists()) {
            return response()->json(['message' => 'Pembayaran tim kamu sudah tercatat.'], 422);
        }

        $amount = (int) $paymentAmounts->amountForMatch($booking->match, $booking->match->matchCost);

        if ($amount <= 0) {
            return response()->json(['message' => 'Nominal pembayaran belum tersedia.'], 422);
        }

        $basePayment = $booking->match->isAutoMatch()
            ? (int) ($booking->match->matchCost?->cost_per_team ?? 0)
            : (int) ($booking->match->matchCost?->dp_per_team ?? $booking->match->matchCost?->cost_per_team ?? 0);
        $handlingFee = (int) ($booking->match->matchCost?->handling_fee ?? ceil($basePayment * 0.1));
        $orderId = 'MG-PAY-' . $booking->id . '-' . $team->id . '-' . now()->format('YmdHisv');

        try {
            $snap = $midtrans->createToken($orderId, $amount, [
                'first_name' => $team->owner?->name ?? $team->name,
                'email' => $team->owner?->email,
            ], [
                [
                    'id' => 'matchgo-payment-' . $booking->id,
                    'price' => $basePayment,
                    'quantity' => 1,
                    'name' => $booking->match->isAutoMatch() ? 'Pelunasan tim ' . $team->name : 'DP 50% tim ' . $team->name,
                ],
                [
                    'id' => 'matchgo-web-fee-' . $booking->id,
                    'price' => $handlingFee,
                    'quantity' => 1,
                    'name' => 'Biaya web 10% dari DP',
                ],
            ]);
        } catch (\RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        session()->put('payment_midtrans_order_' . $booking->id . '_' . $team->id, $orderId);

        return response()->json([
            'token' => $snap['token'] ?? null,
            'order_id' => $orderId,
        ]);
    }

    public function midtransFinish(Request $request, PaymentAmountService $paymentAmounts)
    {
        $data = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'midtrans_order_id' => ['required', 'string'],
            'midtrans_transaction_status' => ['required', 'in:settlement,capture'],
            'midtrans_payment_type' => ['nullable', 'string', 'max:50'],
            'midtrans_transaction_id' => ['nullable', 'string', 'max:100'],
        ]);

        $team = Auth::user()->team;

        if (! $team) {
            abort(403);
        }

        $booking = Booking::with(['match.teamA.owner', 'match.teamB.owner', 'match.matchCost', 'payments'])->findOrFail($data['booking_id']);

        if (! $this->authorizeBooking($booking)) {
            abort(403);
        }

        $sessionKey = 'payment_midtrans_order_' . $booking->id . '_' . $team->id;

        if (session($sessionKey) !== $data['midtrans_order_id']) {
            return redirect()
                ->route('matches.show', $booking->match)
                ->withErrors(['message' => 'Transaksi Midtrans tidak cocok. Silakan ulangi pembayaran.']);
        }

        $amount = (int) $paymentAmounts->amountForMatch($booking->match, $booking->match->matchCost);

        $payment = Payment::updateOrCreate(
            [
                'booking_id' => $booking->id,
                'team_id' => $team->id,
            ],
            [
                'amount' => $amount,
                'payment_method' => $data['midtrans_payment_type'] ?? 'midtrans',
                'payment_status' => 'paid',
                'midtrans_order_id' => $data['midtrans_order_id'],
                'midtrans_transaction_id' => $data['midtrans_transaction_id'] ?? null,
                'refund_reference' => null,
                'refund_note' => null,
                'refunded_at' => null,
            ]
        );

        $paidTeamIds = $booking->payments()
            ->where('payment_status', 'paid')
            ->pluck('team_id')
            ->push($team->id)
            ->unique()
            ->values();

        $requiredTeamIds = collect([$booking->match->team_a_id, $booking->match->team_b_id])
            ->filter()
            ->values();

        if (
            $requiredTeamIds->isNotEmpty()
            && $requiredTeamIds->diff($paidTeamIds)->isEmpty()
            && (
                $booking->match->status === 'scheduled'
                || ($booking->match->isAutoMatch() && $booking->match->status === 'pending')
            )
        ) {
            $booking->match->update(['status' => 'confirmed']);
            $booking->update(['status' => 'confirmed']);
        }

        session()->forget($sessionKey);

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

        return redirect()->route('matches.show', $booking->match)->with('success', 'Pembayaran Midtrans berhasil. DP tim kamu sudah tercatat.');
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
