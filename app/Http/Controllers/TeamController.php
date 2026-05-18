<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeamRequest;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TeamController extends Controller
{
    public function index()
    {
        $userTeam = Auth::user()->team;
        return view('teams.index', ['team' => $userTeam]);
    }

    public function store(TeamRequest $request)
    {
        $user = Auth::user();

        if ($user->team) {
            return back()->withErrors(['message' => 'Anda sudah memiliki satu tim.']);
        }

        $logoPath = $request->hasFile('logo')
            ? $request->file('logo')->store('team-logos', 'public')
            : null;

        $team = Team::create([
            'owner_id' => $user->id,
            'name' => $request->team_name,
            'description' => $request->description,
            'city' => $request->domicile,
            'skill_level' => $request->team_level,
            'contact_number' => $request->contact_number,
            'logo_url' => $logoPath,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'player_count' => 0,
            'verification_status' => 'pending',
        ]);

        return redirect()->route('teams.show', $team)->with('success', 'Tim berhasil dibuat.');
    }

    public function show(Team $team)
    {
        $this->authorizeTeam($team);

        return view('teams.show', ['team' => $team->load(['players', 'sentMatchRequests', 'receivedMatchRequests'])]);
    }

    public function update(TeamRequest $request, Team $team)
    {
        $this->authorizeTeam($team);

        $data = [
            'name' => $request->team_name,
            'description' => $request->description,
            'city' => $request->domicile,
            'skill_level' => $request->team_level,
            'contact_number' => $request->contact_number,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
        ];

        if ($request->hasFile('logo')) {
            if ($team->logo_url) {
                Storage::disk('public')->delete($team->logo_url);
            }

            $data['logo_url'] = $request->file('logo')->store('team-logos', 'public');
        }

        $team->update($data);

        return redirect()->route('teams.show', $team)->with('success', 'Tim berhasil diperbarui.');
    }

    public function destroy(Team $team)
    {
        $this->authorizeTeam($team);

        if ($team->logo_url) {
            Storage::disk('public')->delete($team->logo_url);
        }

        $team->delete();

        return redirect()->route('teams.index')->with('success', 'Tim berhasil dihapus.');
    }

    protected function authorizeTeam(Team $team): void
    {
        if ($team->owner_id !== Auth::id()) {
            abort(403);
        }
    }
}
