<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlayerRequest;
use App\Models\Player;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlayerController extends Controller
{
    public function index()
    {
        $team = Auth::user()->team;

        if (! $team) {
            return response()->json(['message' => 'You must create a team first.'], 422);
        }

        return response()->json($team->players);
    }

    public function store(PlayerRequest $request)
    {
        $team = Auth::user()->team;

        if (! $team) {
            return back()->withErrors(['message' => 'You must create a team first.']);
        }

        $team->players()->create($request->validated());

        return back()->with('success', 'Pemain berhasil ditambahkan.');
    }

    public function demoFill()
    {
        $team = Auth::user()->team;

        if (! $team) {
            return back()->withErrors(['message' => 'Buat tim terlebih dahulu.']);
        }

        if ($team->owner_id !== Auth::id()) {
            abort(403);
        }

        $samples = [
            ['player_name' => 'Andi Pratama', 'position' => 'Kiper', 'age' => 24],
            ['player_name' => 'Budi Santoso', 'position' => 'Anchor', 'age' => 26],
            ['player_name' => 'Citra Nugraha', 'position' => 'Flank Kiri', 'age' => 22],
            ['player_name' => 'Dedi Kurniawan', 'position' => 'Flank Kanan', 'age' => 25],
            ['player_name' => 'Eka Saputra', 'position' => 'Pivot', 'age' => 23],
        ];

        foreach ($samples as $sample) {
            $team->players()->create($sample);
        }

        return back()->with('success', '5 pemain demo berhasil ditambahkan.');
    }

    public function show(Player $player)
    {
        $this->authorizePlayer($player);

        return response()->json($player);
    }

    public function update(PlayerRequest $request, Player $player)
    {
        $this->authorizePlayer($player);

        $player->update($request->validated());

        return back()->with('success', 'Pemain berhasil diperbarui.');
    }

    public function destroy(Player $player)
    {
        $this->authorizePlayer($player);

        $player->delete();

        return back()->with('success', 'Pemain berhasil dihapus.');
    }

    protected function authorizePlayer(Player $player): void
    {
        if ($player->team->owner_id !== Auth::id()) {
            abort(403);
        }
    }
}
