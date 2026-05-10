@extends('layouts.app')

@section('title', 'Detail Pertandingan')

@section('content')
<div class="bg-[#F1F8E9] min-h-screen">
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <a href="{{ route('match.index') }}" class="flex items-center gap-1.5 text-gray-500 hover:text-[#2E7D32] text-sm transition mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Pertandingan
    </a>

    @php
        $badges = [
            'pending'   => 'bg-amber-100 text-amber-700',
            'accepted'  => 'bg-green-100 text-green-700',
            'rejected'  => 'bg-red-100 text-red-600',
            'cancelled' => 'bg-gray-100 text-gray-500',
        ];
        $labels = ['pending'=>'Menunggu','accepted'=>'Diterima','rejected'=>'Ditolak','cancelled'=>'Dibatalkan'];
        $badge = $badges[$request->status] ?? 'bg-gray-100 text-gray-500';
        $l = $labels[$request->status] ?? $request->status;
    @endphp

    {{-- Status header --}}
    <div class="bg-white rounded-2xl border border-green-100 p-6 mb-6 shadow-sm">
        <div class="flex items-center justify-between mb-5">
            <h1 class="text-xl font-bold text-[#1B5E20]">Detail Tantangan</h1>
            <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $badge }}">{{ $l }}</span>
        </div>

        {{-- Teams --}}
        <div class="flex items-center justify-center gap-6">
            <div class="text-center flex-1">
                <div class="w-16 h-16 rounded-2xl bg-green-100 flex items-center justify-center text-[#2E7D32] font-black text-2xl mx-auto mb-2">
                    {{ strtoupper(substr($request->requesterTeam->name, 0, 1)) }}
                </div>
                <p class="font-bold text-gray-900">{{ $request->requesterTeam->name }}</p>
                <p class="text-xs text-gray-400">{{ $request->requesterTeam->city }}</p>
                @if($request->requesterTeam->teamStats)
                    <p class="text-xs text-gray-500 mt-1">{{ $request->requesterTeam->teamStats->total_matches }} M · {{ $request->requesterTeam->teamStats->wins }} W</p>
                @endif
            </div>

            <div class="text-center">
                <div class="text-2xl font-black text-gray-300">VS</div>
                <p class="text-xs text-gray-400 mt-1">{{ \Carbon\Carbon::parse($request->preferred_date)->translatedFormat('d M Y') }}</p>
            </div>

            <div class="text-center flex-1">
                <div class="w-16 h-16 rounded-2xl bg-blue-100 flex items-center justify-center text-blue-600 font-black text-2xl mx-auto mb-2">
                    {{ strtoupper(substr($request->opponentTeam->name, 0, 1)) }}
                </div>
                <p class="font-bold text-gray-900">{{ $request->opponentTeam->name }}</p>
                <p class="text-xs text-gray-400">{{ $request->opponentTeam->city }}</p>
                @if($request->opponentTeam->teamStats)
                    <p class="text-xs text-gray-500 mt-1">{{ $request->opponentTeam->teamStats->total_matches }} M · {{ $request->opponentTeam->teamStats->wins }} W</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Match info (if accepted) --}}
    @if($request->futsalMatch)
    <div class="bg-white rounded-2xl border border-green-100 p-6 mb-6 shadow-sm">
        <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-4">Info Pertandingan</h2>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-400">Lapangan</p>
                <p class="text-gray-900 font-medium mt-0.5">{{ $request->futsalMatch->venue->name }}</p>
                <p class="text-xs text-gray-400">{{ $request->futsalMatch->venue->address }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400">Tanggal & Waktu</p>
                <p class="text-gray-900 font-medium mt-0.5">{{ \Carbon\Carbon::parse($request->futsalMatch->match_date)->translatedFormat('d M Y') }}</p>
                <p class="text-xs text-gray-500">{{ $request->futsalMatch->start_time }} ({{ $request->futsalMatch->duration_minutes }} menit)</p>
            </div>
        </div>

        @if($request->futsalMatch->matchCost)
        <div class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-3 gap-4">
            <div class="text-center bg-gray-50 rounded-xl py-3">
                <p class="text-xs text-gray-400">Total Biaya</p>
                <p class="text-gray-900 font-bold mt-1 text-sm">Rp {{ number_format($request->futsalMatch->matchCost->total_cost, 0, ',', '.') }}</p>
            </div>
            <div class="text-center bg-green-50 rounded-xl py-3">
                <p class="text-xs text-gray-400">Per Tim</p>
                <p class="text-green-700 font-bold mt-1 text-sm">Rp {{ number_format($request->futsalMatch->matchCost->cost_per_team, 0, ',', '.') }}</p>
            </div>
            <div class="text-center bg-blue-50 rounded-xl py-3">
                <p class="text-xs text-gray-400">Per Pemain</p>
                <p class="text-blue-700 font-bold mt-1 text-sm">Rp {{ number_format($request->futsalMatch->matchCost->cost_per_player, 0, ',', '.') }}</p>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- Actions for pending incoming requests --}}
    @php
        $myTeamIds = auth()->user()->ownedTeams()->pluck('id');
        $isOpponent = $myTeamIds->contains($request->opponent_team_id);
    @endphp

    @if($request->status === 'pending' && $isOpponent)
    <div class="flex gap-3">
        <form method="POST" action="{{ route('match.accept', $request->id) }}" class="flex-1">
            @csrf
            <button type="submit" class="w-full py-3 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl transition">
                Terima Tantangan
            </button>
        </form>
        <form method="POST" action="{{ route('match.reject', $request->id) }}" class="flex-1">
            @csrf
            <button type="submit" class="w-full py-3 bg-red-50 hover:bg-red-100 text-red-600 font-bold rounded-xl border border-red-200 transition">
                Tolak
            </button>
        </form>
    </div>
    @endif

    @if($request->status === 'accepted' && !$request->futsalMatch)
    <div class="mt-4">
        <a href="{{ route('venue.index') }}"
           class="block w-full text-center py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition">
            Pilih & Booking Lapangan
        </a>
    </div>
    @endif

</div>
</div>
@endsection
