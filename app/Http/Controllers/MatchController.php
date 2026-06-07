<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\AutoMatchmakingQueue;
use App\Models\Field;
use App\Models\FutsalMatch;
use App\Models\MatchCost;
use App\Models\MatchRequest;
use App\Models\Payment;
use App\Models\Team;
use App\Models\Venue;
use App\Models\VenueSchedule;
use App\Notifications\MatchNotification;
use App\Services\MatchmakingService;
use App\Services\MidtransSnapService;
use App\Services\PaymentAmountService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MatchController extends Controller
{
    public function index(Request $request, MatchmakingService $service)
    {
        $team = Auth::user()->team;

        if (! $team) {
            return redirect()->route('teams.index')->withErrors(['message' => 'Buat tim terlebih dahulu.']);
        }

        $canUseMatchFeatures = $team->hasMinimumPlayers();
        $now = now();
        $upcomingOnly = function ($query) use ($now) {
            $query->where(function ($query) use ($now) {
                $query->whereDate('match_date', '>', $now->toDateString())
                    ->orWhere(function ($query) use ($now) {
                        $query->whereDate('match_date', $now->toDateString())
                            ->whereTime('start_time', '>=', $now->format('H:i:s'));
                    });
            });
        };

        $myChallenges = FutsalMatch::query()
            ->where('team_a_id', $team->id)
            ->whereNull('team_b_id')
            ->where('status', 'scheduled')
            ->where($upcomingOnly)
            ->with(['field', 'matchCost'])
            ->orderBy('match_date')
            ->orderBy('start_time')
            ->get();

        $openChallenges = FutsalMatch::query()
            ->whereNull('team_b_id')
            ->where('team_a_id', '!=', $team->id)
            ->where('status', 'scheduled')
            ->where($upcomingOnly)
            ->whereHas('teamA', fn ($query) => $query
                ->where('verification_status', 'verified')
                ->has('players', '>=', 4))
            ->with(['teamA', 'field', 'booking', 'matchCost'])
            ->orderBy('match_date')
            ->orderBy('start_time')
            ->get();

        $myMatches = FutsalMatch::query()
            ->where(fn ($query) => $query
                ->where('team_a_id', $team->id)
                ->orWhere('team_b_id', $team->id))
            ->whereNotNull('team_b_id')
            ->whereIn('status', ['pending', 'scheduled', 'confirmed', 'ongoing'])
            ->where($upcomingOnly)
            ->with(['teamA', 'teamB', 'field', 'matchCost'])
            ->orderBy('match_date')
            ->orderBy('start_time')
            ->limit(6)
            ->get();

        return view('matches.index', [
            'team' => $team,
            'canUseMatchFeatures' => $canUseMatchFeatures,
            'myMatches' => $myMatches,
        ]);
    }

    public function take()
    {
        $team = Auth::user()->team;

        if (! $team) {
            return redirect()->route('teams.index')->withErrors(['message' => 'Buat tim terlebih dahulu.']);
        }

        $canUseMatchFeatures = $team->hasMinimumPlayers();
        $now = now();
        $upcomingOnly = function ($query) use ($now) {
            $query->where(function ($query) use ($now) {
                $query->whereDate('match_date', '>', $now->toDateString())
                    ->orWhere(function ($query) use ($now) {
                        $query->whereDate('match_date', $now->toDateString())
                            ->whereTime('start_time', '>=', $now->format('H:i:s'));
                    });
            });
        };

        $openChallenges = FutsalMatch::query()
            ->whereNull('team_b_id')
            ->where('team_a_id', '!=', $team->id)
            ->where('status', 'scheduled')
            ->where($upcomingOnly)
            ->whereHas('teamA', fn ($query) => $query
                ->where('verification_status', 'verified')
                ->has('players', '>=', 4))
            ->with(['teamA', 'field', 'booking', 'matchCost'])
            ->orderBy('match_date')
            ->orderBy('start_time')
            ->get();

        return view('matches.take', [
            'team' => $team,
            'openChallenges' => $openChallenges,
            'canUseMatchFeatures' => $canUseMatchFeatures,
        ]);
    }

    public function history()
    {
        $team = Auth::user()->team;

        if (! $team) {
            return redirect()->route('teams.index')->withErrors(['message' => 'Buat tim terlebih dahulu.']);
        }

        $matches = FutsalMatch::query()
            ->where(fn ($query) => $query
                ->where('team_a_id', $team->id)
                ->orWhere('team_b_id', $team->id))
            ->with(['teamA', 'teamB', 'field', 'booking', 'matchCost'])
            ->latest('match_date')
            ->latest('start_time')
            ->get();

        $completedCount = $matches->where('status', 'completed')->count();
        $cancelledCount = $matches->where('status', 'cancelled')->count();
        $upcomingCount = $matches->whereNotIn('status', ['completed', 'cancelled'])->count();

        return view('matches.history', [
            'team' => $team,
            'matches' => $matches,
            'completedCount' => $completedCount,
            'cancelledCount' => $cancelledCount,
            'upcomingCount' => $upcomingCount,
        ]);
    }

    public function auto(Request $request, MatchmakingService $service)
    {
        $team = Auth::user()->team;

        if (! $team) {
            return redirect()->route('teams.index')->withErrors(['message' => 'Buat tim terlebih dahulu.']);
        }

        $canUseMatchFeatures = $team->hasMinimumPlayers();
        $service->expireStaleQueues();
        $autoAvailability = $service->autoMatchAvailability();

        $autoParams = [
            'duration_minutes' => (int) $request->input('duration_minutes', 60),
            'radius_km' => (int) $request->input('radius_km', 10),
            'match_time' => $autoAvailability['match_time'],
        ];

        $latestMatchedQueue = AutoMatchmakingQueue::query()
            ->where('team_id', $team->id)
            ->where('status', 'matched')
            ->whereNotNull('match_id')
            ->whereHas('match', fn ($query) => $query->where('status', 'pending'))
            ->with('match.teamA', 'match.teamB', 'match.field', 'match.matchCost')
            ->latest('matched_at')
            ->first();

        if ($latestMatchedQueue?->match) {
            AutoMatchmakingQueue::query()
                ->where('team_id', $team->id)
                ->whereIn('status', ['waiting', 'searching'])
                ->update(['status' => 'cancelled']);
        }

        $waitingQueue = $latestMatchedQueue?->match
            ? null
            : AutoMatchmakingQueue::query()
                ->where('team_id', $team->id)
                ->whereIn('status', ['waiting', 'searching'])
                ->latest()
                ->first();

        return view('matches.auto', [
            'team' => $team,
            'autoParams' => $autoParams,
            'waitingQueue' => $waitingQueue,
            'latestMatchedQueue' => $latestMatchedQueue,
            'canUseMatchFeatures' => $canUseMatchFeatures,
            'autoAvailability' => $autoAvailability,
        ]);
    }

    public function create(MatchmakingService $service)
    {
        $team = Auth::user()->team;

        if (! $team) {
            return redirect()->route('teams.index')->withErrors(['message' => 'Buat tim terlebih dahulu.']);
        }

        if ($team->owner_id !== Auth::id()) {
            abort(403);
        }

        if (! $team->hasMinimumPlayers()) {
            return redirect()->route('teams.index')
                ->withErrors(['message' => 'Minimal 5 pemain diperlukan untuk memakai fitur Buat Pertandingan.']);
        }

        if (! $team->isVerified()) {
            return redirect()->route('matches.take')
                ->withErrors(['message' => 'Tim kamu belum diverifikasi admin, jadi belum bisa membuat tantangan.']);
        }

        $teamLat = $team->latitude;
        $teamLng = $team->longitude;
        $radiusKm = 50; // hanya tampilkan lapangan dalam radius ini dari lokasi tim
        $fields = Field::where('is_available', true)->get()
            ->map(function ($field) use ($teamLat, $teamLng) {
                $field->distance_km = ($teamLat && $teamLng && $field->latitude && $field->longitude)
                    ? $this->haversineKm((float) $teamLat, (float) $teamLng, (float) $field->latitude, (float) $field->longitude)
                    : null;

                return $field;
            })
            ->sortBy(fn ($field) => $field->distance_km ?? INF)
            ->values();

        // Saring ke lapangan terdekat saja kalau tim punya koordinat.
        // Fallback ke semua lapangan kalau tidak ada yang masuk radius (biar picker tidak kosong).
        if ($teamLat && $teamLng) {
            $nearby = $fields
                ->filter(fn ($field) => $field->distance_km !== null && $field->distance_km <= $radiusKm)
                ->values();
            $fields = $nearby->isNotEmpty() ? $nearby : $fields;
        }

        $now = now();
        $upcomingOnly = function ($query) use ($now) {
            $query->where(function ($query) use ($now) {
                $query->whereDate('match_date', '>', $now->toDateString())
                    ->orWhere(function ($query) use ($now) {
                        $query->whereDate('match_date', $now->toDateString())
                            ->whereTime('start_time', '>=', $now->format('H:i:s'));
                    });
            });
        };
        $myChallenges = FutsalMatch::query()
            ->where('team_a_id', $team->id)
            ->whereNull('team_b_id')
            ->where('status', 'scheduled')
            ->where($upcomingOnly)
            ->with(['field', 'matchCost'])
            ->orderBy('match_date')
            ->orderBy('start_time')
            ->get();

        $defaultMatchTime = $service->defaultMatchTime();
        $pendingChallenge = session('pending_challenge');

        if ($pendingChallenge && ($pendingChallenge['team_id'] ?? null) !== $team->id) {
            session()->forget('pending_challenge');
            $pendingChallenge = null;
        }

        $pendingField = $pendingChallenge ? Field::find($pendingChallenge['field_id'] ?? null) : null;

        if ($pendingChallenge && ! $pendingField) {
            session()->forget('pending_challenge');
            $pendingChallenge = null;
        }

        $pendingPayment = null;

        if ($pendingChallenge && $pendingField) {
            $durationHours = (int) ceil(($pendingChallenge['duration_minutes'] ?? 60) / 60);
            $totalCost = $pendingField->price_per_hour * $durationHours;
            $costPerTeam = (int) round($totalCost / 2);
            $dpPerTeam = $costPerTeam;
            $handlingFee = (int) ceil($dpPerTeam * 0.1);

            $pendingPayment = [
                'field' => $pendingField,
                'match_date' => $pendingChallenge['match_date'],
                'start_time' => $pendingChallenge['start_time'],
                'duration_minutes' => $pendingChallenge['duration_minutes'],
                'total_cost' => $totalCost,
                'cost_per_team' => $costPerTeam,
                'dp_per_team' => $dpPerTeam,
                'handling_fee' => $handlingFee,
                'pay_now' => $dpPerTeam + $handlingFee,
            ];
        }

        $scheduleOptions = $this->availableScheduleOptions($fields);

        return view('matches.create', [
            'team' => $team,
            'fields' => $fields,
            'myChallenges' => $myChallenges,
            'defaultMatchTime' => $defaultMatchTime,
            'pendingPayment' => $pendingPayment,
            'scheduleOptions' => $scheduleOptions,
        ]);
    }

    protected function availableScheduleOptions($fields): array
    {
        $fields = collect($fields);
        $venuesByName = Venue::query()
            ->whereIn('name', $fields->pluck('name')->filter()->unique()->values())
            ->get()
            ->keyBy('name');

        $fieldByVenueId = $fields
            ->mapWithKeys(function ($field) use ($venuesByName) {
                $venue = $venuesByName->get($field->name);

                return $venue ? [$venue->id => $field] : [];
            });

        if ($fieldByVenueId->isEmpty()) {
            return [];
        }

        $schedules = VenueSchedule::query()
            ->whereIn('venue_id', $fieldByVenueId->keys())
            ->whereDate('date', '>=', now()->toDateString())
            ->where('is_booked', false)
            ->orderBy('date')
            ->orderBy('start_time')
            ->get();

        $options = [];

        foreach ($schedules as $schedule) {
            $field = $fieldByVenueId->get($schedule->venue_id);

            if (! $field) {
                continue;
            }

            $date = $schedule->date->toDateString();
            $start = Carbon::parse($date . ' ' . $schedule->start_time);
            $end = Carbon::parse($date . ' ' . $schedule->end_time);

            if ($end->lessThanOrEqualTo($start)) {
                $end->addDay();
            }

            if ($start->lt(now())) {
                continue;
            }

            $durationMinutes = $start->diffInMinutes($end);
            $startTime = $start->format('H:i');

            foreach ([60, 120] as $duration) {
                if ($durationMinutes < $duration) {
                    continue;
                }

                $slotEnd = $start->copy()->addMinutes($duration);

                if (! $this->isFieldSlotFree($field, $start, $slotEnd)) {
                    continue;
                }

                $options[$field->id][$date][$duration][] = [
                    'value' => $startTime,
                    'label' => $start->format('H:i') . ' - ' . $slotEnd->format('H:i'),
                    'schedule_id' => $schedule->id,
                ];
            }
        }

        return $options;
    }

    protected function availableVenueScheduleFor(Field $field, string $matchDate, string $startTime, int $durationMinutes): ?VenueSchedule
    {
        $venue = Venue::query()->where('name', $field->name)->first();

        if (! $venue) {
            return null;
        }

        $bookingStart = Carbon::parse("$matchDate $startTime");
        $bookingEnd = $bookingStart->copy()->addMinutes($durationMinutes);

        if ($bookingStart->lt(now()) || ! $this->isFieldSlotFree($field, $bookingStart, $bookingEnd)) {
            return null;
        }

        return VenueSchedule::query()
            ->where('venue_id', $venue->id)
            ->whereDate('date', $matchDate)
            ->where('is_booked', false)
            ->get()
            ->first(function (VenueSchedule $schedule) use ($matchDate, $bookingStart, $bookingEnd) {
                $scheduleStart = Carbon::parse($matchDate . ' ' . $schedule->start_time);
                $scheduleEnd = Carbon::parse($matchDate . ' ' . $schedule->end_time);

                if ($scheduleEnd->lessThanOrEqualTo($scheduleStart)) {
                    $scheduleEnd->addDay();
                }

                return $bookingStart->equalTo($scheduleStart)
                    && $bookingEnd->lessThanOrEqualTo($scheduleEnd);
            });
    }

    protected function isFieldSlotFree(Field $field, Carbon $start, Carbon $end): bool
    {
        return ! Booking::query()
            ->where('field_id', $field->id)
            ->where('status', '!=', 'cancelled')
            ->where('start_at', '<', $end)
            ->whereRaw('DATE_ADD(start_at, INTERVAL duration_hours HOUR) > ?', [$start])
            ->exists();
    }

    protected function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * asin(min(1, sqrt($a)));
    }

    public function show(FutsalMatch $match)
    {
        $this->authorizeMatchView($match);

        return view('matches.show', [
            'match' => $match->load([
                'teamA.players',
                'teamB.players',
                'field',
                'booking.field',
                'booking.payments',
                'matchCost',
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'field_id' => ['required', 'exists:fields,id'],
            'match_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required'],
            'duration_minutes' => ['required', 'integer', 'in:60,120'],
        ]);

        $team = Auth::user()->team;

        if (! $team) {
            return redirect()->route('teams.index')->withErrors(['message' => 'Buat tim terlebih dahulu.']);
        }

        if ($team->owner_id !== Auth::id()) {
            abort(403);
        }

        if (! $team->isVerified()) {
            return back()
                ->withErrors(['message' => 'Tim kamu belum diverifikasi admin, jadi belum bisa membuat tantangan.'])
                ->withInput();
        }

        if (! $team->hasMinimumPlayers()) {
            return back()
                ->withErrors(['message' => 'Minimal 5 pemain diperlukan untuk membuat pertandingan.'])
                ->withInput();
        }

        $field = Field::findOrFail($request->field_id);
        $matchDate = $request->match_date;
        $startTime = $request->start_time;
        $durationMinutes = (int) $request->duration_minutes;

        // Cek ketersediaan lapangan
        $bookingStartTime = Carbon::parse("$matchDate $startTime");

        if ($bookingStartTime->lt(now())) {
            return back()
                ->withErrors(['message' => 'Jam pertandingan sudah lewat. Pilih tanggal dan jam yang masih akan datang.'])
                ->withInput();
        }

        $availableSchedule = $this->availableVenueScheduleFor($field, $matchDate, $startTime, $durationMinutes);

        if (! $availableSchedule) {
            return back()
                ->withErrors(['start_time' => 'Jadwal lapangan tidak tersedia pada tanggal, jam, dan durasi tersebut.'])
                ->withInput();
        }

        session()->put('pending_challenge', [
            'team_id' => $team->id,
            'field_id' => $field->id,
            'match_date' => $matchDate,
            'start_time' => $startTime,
            'duration_minutes' => $durationMinutes,
        ]);

        return redirect()
            ->route('matches.create')
            ->withInput()
            ->with('payment_required', 'Slot lapangan tersedia. Silakan bayar DP 50% + biaya web 10% untuk mencatat pertandingan dan mengunci booking.');
    }

    public function midtransToken(MidtransSnapService $midtrans)
    {
        $team = Auth::user()->team;
        $pendingChallenge = session('pending_challenge');

        if (! $team || ! $pendingChallenge || ($pendingChallenge['team_id'] ?? null) !== $team->id) {
            return response()->json(['message' => 'Data pertandingan belum tersedia.'], 422);
        }

        if ($team->owner_id !== Auth::id()) {
            abort(403);
        }

        if (! $team->isVerified() || ! $team->hasMinimumPlayers()) {
            return response()->json(['message' => 'Tim harus terverifikasi dan memiliki minimal 5 pemain.'], 422);
        }

        $field = Field::findOrFail($pendingChallenge['field_id']);
        $matchDate = $pendingChallenge['match_date'];
        $startTime = $pendingChallenge['start_time'];
        $durationMinutes = (int) $pendingChallenge['duration_minutes'];
        $bookingStartTime = Carbon::parse("$matchDate $startTime");

        if ($bookingStartTime->lt(now())) {
            session()->forget('pending_challenge');

            return response()->json(['message' => 'Jam pertandingan sudah lewat. Pilih tanggal dan jam lain.'], 422);
        }

        if (! $this->availableVenueScheduleFor($field, $matchDate, $startTime, $durationMinutes)) {
            session()->forget('pending_challenge');

            return response()->json(['message' => 'Lapangan sudah tidak tersedia pada slot tersebut. Pilih jadwal lain.'], 422);
        }

        $durationHours = (int) ceil($durationMinutes / 60);
        $totalCost = $field->price_per_hour * $durationHours;
        $dp = (int) round($totalCost / 2);
        $fee = (int) ceil($dp * 0.1);
        $amount = $dp + $fee;
        $orderId = 'MG-DP-' . $team->id . '-' . now()->format('YmdHisv');

        try {
            $snap = $midtrans->createToken($orderId, $amount, [
                'first_name' => $team->owner?->name ?? $team->name,
                'email' => $team->owner?->email,
            ], [
                [
                    'id' => 'matchgo-dp-' . $field->id,
                    'price' => $dp,
                    'quantity' => 1,
                    'name' => 'DP 50% ' . $field->name,
                ],
                [
                    'id' => 'matchgo-web-fee',
                    'price' => $fee,
                    'quantity' => 1,
                    'name' => 'Biaya web 10% dari DP',
                ],
            ]);
        } catch (\RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        session()->put('pending_challenge_midtrans_order_id', $orderId);

        return response()->json([
            'token' => $snap['token'] ?? null,
            'order_id' => $orderId,
        ]);
    }

    public function payAndCreate(Request $request, PaymentAmountService $paymentAmounts)
    {
        $data = $request->validate([
            'midtrans_order_id' => ['required', 'string'],
            'midtrans_transaction_status' => ['required', 'in:settlement,capture'],
            'midtrans_payment_type' => ['nullable', 'string', 'max:50'],
            'midtrans_transaction_id' => ['nullable', 'string', 'max:100'],
        ]);

        $team = Auth::user()->team;
        $pendingChallenge = session('pending_challenge');

        if (! $team || ! $pendingChallenge || ($pendingChallenge['team_id'] ?? null) !== $team->id) {
            return redirect()
                ->route('matches.create')
                ->withErrors(['message' => 'Data pertandingan belum tersedia. Isi detail pertandingan terlebih dahulu.']);
        }

        if (session('pending_challenge_midtrans_order_id') !== $data['midtrans_order_id']) {
            return redirect()
                ->route('matches.create')
                ->withErrors(['message' => 'Transaksi Midtrans tidak cocok. Silakan ulangi pembayaran.']);
        }

        if ($team->owner_id !== Auth::id()) {
            abort(403);
        }

        if (! $team->isVerified() || ! $team->hasMinimumPlayers()) {
            return redirect()
                ->route('matches.create')
                ->withErrors(['message' => 'Tim harus terverifikasi dan memiliki minimal 5 pemain untuk membuat pertandingan.']);
        }

        $field = Field::findOrFail($pendingChallenge['field_id']);
        $matchDate = $pendingChallenge['match_date'];
        $startTime = $pendingChallenge['start_time'];
        $durationMinutes = (int) $pendingChallenge['duration_minutes'];
        $durationHours = (int) ceil($durationMinutes / 60);
        $bookingStartTime = Carbon::parse("$matchDate $startTime");

        if ($bookingStartTime->lt(now())) {
            session()->forget('pending_challenge');

            return redirect()
                ->route('matches.create')
                ->withErrors(['message' => 'Jam pertandingan sudah lewat. Pilih tanggal dan jam yang masih akan datang.']);
        }

        $availableSchedule = $this->availableVenueScheduleFor($field, $matchDate, $startTime, $durationMinutes);

        if (! $availableSchedule) {
            session()->forget('pending_challenge');

            return redirect()
                ->route('matches.create')
                ->withErrors(['message' => 'Lapangan sudah tidak tersedia pada jam tersebut. Pilih slot lain.']);
        }

        $match = DB::transaction(function () use ($team, $field, $matchDate, $startTime, $durationMinutes, $bookingStartTime, $durationHours, $paymentAmounts, $availableSchedule, $data) {
            $match = FutsalMatch::create([
                'match_request_id' => null,
                'venue_id' => null,
                'field_id' => $field->id,
                'team_a_id' => $team->id,
                'team_b_id' => null,
                'match_date' => $matchDate,
                'start_time' => $startTime,
                'duration_minutes' => $durationMinutes,
                'status' => 'scheduled',
            ]);

            $booking = Booking::create([
                'field_id' => $field->id,
                'match_id' => $match->id,
                'start_at' => $bookingStartTime,
                'duration_hours' => $durationHours,
                'status' => 'confirmed',
            ]);

            $totalCost = $field->price_per_hour * $durationHours;

            $matchCost = MatchCost::create([
                'match_id' => $match->id,
                'total_cost' => $totalCost,
                'cost_per_team' => (int) round($totalCost / 2),
                'dp_per_team' => (int) round($totalCost / 2),
                'handling_fee' => (int) ceil(round($totalCost / 2) * 0.1),
                'cost_per_player' => (int) round($totalCost / max(1, $team->player_count ?: 1)),
                'payment_notes' => "Biaya lapangan {$field->name} dibagi 2 tim. Kapten pembuat wajib membayar DP 50% dari harga lapangan ditambah biaya pengelola web 10% dari DP. Refund maksimal 6 jam setelah pertandingan dibuat.",
            ]);

            Payment::updateOrCreate([
                'booking_id' => $booking->id,
                'team_id' => $team->id,
            ], [
                'amount' => $paymentAmounts->regularMatchAmount($matchCost),
                'payment_method' => $data['midtrans_payment_type'] ?? 'midtrans',
                'payment_status' => 'paid',
                'midtrans_order_id' => $data['midtrans_order_id'],
                'midtrans_transaction_id' => $data['midtrans_transaction_id'] ?? null,
                'refund_reference' => null,
                'refund_note' => null,
                'refunded_at' => null,
            ]);

            $availableSchedule->update(['is_booked' => true]);

            return $match;
        });

        session()->forget('pending_challenge');
        session()->forget('pending_challenge_midtrans_order_id');

        $team->owner->notify(new MatchNotification(
            'challenge_created',
            "Tantangan pertandingan dibuat pada {$match->match_date->format('d M Y')} di {$field->name}.",
            $match->id
        ));

        return redirect()->route('matches.show', $match)->with('success', 'DP berhasil dibayar. Pertandingan berhasil dibuat, lapangan terbooking, dan tantangan tampil sebagai tantangan terbuka.');
    }

    public function accept(FutsalMatch $match)
    {
        $team = Auth::user()->team;

        if (! $team) {
            return redirect()->route('teams.index')->withErrors(['message' => 'Buat tim terlebih dahulu.']);
        }

        if ($team->owner_id !== Auth::id()) {
            abort(403);
        }

        if (! $team->isVerified()) {
            return back()->withErrors(['message' => 'Tim kamu belum diverifikasi admin, jadi belum bisa mengambil tantangan.']);
        }

        if (! $team->hasMinimumPlayers()) {
            return back()->withErrors(['message' => 'Minimal 5 pemain diperlukan untuk mencari pertandingan.']);
        }

        if (! $match->teamA?->isVerified()) {
            return back()->withErrors(['message' => 'Tantangan ini tidak tersedia karena tim pembuat belum terverifikasi.']);
        }

        if (! $match->teamA?->hasMinimumPlayers()) {
            return back()->withErrors(['message' => 'Tantangan ini tidak tersedia karena tim pembuat belum memiliki minimal 5 pemain.']);
        }

        if ($match->team_a_id === $team->id) {
            return back()->withErrors(['message' => 'Tim pembuat tidak bisa mengambil tantangan sendiri.']);
        }

        if ($match->team_b_id !== null || $match->status !== 'scheduled') {
            return back()->withErrors(['message' => 'Tantangan ini sudah tidak tersedia.']);
        }

        if (Carbon::parse($match->match_date->toDateString() . ' ' . $match->start_time)->lt(now())) {
            return back()->withErrors(['message' => 'Tantangan ini sudah lewat dan tidak bisa diambil.']);
        }

        $match->update(['team_b_id' => $team->id]);
        $match->loadMissing(['teamA.owner', 'matchCost']);

        $match->teamA?->owner?->notify(new MatchNotification(
            'challenge_accepted',
            "{$team->name} menerima tantangan pertandingan kamu. Buka detail match untuk melihat jadwal dan lapangan.",
            $match->id
        ));

        $team->owner?->notify(new MatchNotification(
            'challenge_accepted',
            "Kamu berhasil menerima tantangan dari {$match->teamA->name}. Biaya per tim: Rp " . number_format($match->matchCost?->cost_per_team ?? 0),
            $match->id
        ));

        return redirect()->route('matches.show', $match)->with('success', 'Tantangan berhasil diambil. Silakan bayar DP tim kamu lewat Midtrans.');
    }

    public function autoStore(Request $request, MatchmakingService $service)
    {
        $request->validate([
            'duration_minutes' => ['required', 'integer', 'in:60,120'],
            'radius_km' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        $team = Auth::user()->team;

        if (! $team) {
            return redirect()->route('teams.index')->withErrors(['message' => 'Buat tim terlebih dahulu.']);
        }

        if ($team->owner_id !== Auth::id()) {
            abort(403);
        }

        if (! $team->isVerified()) {
            return back()
                ->withErrors(['message' => 'Tim kamu belum diverifikasi admin, jadi belum bisa membuat match otomatis.'])
                ->withInput();
        }

        if (! $team->hasMinimumPlayers()) {
            return back()
                ->withErrors(['message' => 'Minimal 5 pemain diperlukan untuk memakai AutoMatching.'])
                ->withInput();
        }

        $autoAvailability = $service->autoMatchAvailability();

        if (! $autoAvailability['available']) {
            return back()
                ->withErrors(['message' => $autoAvailability['message']])
                ->withInput();
        }

        $existingPendingMatch = FutsalMatch::query()
            ->whereIn('status', ['pending'])
            ->where(fn ($query) => $query
                ->where('team_a_id', $team->id)
                ->orWhere('team_b_id', $team->id))
            ->latest('created_at')
            ->first();

        if ($existingPendingMatch) {
            AutoMatchmakingQueue::query()
                ->where('team_id', $team->id)
                ->whereIn('status', ['waiting', 'searching'])
                ->update(['status' => 'cancelled']);

            return redirect()->route('matches.show', $existingPendingMatch)
                ->with('success', 'AutoMatching sudah menemukan lawan. Silakan konfirmasi match ini.');
        }

        $durationMinutes = (int) $request->duration_minutes;
        $radiusKm = (int) $request->radius_km;

        try {
            $queue = $service->startAutoSearch($team, $durationMinutes, $radiusKm);
        } catch (\RuntimeException $exception) {
            return back()
                ->withErrors(['message' => $exception->getMessage()])
                ->withInput();
        }

        return redirect()->route('matches.auto')
            ->with('success', 'Tim kamu masuk queue matchmaking. Sistem mencari lawan maksimal 5 menit.');
    }

    public function autoCancel()
    {
        $team = Auth::user()->team;

        if (! $team) {
            return redirect()->route('teams.index')->withErrors(['message' => 'Buat tim terlebih dahulu.']);
        }

        AutoMatchmakingQueue::query()
            ->where('team_id', $team->id)
            ->whereIn('status', ['waiting', 'searching'])
            ->update(['status' => 'cancelled']);

        return redirect()->route('matches.auto')->with('success', 'Pencarian AutoMatching dibatalkan.');
    }

    public function autoStatus(MatchmakingService $service)
    {
        $team = Auth::user()->team;

        if (! $team) {
            abort(403);
        }

        $queue = AutoMatchmakingQueue::query()
            ->where('team_id', $team->id)
            ->whereIn('status', ['waiting', 'searching'])
            ->latest()
            ->first();

        if ($queue) {
            $service->processQueue($queue);
            $queue->refresh();
        }

        if ($queue?->status === 'matched' && $queue->match_id) {
            $match = $queue->match()->with(['teamA', 'teamB', 'field', 'matchCost'])->first();
            $opponent = $match->team_a_id === $team->id ? $match->teamB : $match->teamA;

            return response()->json([
                'status' => 'matched',
                'match_status' => $match->status,
                'match_url' => route('matches.show', $match),
                'opponent' => $opponent?->name,
                'field' => $match->field?->name,
                'match_time' => Carbon::parse($match->match_date->toDateString().' '.$match->start_time)->format('d M Y H:i'),
            ]);
        }

        $matchedQueue = AutoMatchmakingQueue::query()
            ->where('team_id', $team->id)
            ->where('status', 'matched')
            ->whereNotNull('match_id')
            ->whereHas('match', fn ($query) => $query->where('status', 'pending'))
            ->with('match.teamA', 'match.teamB', 'match.field', 'match.matchCost')
            ->latest('matched_at')
            ->first();

        if ($matchedQueue?->match) {
            $match = $matchedQueue->match;
            $opponent = $match->team_a_id === $team->id ? $match->teamB : $match->teamA;

            return response()->json([
                'status' => 'matched',
                'match_status' => $match->status,
                'match_url' => route('matches.show', $match),
                'opponent' => $opponent?->name,
                'field' => $match->field?->name,
                'match_time' => Carbon::parse($match->match_date->toDateString().' '.$match->start_time)->format('d M Y H:i'),
            ]);
        }

        if ($queue) {
            return response()->json([
                'status' => $queue->status === 'waiting' ? 'searching' : $queue->status,
                'seconds_remaining' => (int) max(0, floor(now()->diffInSeconds($queue->expired_at, false))),
                'expires_at' => $queue->expired_at?->toIso8601String(),
            ]);
        }

        return response()->json(['status' => 'idle']);
    }

    public function autoConfirm(FutsalMatch $match)
    {
        $this->authorizeMatchView($match);

        if ($match->status !== 'pending') {
            return redirect()->route('matches.show', $match)->with('success', 'Status pertandingan sudah diperbarui.');
        }

        if ($match->isAutoMatch()) {
            $match->loadMissing(['booking.payments']);

            $paidTeamIds = $match->booking?->payments
                ?->where('payment_status', 'paid')
                ->pluck('team_id')
                ->all() ?? [];

            if (
                ! in_array($match->team_a_id, $paidTeamIds, true)
                || ! in_array($match->team_b_id, $paidTeamIds, true)
            ) {
                return redirect()->route('matches.show', $match)
                    ->withErrors(['message' => 'AutoMatching baru bisa dikonfirmasi setelah kedua tim melunasi pembayaran.']);
            }
        }

        $match->update(['status' => 'confirmed']);
        $match->booking?->update(['status' => 'confirmed']);

        foreach ([$match->teamA, $match->teamB] as $team) {
            $team?->owner?->notify(new MatchNotification(
                'auto_match_confirmed',
                "Pertandingan {$match->teamA->name} vs {$match->teamB->name} sudah dikonfirmasi.",
                $match->id
            ));
        }

        return redirect()->route('matches.show', $match)->with('success', 'Match dikonfirmasi. Lapangan sudah di-reserve.');
    }

    public function autoReject(FutsalMatch $match)
    {
        $this->authorizeMatchView($match);

        if ($match->isAutoMatch()) {
            return redirect()->route('matches.show', $match)
                ->withErrors(['message' => 'Match AutoMatching tidak bisa dibatalkan. Kedua tim wajib melunasi 100% biaya per tim + biaya admin 10%.']);
        }

        if (! in_array($match->status, ['pending', 'confirmed'], true)) {
            return redirect()->route('matches.auto')->withErrors(['message' => 'Match ini sudah tidak bisa ditolak.']);
        }

        $match->update(['status' => 'cancelled']);
        $match->booking?->update(['status' => 'cancelled']);

        foreach ([$match->teamA, $match->teamB] as $team) {
            $team?->owner?->notify(new MatchNotification(
                'auto_match_cancelled',
                "Pertandingan auto matchmaking dibatalkan.",
                $match->id
            ));
        }

        return redirect()->route('matches.auto')->with('success', 'Match ditolak dan slot lapangan dilepas.');
    }

    public function reject(MatchRequest $matchRequest)
    {
        $team = Auth::user()->team;

        if (! $team || ! in_array($team->id, [$matchRequest->requester_team_id, $matchRequest->opponent_team_id], true)) {
            abort(403);
        }

        $matchRequest->update(['status' => 'rejected']);

        $matchRequest->requesterTeam->owner->notify(new MatchNotification(
            'match_rejected',
            "Your match request has been rejected by {$team->name}.",
            $matchRequest->id
        ));

        return response()->json(['message' => 'Match request rejected successfully']);
    }

    public function cancel(FutsalMatch $match, MidtransSnapService $midtrans)
    {
        $this->authorizeMatchCancel($match);

        if ($match->isAutoMatch()) {
            return redirect()->route('matches.show', $match)
                ->withErrors(['message' => 'Match AutoMatching tidak bisa dibatalkan. Kedua tim wajib melunasi 100% biaya per tim + biaya admin 10%.']);
        }

        if ($match->status === 'cancelled') {
            return redirect()->route('matches.take')->with('success', 'Pertandingan sudah dibatalkan.');
        }

        $match->loadMissing('booking.payments');
        $refundAllowed = $match->created_at?->greaterThanOrEqualTo(now()->subHours(6)) ?? false;

        $match->update(['status' => 'cancelled']);
        $match->booking?->update(['status' => 'cancelled']);

        $refundedCount = 0;

        if ($refundAllowed) {
            $refundedCount = $this->refundPaidPaymentsForCancelledMatch($match, $midtrans);
        }

        $this->releaseVenueScheduleForMatch($match);

        $match->teamA->owner->notify(new MatchNotification(
            'match_cancelled',
            "Match on {$match->match_date} has been cancelled.",
            $match->id
        ));

        if ($match->teamB) {
            $match->teamB->owner->notify(new MatchNotification(
                'match_cancelled',
                "Match on {$match->match_date} has been cancelled.",
                $match->id
            ));
        }

        $message = $refundAllowed
            ? 'Pertandingan berhasil dibatalkan. Refund demo Midtrans dibuat untuk ' . $refundedCount . ' pembayaran dan slot lapangan dilepas.'
            : 'Pertandingan berhasil dibatalkan. DP hangus karena pembatalan dilakukan lebih dari 6 jam setelah pertandingan dibuat.';

        return redirect()->route('matches.show', $match)->with('success', $message);
    }

    protected function refundPaidPaymentsForCancelledMatch(FutsalMatch $match, MidtransSnapService $midtrans): int
    {
        $payments = $match->booking?->payments()
            ->where('payment_status', 'paid')
            ->get() ?? collect();

        $refundedCount = 0;

        foreach ($payments as $payment) {
            $orderId = $payment->midtrans_order_id ?: 'MATCHGO-DEMO-PAYMENT-' . $payment->id;
            $amount = (int) round((float) $payment->amount);
            $reason = 'Demo refund pembatalan match #' . $match->id;

            try {
                $refund = $midtrans->refundDemo($orderId, $amount, $reason);
            } catch (\Throwable $exception) {
                $refund = [
                    'refund_key' => 'RF-LOCAL-' . now()->format('YmdHisv') . '-' . $payment->id,
                    'status_message' => 'Fallback demo refund lokal: ' . $exception->getMessage(),
                    'source' => 'local_fallback',
                ];
            }

            $payment->update([
                'payment_status' => 'refunded',
                'refund_reference' => $refund['refund_key'] ?? ('RF-DEMO-' . $payment->id),
                'refund_note' => ($refund['status_message'] ?? 'Demo refund berhasil.') . ' (' . ($refund['source'] ?? 'demo') . ')',
                'refunded_at' => now(),
            ]);

            $refundedCount++;
        }

        return $refundedCount;
    }

    protected function releaseVenueScheduleForMatch(FutsalMatch $match): void
    {
        $field = $match->booking?->field ?? $match->field;

        if (! $field) {
            return;
        }

        $venue = Venue::query()->where('name', $field->name)->first();

        if (! $venue) {
            return;
        }

        VenueSchedule::query()
            ->where('venue_id', $venue->id)
            ->whereDate('date', $match->match_date)
            ->whereTime('start_time', Carbon::parse($match->start_time)->format('H:i:s'))
            ->where('is_booked', true)
            ->update(['is_booked' => false]);
    }

    protected function authorizeMatchView(FutsalMatch $match): void
    {
        $team = Auth::user()->team;

        if (! $team) {
            abort(403);
        }

        if ($match->team_b_id === null && $match->status === 'scheduled') {
            return;
        }

        if (! in_array($team->id, [$match->team_a_id, $match->team_b_id], true)) {
            abort(403);
        }
    }

    protected function authorizeMatchManage(FutsalMatch $match): void
    {
        $team = Auth::user()->team;

        if (! $team || $match->team_a_id !== $team->id || $team->owner_id !== Auth::id()) {
            abort(403);
        }
    }

    protected function authorizeMatchCancel(FutsalMatch $match): void
    {
        $team = Auth::user()->team;

        if (! $team || $team->owner_id !== Auth::id()) {
            abort(403);
        }

        if (! in_array($team->id, [$match->team_a_id, $match->team_b_id], true)) {
            abort(403);
        }
    }
}
