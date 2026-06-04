<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\AutoMatchmakingQueue;
use App\Models\Field;
use App\Models\FutsalMatch;
use App\Models\MatchCost;
use App\Models\MatchRequest;
use App\Models\Team;
use App\Notifications\MatchNotification;
use App\Services\MatchmakingService;
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

        $autoParams = [
            'duration_minutes' => (int) $request->input('duration_minutes', 60),
            'radius_km' => (int) $request->input('radius_km', 10),
        ];

        $latestMatchedQueue = AutoMatchmakingQueue::query()
            ->where('team_id', $team->id)
            ->where('status', 'matched')
            ->whereNotNull('match_id')
            ->whereHas('match', fn ($query) => $query->whereIn('status', ['pending', 'confirmed']))
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
        ]);
    }

    public function create()
    {
        $team = Auth::user()->team;

        if (! $team) {
            return redirect()->route('teams.index')->withErrors(['message' => 'Buat tim terlebih dahulu.']);
        }

        if ($team->owner_id !== Auth::id()) {
            abort(403);
        }

        if (! $team->hasMinimumPlayers()) {
            return redirect()->route('teams.show', $team)
                ->withErrors(['message' => 'Minimal 5 pemain diperlukan untuk memakai fitur Buat Pertandingan.']);
        }

        if (! $team->isVerified()) {
            return redirect()->route('matches.take')
                ->withErrors(['message' => 'Tim kamu belum diverifikasi admin, jadi belum bisa membuat tantangan.']);
        }

        $fields = Field::where('is_available', true)->get();
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

        return view('matches.create', [
            'team' => $team,
            'fields' => $fields,
            'myChallenges' => $myChallenges,
        ]);
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
        $durationHours = (int) ceil($request->duration_minutes / 60);

        // Cek ketersediaan lapangan
        $bookingStartTime = Carbon::parse("$matchDate $startTime");
        $bookingEndTime = $bookingStartTime->copy()->addMinutes((int) $request->duration_minutes);

        if ($bookingStartTime->lt(now())) {
            return back()
                ->withErrors(['message' => 'Jam pertandingan sudah lewat. Pilih tanggal dan jam yang masih akan datang.'])
                ->withInput();
        }

        $booking = Booking::query()
            ->where('field_id', $field->id)
            ->where('status', '!=', 'cancelled')
            ->where('start_at', '<', $bookingEndTime)
            ->whereRaw('DATE_ADD(start_at, INTERVAL duration_hours HOUR) > ?', [$bookingStartTime])
            ->first();

        if ($booking) {
            return back()->withErrors(['message' => 'Lapangan tidak tersedia pada jam tersebut.']);
        }

        $match = DB::transaction(function () use ($team, $field, $matchDate, $startTime, $request, $bookingStartTime, $durationHours) {
            $match = FutsalMatch::create([
                'match_request_id' => null,
                'venue_id' => null,
                'field_id' => $field->id,
                'team_a_id' => $team->id,
                'team_b_id' => null,
                'match_date' => $matchDate,
                'start_time' => $startTime,
                'duration_minutes' => $request->duration_minutes,
                'status' => 'scheduled',
            ]);

            Booking::create([
                'field_id' => $field->id,
                'match_id' => $match->id,
                'start_at' => $bookingStartTime,
                'duration_hours' => $durationHours,
                'status' => 'confirmed',
            ]);

            $totalCost = $field->price_per_hour * $durationHours;

            MatchCost::create([
                'match_id' => $match->id,
                'total_cost' => $totalCost,
                'cost_per_team' => (int) round($totalCost / 2),
                'dp_per_team' => (int) ceil(($totalCost / 2) * 0.5),
                'handling_fee' => (int) ceil($totalCost * 0.1),
                'cost_per_player' => (int) round($totalCost / max(1, $team->player_count ?: 1)),
                'payment_notes' => "Biaya lapangan {$field->name} dibagi 2 tim. DP minimal 50% dari biaya per tim. Biaya penanganan 10% untuk pengelola web.",
            ]);

            return $match;
        });

        $team->owner->notify(new MatchNotification(
            'challenge_created',
            "Tantangan pertandingan dibuat pada {$match->match_date->format('d M Y')} di {$field->name}.",
            $match->id
        ));

        return redirect()->route('matches.show', $match)->with('success', 'Tantangan dibuat, lapangan sudah dibooking, dan biaya sudah dibagi 2.');
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

        $match->teamA->owner->notify(new MatchNotification(
            'challenge_accepted',
            "{$team->name} menerima tantangan pertandingan kamu. Buka detail match untuk melihat jadwal dan lapangan.",
            $match->id
        ));

        $team->owner->notify(new MatchNotification(
            'challenge_accepted',
            "Kamu berhasil menerima tantangan dari {$match->teamA->name}. Biaya per tim: Rp " . number_format($match->matchCost?->cost_per_team ?? 0),
            $match->id
        ));

        return redirect()->route('matches.show', $match)->with('success', 'Tantangan berhasil diambil. Lapangan sudah terbooking dan biaya dibagi 2 tim.');
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

        $queue = $service->startAutoSearch($team, $durationMinutes, $radiusKm);

        return redirect()->route('matches.auto')
            ->with('success', 'Tim kamu masuk queue matchmaking. Sistem mencari lawan maksimal 5 menit.');
    }

    public function autoCancel()
    {
        return redirect()->route('matches.auto')
            ->withErrors(['message' => 'AutoMatching tidak bisa dibatalkan setelah pencarian dimulai.']);
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
            ->whereHas('match', fn ($query) => $query->whereIn('status', ['pending', 'confirmed']))
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
                ->withErrors(['message' => 'Match AutoMatching tidak bisa dibatalkan. Kedua tim wajib membayar DP minimal 50%.']);
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

    public function cancel(FutsalMatch $match)
    {
        $this->authorizeMatchCancel($match);

        if ($match->isAutoMatch()) {
            return redirect()->route('matches.show', $match)
                ->withErrors(['message' => 'Match AutoMatching tidak bisa dibatalkan. Kedua tim wajib membayar DP minimal 50%.']);
        }

        if ($match->status === 'cancelled') {
            return redirect()->route('matches.take')->with('success', 'Pertandingan sudah dibatalkan.');
        }

        $match->update(['status' => 'cancelled']);
        $match->booking?->update(['status' => 'cancelled']);

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

        return redirect()->route('matches.take')->with('success', 'Pertandingan berhasil dibatalkan.');
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
