@extends('layouts.app')

@section('title', $team->name)

@section('content')
<div class="bg-[#F1F8E9] min-h-screen">
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <a href="{{ route('discover.index') }}" class="flex items-center gap-1.5 text-gray-500 hover:text-[#2E7D32] text-sm transition mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Cari Lawan
    </a>

    {{-- Team header --}}
    <div class="bg-white border border-green-100 rounded-2xl p-6 mb-6 shadow-sm">
        <div class="flex items-start gap-5">
            <div class="w-20 h-20 rounded-2xl bg-green-100 flex items-center justify-center text-[#2E7D32] font-black text-3xl shrink-0">
                {{ strtoupper(substr($team->name, 0, 1)) }}
            </div>
            <div class="flex-1">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h1 class="text-2xl font-black text-[#1B5E20]">{{ $team->name }}</h1>
                        <p class="text-gray-500 mt-1 flex items-center gap-1.5 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            {{ $team->city }}
                        </p>
                    </div>
                    @php
                        $levelBadge = [
                            'casual'      => 'bg-green-100 text-green-700',
                            'semi_pro'    => 'bg-amber-100 text-amber-700',
                            'competitive' => 'bg-red-100 text-red-600',
                        ];
                        $levelLabels = ['casual'=>'Kasual','semi_pro'=>'Semi Pro','competitive'=>'Kompetitif'];
                    @endphp
                    <span class="text-sm px-3 py-1.5 rounded-full font-semibold {{ $levelBadge[$team->skill_level] ?? 'bg-gray-100 text-gray-500' }}">
                        {{ $levelLabels[$team->skill_level] ?? $team->skill_level }}
                    </span>
                </div>

                {{-- Stats --}}
                @if($team->teamStats)
                <div class="mt-4 flex gap-6">
                    <div>
                        <p class="text-xl font-black text-gray-800">{{ $team->teamStats->total_matches }}</p>
                        <p class="text-xs text-gray-400">Pertandingan</p>
                    </div>
                    <div>
                        <p class="text-xl font-black text-green-600">{{ $team->teamStats->wins }}</p>
                        <p class="text-xs text-gray-400">Menang</p>
                    </div>
                    <div>
                        <p class="text-xl font-black text-red-500">{{ $team->teamStats->losses }}</p>
                        <p class="text-xs text-gray-400">Kalah</p>
                    </div>
                    <div>
                        <p class="text-xl font-black text-gray-500">{{ $team->teamStats->draws }}</p>
                        <p class="text-xs text-gray-400">Seri</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        {{-- Schedule --}}
        @if($team->teamSchedules->count())
        <div class="bg-white border border-green-100 rounded-2xl p-5 shadow-sm">
            <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-4">Jadwal Aktif</h2>
            @php $days = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] @endphp
            <div class="space-y-2">
                @foreach($team->teamSchedules->where('is_active', true) as $sched)
                <div class="flex items-center justify-between py-2 border-b border-gray-50 last:border-0">
                    <span class="text-gray-800 font-medium text-sm">{{ $days[$sched->day_of_week] ?? 'N/A' }}</span>
                    <span class="text-gray-500 text-sm">{{ substr($sched->start_time, 0, 5) }} – {{ substr($sched->end_time, 0, 5) }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Members --}}
        <div class="bg-white border border-green-100 rounded-2xl p-5 shadow-sm">
            <h2 class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-4">
                Anggota Tim ({{ $team->teamMembers->count() }})
            </h2>
            <div class="space-y-2">
                @foreach($team->teamMembers->take(6) as $member)
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center text-xs font-bold text-[#2E7D32]">
                        {{ strtoupper(substr($member->user->name, 0, 1)) }}
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-800">{{ $member->user->name }}</p>
                    </div>
                    @if($member->role === 'captain')
                    <span class="text-xs px-1.5 py-0.5 bg-amber-100 text-amber-700 rounded font-medium">Kapten</span>
                    @endif
                </div>
                @endforeach
                @if($team->teamMembers->count() > 6)
                <p class="text-xs text-gray-400 pt-1">+{{ $team->teamMembers->count() - 6 }} lainnya</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Challenge form --}}
    <div class="mt-6 bg-white border border-green-100 rounded-2xl p-6 shadow-sm">
        <h2 class="text-lg font-bold text-[#1B5E20] mb-4">Tantang Tim Ini</h2>

        @if($alreadyChallenged)
        <div class="flex items-center gap-3 p-4 bg-amber-50 border border-amber-200 rounded-xl text-amber-700 text-sm">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            Kamu sudah mengirim tantangan ke tim ini yang masih aktif.
        </div>
        @elseif($myTeams->isEmpty())
        <p class="text-gray-500 text-sm">
            Tim kamu belum terverifikasi.
            <a href="/app" class="text-[#4CAF50] hover:underline font-medium">Kelola tim</a> untuk bisa menantang.
        </p>
        @else
        <form method="POST" action="{{ route('match.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Dari Tim</label>
                <select name="requester_team_id"
                        class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 transition">
                    @foreach($myTeams as $myTeam)
                    <option value="{{ $myTeam->id }}">{{ $myTeam->name }}</option>
                    @endforeach
                </select>
            </div>

            <input type="hidden" name="opponent_team_id" value="{{ $team->id }}">

            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Tanggal Preferensi</label>
                <input type="date" name="preferred_date" min="{{ date('Y-m-d') }}"
                       class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 transition"
                       required>
            </div>

            <button type="submit"
                    class="w-full py-3.5 bg-gradient-to-r from-[#2E7D32] to-[#4CAF50] hover:from-[#1B5E20] hover:to-[#2E7D32] text-white font-bold rounded-xl shadow-sm transition-all duration-200">
                Kirim Tantangan ke {{ $team->name }}
            </button>
        </form>
        @endif
    </div>

</div>
</div>
@endsection
