<?php

namespace App\Http\Controllers;

use App\Models\MatchRequest;
use App\Models\Team;
use App\Services\MatchmakingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AutomatchController extends Controller
{
    public function __construct(private MatchmakingService $matchmaking) {}

    public function index(): View
    {
        $myTeams = auth()->user()->ownedTeams()
            ->where('verification_status', 'verified')
            ->with('teamSchedules', 'teamStats')
            ->get();

        $selectedTeam = null;
        $candidates   = collect();

        if ($myTeams->count() === 1) {
            $selectedTeam = $myTeams->first();
            try {
                $candidates = $this->matchmaking->findOpponentWithScore($selectedTeam);
            } catch (\RuntimeException $e) {
                session()->flash('error', $e->getMessage());
            }
        }

        return view('automatching.index', compact('myTeams', 'selectedTeam', 'candidates'));
    }

    public function run(Request $request): View
    {
        $validated = $request->validate([
            'team_id' => ['required', 'exists:teams,id'],
        ]);

        $team = Team::with(['teamSchedules', 'teamStats'])->findOrFail($validated['team_id']);

        if ($team->owner_id !== auth()->id()) {
            abort(403);
        }

        $myTeams = auth()->user()->ownedTeams()
            ->where('verification_status', 'verified')
            ->with('teamSchedules', 'teamStats')
            ->get();

        $candidates = collect();
        try {
            $candidates = $this->matchmaking->findOpponentWithScore($team);
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }

        return view('automatching.index', [
            'myTeams'      => $myTeams,
            'selectedTeam' => $team,
            'candidates'   => $candidates,
        ]);
    }

    public function challenge(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'requester_team_id' => ['required', 'exists:teams,id'],
            'opponent_team_id'  => ['required', 'exists:teams,id'],
            'preferred_date'    => ['required', 'date', 'after_or_equal:today'],
        ]);

        $team = Team::findOrFail($validated['requester_team_id']);
        if ($team->owner_id !== auth()->id()) {
            abort(403);
        }

        MatchRequest::create([
            'requester_team_id' => $validated['requester_team_id'],
            'opponent_team_id'  => $validated['opponent_team_id'],
            'preferred_date'    => $validated['preferred_date'],
            'status'            => 'pending',
        ]);

        return redirect()->route('match.index')
            ->with('success', 'Tantangan otomatis berhasil dikirim!');
    }
}
