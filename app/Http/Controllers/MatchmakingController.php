<?php

namespace App\Http\Controllers;

use App\Events\MatchAcceptedEvent;
use App\Events\MatchRejectedEvent;
use App\Models\MatchmakingHistory;
use App\Models\MatchmakingMatch;
use App\Models\MatchmakingQueue;
use App\Models\MatchRequest;
use App\Models\Team;
use App\Services\AutoBookingService;
use App\Services\QueueManagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatchmakingController extends Controller
{
    public function __construct(private QueueManagerService $manager) {}

    public function join(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'team_id' => ['required', 'exists:teams,id'],
        ]);

        $team = Team::findOrFail($validated['team_id']);

        if ($team->owner_id !== auth()->id()) {
            abort(403, 'Hanya kapten yang bisa memulai matchmaking.');
        }

        if ($team->verification_status !== 'verified') {
            return response()->json([
                'message' => 'Tim belum terverifikasi. Verifikasi dulu di profil tim.',
            ], 422);
        }

        $queue = $this->manager->join($team, $request->user());

        return response()->json([
            'message' => 'Bergabung ke antrian matchmaking.',
            'queue'   => $this->manager->getStatus($team),
            'queue_id'=> $queue->id,
        ]);
    }

    public function leave(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'team_id' => ['required', 'exists:teams,id'],
        ]);

        $team = Team::findOrFail($validated['team_id']);

        if ($team->owner_id !== auth()->id()) {
            abort(403);
        }

        $queue = MatchmakingQueue::where('team_id', $team->id)
            ->where('status', 'waiting')
            ->first();

        if ($queue) {
            $this->manager->leave($queue);
        }

        return response()->json(['message' => 'Antrian dibatalkan.']);
    }

    public function accept(int $matchmakingMatchId): JsonResponse
    {
        $match = MatchmakingMatch::with(['queueA.team', 'queueB.team'])
            ->findOrFail($matchmakingMatchId);

        $side   = $this->resolveSide($match);
        $teamId = $side === 'a' ? $match->queueA->team_id : $match->queueB->team_id;

        if ($match->status !== 'pending') {
            return response()->json(['message' => 'Match sudah tidak aktif.'], 422);
        }

        if ($match->expires_at?->isPast()) {
            return response()->json(['message' => 'Match sudah kedaluwarsa.'], 422);
        }

        DB::transaction(function () use ($match, $side, $teamId) {
            $match->refresh();

            if ($side === 'a') {
                $match->update(['accepted_by_a' => true]);
            } else {
                $match->update(['accepted_by_b' => true]);
            }

            $match->refresh();

            if ($match->isFullyAccepted()) {
                $teamA = $match->queueA->team;
                $teamB = $match->queueB->team;

                $matchRequest = MatchRequest::create([
                    'requester_team_id' => $teamA->id,
                    'opponent_team_id'  => $teamB->id,
                    'status'            => 'accepted',
                    'preferred_date'    => now()->addHours(AutoBookingService::MIN_LEAD_HOURS)->toDateString(),
                    'notes'             => 'Dibuat otomatis oleh sistem matchmaking.',
                ]);

                $match->update([
                    'status'           => 'accepted',
                    'match_request_id' => $matchRequest->id,
                ]);

                app(AutoBookingService::class)->autoBook($matchRequest, $teamA, $teamB);

                $this->logHistory($match, 'accepted');

                broadcast(new MatchAcceptedEvent($match->fresh(), $teamA->id));
                broadcast(new MatchAcceptedEvent($match->fresh(), $teamB->id));
            } else {
                broadcast(new MatchAcceptedEvent($match, $teamId));
            }
        });

        return response()->json([
            'message'        => 'Match diterima.',
            'fully_accepted' => $match->fresh()->isFullyAccepted(),
            'match_request_id' => $match->fresh()->match_request_id,
        ]);
    }

    public function reject(int $matchmakingMatchId): JsonResponse
    {
        $match = MatchmakingMatch::with(['queueA', 'queueB'])
            ->findOrFail($matchmakingMatchId);

        $this->resolveSide($match);

        if ($match->status !== 'pending') {
            return response()->json(['message' => 'Match sudah tidak aktif.'], 422);
        }

        DB::transaction(function () use ($match) {
            $match->update(['status' => 'rejected']);

            // Both queues are cancelled — captains must rejoin manually.
            $match->queueA?->update(['status' => 'cancelled']);
            $match->queueB?->update(['status' => 'cancelled']);

            $this->logHistory($match, 'rejected');
        });

        broadcast(new MatchRejectedEvent($match->fresh(), 'rejected'));

        return response()->json(['message' => 'Match ditolak.']);
    }

    /**
     * Determine which side (a|b) the authenticated user belongs to. Aborts with
     * 403 if user is not the captain of either team in the match.
     */
    private function resolveSide(MatchmakingMatch $match): string
    {
        $userId = auth()->id();

        if ($match->queueA?->captain_id === $userId) {
            return 'a';
        }
        if ($match->queueB?->captain_id === $userId) {
            return 'b';
        }
        abort(403, 'Bukan kapten tim yang terlibat di match ini.');
    }

    private function logHistory(MatchmakingMatch $match, string $result): void
    {
        $teamA = $match->queueA;
        $teamB = $match->queueB;

        if ($teamA) {
            MatchmakingHistory::create([
                'team_id'                => $teamA->team_id,
                'matched_team_id'        => $teamB?->team_id,
                'compatibility_score'    => $match->compatibility_score,
                'queue_duration_seconds' => $teamA->queued_at?->diffInSeconds(now()) ?? 0,
                'result'                 => $result,
            ]);
        }

        if ($teamB) {
            MatchmakingHistory::create([
                'team_id'                => $teamB->team_id,
                'matched_team_id'        => $teamA?->team_id,
                'compatibility_score'    => $match->compatibility_score,
                'queue_duration_seconds' => $teamB->queued_at?->diffInSeconds(now()) ?? 0,
                'result'                 => $result,
            ]);
        }
    }
}
