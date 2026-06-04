<?php

namespace App\Http\Controllers;

use App\Models\FutsalMatch;
use App\Models\MatchRequest;
use App\Models\Team;
use App\Notifications\MatchAccepted;
use App\Notifications\MatchRejected;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MatchController extends Controller
{
    public function index(): View
    {
        $myTeamIds = auth()->user()->ownedTeams()->pluck('id');

        // Open challenges from OTHER teams (no opponent assigned yet)
        $openChallenges = MatchRequest::with(['requesterTeam.teamStats'])
            ->whereNull('opponent_team_id')
            ->whereNotIn('requester_team_id', $myTeamIds)
            ->where('status', 'pending')
            ->latest()
            ->get();

        // Incoming: challenges directed specifically at MY teams (pending)
        $incoming = MatchRequest::with(['requesterTeam'])
            ->whereIn('opponent_team_id', $myTeamIds)
            ->where('status', 'pending')
            ->latest()
            ->get();

        // Accepted matches waiting for venue booking (my team is requester OR opponent)
        $accepted = MatchRequest::with([
                'requesterTeam.teamMembers.user',
                'opponentTeam.teamMembers.user',
                'futsalMatch',
            ])
            ->where('status', 'accepted')
            ->whereDoesntHave('futsalMatch')
            ->where(function ($q) use ($myTeamIds) {
                $q->whereIn('requester_team_id', $myTeamIds)
                  ->orWhereIn('opponent_team_id', $myTeamIds);
            })
            ->latest()
            ->get();

        // Confirmed matches — show all scheduled/ongoing regardless of date
        // Eager-load team owners (captains) so the view can show WhatsApp links.
        $upcoming = FutsalMatch::with(['venue', 'teamA.owner', 'teamB.owner'])
            ->where(function ($q) use ($myTeamIds) {
                $q->whereIn('team_a_id', $myTeamIds)
                  ->orWhereIn('team_b_id', $myTeamIds);
            })
            ->whereIn('status', ['scheduled', 'ongoing'])
            ->orderBy('match_date')
            ->get();

        // Past matches history
        $history = FutsalMatch::with(['venue', 'teamA', 'teamB', 'matchCost'])
            ->where(function ($q) use ($myTeamIds) {
                $q->whereIn('team_a_id', $myTeamIds)
                  ->orWhereIn('team_b_id', $myTeamIds);
            })
            ->whereIn('status', ['completed', 'cancelled'])
            ->orderByDesc('match_date')
            ->take(10)
            ->get();

        // All my challenges — where my team is requester OR opponent
        $myChallenges = MatchRequest::with(['requesterTeam', 'opponentTeam'])
            ->where(function ($q) use ($myTeamIds) {
                $q->whereIn('requester_team_id', $myTeamIds)
                  ->orWhereIn('opponent_team_id', $myTeamIds);
            })
            ->whereNotIn('status', ['accepted']) // accepted ones shown in their own section
            ->latest()
            ->paginate(8);

        // My verified teams (for accepting open challenges)
        $myTeams = auth()->user()->ownedTeams()
            ->where('verification_status', 'verified')
            ->get();

        return view('match.index', compact('openChallenges', 'incoming', 'accepted', 'myChallenges', 'upcoming', 'history', 'myTeams', 'myTeamIds'));
    }

    public function create(): View
    {
        $myTeams = auth()->user()->ownedTeams()
            ->where('verification_status', 'verified')
            ->get();

        return view('match.create', compact('myTeams'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'requester_team_id' => ['required', 'exists:teams,id'],
            'preferred_date'    => ['required', 'date', 'after_or_equal:today'],
            'notes'             => ['nullable', 'string', 'max:255'],
        ]);

        $team = Team::findOrFail($validated['requester_team_id']);

        if ($team->owner_id !== auth()->id()) {
            abort(403, 'Kamu bukan pemilik tim ini.');
        }

        if ($team->verification_status !== 'verified') {
            return back()->with('error', 'Tim kamu belum terverifikasi. Tunggu proses audit admin.');
        }

        // Check for existing open pending challenge from this team
        $exists = MatchRequest::where('requester_team_id', $team->id)
            ->whereNull('opponent_team_id')
            ->where('status', 'pending')
            ->exists();

        if ($exists) {
            return back()->with('error', 'Tim kamu sudah punya tantangan terbuka yang aktif. Tunggu ada yang menerima dulu.');
        }

        MatchRequest::create([
            'requester_team_id' => $validated['requester_team_id'],
            'opponent_team_id'  => null,
            'preferred_date'    => $validated['preferred_date'],
            'status'            => 'pending',
            'notes'             => $validated['notes'] ?? null,
        ]);

        return redirect()->route('match.index')
            ->with('success', 'Tantangan terbuka berhasil diposting! Tunggu tim lain merespons.');
    }

    public function show(int $id): View
    {
        $request = MatchRequest::with([
            'requesterTeam.teamStats',
            'opponentTeam.teamStats',
            'futsalMatch.venue',
            'futsalMatch.matchCost',
        ])->findOrFail($id);

        $this->authorizeMatchRequest($request);

        $myTeams = auth()->user()->ownedTeams()
            ->where('verification_status', 'verified')
            ->get();

        return view('match.show', compact('request', 'myTeams'));
    }

    public function accept(int $id): RedirectResponse
    {
        $matchRequest = MatchRequest::with(['requesterTeam'])->findOrFail($id);

        if ($matchRequest->status !== 'pending') {
            return back()->with('error', 'Tantangan ini sudah tidak bisa diproses.');
        }

        $myTeamIds = auth()->user()->ownedTeams()->pluck('id');

        // Open challenge: the accepting user picks which of their teams responds
        if (is_null($matchRequest->opponent_team_id)) {
            $acceptingTeamId = request('team_id');

            if (! $acceptingTeamId) {
                return back()->with('error', 'Pilih tim kamu terlebih dahulu.');
            }

            if (! $myTeamIds->contains($acceptingTeamId)) {
                abort(403, 'Bukan timmu.');
            }

            if ($matchRequest->requester_team_id == $acceptingTeamId) {
                return back()->with('error', 'Kamu tidak bisa menerima tantanganmu sendiri.');
            }

            $matchRequest->update([
                'opponent_team_id' => $acceptingTeamId,
                'status'           => 'accepted',
            ]);
        } else {
            // Directed challenge: only the designated opponent can accept
            if (! $myTeamIds->contains($matchRequest->opponent_team_id)) {
                abort(403, 'Kamu bukan pemilik tim yang ditantang.');
            }

            $matchRequest->update(['status' => 'accepted']);
        }

        $matchRequest->requesterTeam->owner->notify(new MatchAccepted($matchRequest));

        return redirect()->route('match.index')
            ->with('success', 'Tantangan diterima! Segera atur jadwal lapangan.');
    }

    public function reject(int $id): RedirectResponse
    {
        $matchRequest = MatchRequest::with('requesterTeam')->findOrFail($id);

        if ($matchRequest->status !== 'pending') {
            return back()->with('error', 'Tantangan ini sudah tidak bisa diproses.');
        }

        $myTeamIds = auth()->user()->ownedTeams()->pluck('id');

        if (! is_null($matchRequest->opponent_team_id) && ! $myTeamIds->contains($matchRequest->opponent_team_id)) {
            abort(403);
        }

        $matchRequest->update(['status' => 'rejected']);

        $matchRequest->requesterTeam->owner->notify(new MatchRejected($matchRequest));

        return redirect()->route('match.index')
            ->with('success', 'Tantangan ditolak.');
    }

    private function authorizeMatchRequest(MatchRequest $request): void
    {
        $teamIds = auth()->user()->ownedTeams()->pluck('id');

        // Owner of the requester team can always see it
        if ($teamIds->contains($request->requester_team_id)) {
            return;
        }

        // Open challenge — anyone logged in can view
        if (is_null($request->opponent_team_id)) {
            return;
        }

        if (! $teamIds->contains($request->opponent_team_id)) {
            abort(403);
        }
    }
}
