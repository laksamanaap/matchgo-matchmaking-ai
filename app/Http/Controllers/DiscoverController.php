<?php

namespace App\Http\Controllers;

use App\Models\MatchRequest;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiscoverController extends Controller
{
    public function index(Request $request): View
    {
        $query = Team::query()
            ->where('verification_status', 'verified')
            ->whereNotIn('owner_id', [auth()->id()])
            ->with(['teamStats', 'owner', 'teamSchedules'])
            ->withCount('teamMembers');

        if ($request->filled('skill_level')) {
            $query->where('skill_level', $request->skill_level);
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        if ($request->filled('day')) {
            $query->whereHas('teamSchedules', fn ($q) =>
                $q->where('day_of_week', $request->day)->where('is_active', true)
            );
        }

        $teams = $query->latest()->paginate(12)->withQueryString();

        $cities = Team::where('verification_status', 'verified')->distinct()->pluck('city')->sort()->values();

        return view('discover.index', compact('teams', 'cities'));
    }

    public function show(int $id): View
    {
        $team = Team::with([
            'teamStats',
            'teamMembers.user',
            'teamSchedules',
            'owner',
        ])->where('verification_status', 'verified')->findOrFail($id);

        $myTeams = auth()->user()->ownedTeams()
            ->where('verification_status', 'verified')
            ->get();

        $alreadyChallenged = MatchRequest::whereIn('requester_team_id', $myTeams->pluck('id'))
            ->where('opponent_team_id', $team->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->exists();

        return view('discover.show', compact('team', 'myTeams', 'alreadyChallenged'));
    }
}
