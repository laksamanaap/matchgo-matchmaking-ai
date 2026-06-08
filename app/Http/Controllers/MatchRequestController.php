<?php

namespace App\Http\Controllers;

use App\Http\Requests\MatchRequestStoreRequest;
use App\Models\MatchRequest;
use App\Models\Team;
use App\Notifications\MatchNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MatchRequestController extends Controller
{
    public function index(): View|JsonResponse|RedirectResponse
    {
        $team = $this->captainTeam();

        if (! $team) {
            if (request()->expectsJson()) {
                return response()->json(['message' => 'Only team captains can access match requests.'], 403);
            }

            return redirect()->route('teams.index')
                ->withErrors(['message' => 'Hanya captain tim yang dapat melihat request pertandingan.']);
        }

        $requests = MatchRequest::query()
            ->where(function ($query) use ($team) {
                $query->where('requester_team_id', $team->id)
                    ->orWhere('opponent_team_id', $team->id);
            })
            ->with(['requesterTeam', 'opponentTeam'])
            ->latest()
            ->get();

        if (request()->expectsJson()) {
            return response()->json($requests);
        }

        return view('match-requests.index', [
            'team' => $team,
            'requests' => $requests,
        ]);
    }

    public function store(MatchRequestStoreRequest $request): RedirectResponse|JsonResponse
    {
        $team = $this->captainTeam();

        if (! $team) {
            return $this->respondError('Hanya captain tim yang dapat membuat request pertandingan.', 403);
        }

        if (! $team->isVerified()) {
            return $this->respondError('Tim kamu belum diverifikasi admin, jadi belum bisa membuat request pertandingan.', 422);
        }

        if ((int) $team->id === (int) $request->opponent_team_id) {
            return $this->respondError('Tim tidak dapat membuat request pertandingan melawan tim sendiri.', 422);
        }

        $opponent = Team::findOrFail($request->opponent_team_id);

        if (! $opponent->isVerified()) {
            return $this->respondError('Tim lawan belum diverifikasi admin, jadi belum bisa menerima request pertandingan.', 422);
        }

        $existingPendingRequest = MatchRequest::query()
            ->where('status', 'pending')
            ->where(function ($query) use ($team, $opponent) {
                $query->where(function ($query) use ($team, $opponent) {
                    $query->where('requester_team_id', $team->id)
                        ->where('opponent_team_id', $opponent->id);
                })->orWhere(function ($query) use ($team, $opponent) {
                    $query->where('requester_team_id', $opponent->id)
                        ->where('opponent_team_id', $team->id);
                });
            })
            ->exists();

        if ($existingPendingRequest) {
            return $this->respondError('Masih ada request pertandingan pending dengan tim ini.', 422);
        }

        $matchRequest = MatchRequest::create([
            'requester_team_id' => $team->id,
            'opponent_team_id' => $opponent->id,
            'preferred_date' => $request->preferred_date,
            'preferred_location' => $request->preferred_location,
            'status' => 'pending',
        ]);

        $opponent->owner->notify(new MatchNotification(
            'match_request',
            "Team {$team->name} mengajukan pertandingan pada {$request->preferred_date} di {$request->preferred_location}.",
            $matchRequest->id
        ));

        if ($request->expectsJson()) {
            return response()->json(['match_request' => $matchRequest], 201);
        }

        return redirect()->route('match_requests.index')
            ->with('success', 'Request pertandingan berhasil dibuat dan masuk ke sistem matchmaking.');
    }

    public function cancel(MatchRequest $matchRequest): RedirectResponse|JsonResponse
    {
        $team = $this->captainTeam();

        if (! $team || $matchRequest->requester_team_id !== $team->id) {
            abort(403);
        }

        if ($matchRequest->status !== 'pending') {
            return $this->respondError('Hanya request pending yang dapat dibatalkan.', 422);
        }

        $matchRequest->update(['status' => 'cancelled']);

        if (request()->expectsJson()) {
            return response()->json(['message' => 'Match request cancelled successfully']);
        }

        return redirect()->route('match_requests.index')
            ->with('success', 'Request pertandingan berhasil dibatalkan.');
    }

    protected function captainTeam(): ?Team
    {
        $team = Auth::user()?->team;

        if (! $team || $team->owner_id !== Auth::id()) {
            return null;
        }

        return $team;
    }

    protected function respondError(string $message, int $status): RedirectResponse|JsonResponse
    {
        if (request()->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        return back()->withErrors(['message' => $message])->withInput();
    }
}
