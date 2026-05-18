@extends('layouts.app')

@section('content')
@php
    $user = Auth::user();
    $team = $user->team;
    $teamMatchesCount = $team
        ? $team->matchesAsTeamA()->count() + $team->matchesAsTeamB()->count()
        : 0;
    $recentMatches = $team
        ? \App\Models\FutsalMatch::query()
            ->where(function ($query) use ($team) {
                $query->where('team_a_id', $team->id)
                    ->orWhere('team_b_id', $team->id);
            })
            ->with(['teamA', 'teamB', 'field'])
            ->latest('match_date')
            ->latest('start_time')
            ->take(5)
            ->get()
        : collect();
    $myChallenges = $team
        ? \App\Models\FutsalMatch::query()
            ->where('team_a_id', $team->id)
            ->whereNull('team_b_id')
            ->where('status', 'scheduled')
            ->with(['field', 'matchCost'])
            ->latest('match_date')
            ->latest('start_time')
            ->get()
        : collect();
    $openChallenges = $team
        ? \App\Models\FutsalMatch::query()
            ->whereNull('team_b_id')
            ->where('team_a_id', '!=', $team->id)
            ->where('status', 'scheduled')
            ->whereDate('match_date', '>=', now()->toDateString())
            ->whereHas('teamA', fn ($query) => $query
                ->where('verification_status', 'verified')
                ->has('players', '>=', 4))
            ->with(['teamA', 'field', 'booking', 'matchCost'])
            ->orderBy('match_date')
            ->orderBy('start_time')
            ->get()
        : collect();

    if ($team) {
        $team->loadMissing(['owner', 'players']);
    }

    $canUseMatchFeatures = $team?->activePlayerCount() >= 5;
    $teamSetupRoute = $team ? route('teams.show', $team) : route('teams.index');
    $todayLabel = \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y');
@endphp

<div class="min-h-screen bg-[#F1F8E9]">
    <x-navbar />

    <div class="pt-24 pb-12 container mx-auto px-6">
        <div class="mb-8 overflow-hidden rounded-3xl border border-[#DDEED8] bg-white shadow-xl shadow-[#1B5E20]/10">
            <div class="grid gap-0 lg:grid-cols-[1.4fr_0.9fr]">
                <div class="p-8 sm:p-10">
                    <p class="text-sm font-bold text-[#4B8B43]">{{ $todayLabel }}</p>
                    <h1 class="mt-3 text-4xl font-black leading-tight text-[#0B5D1E] sm:text-5xl">
                        Halo, {{ $user->name }}
                    </h1>
                </div>

                <div class="border-t border-[#DDEED8] bg-[#F8FCF4] p-6 sm:p-8 lg:border-l lg:border-t-0">
                    @if($team)
                        <div class="flex items-center gap-6">
                            <div class="h-28 w-28 overflow-hidden rounded-3xl border border-[#C8E6C9] bg-white shadow-sm">
                                @if($team->logo_url)
                                    <img src="{{ asset('storage/' . $team->logo_url) }}" alt="{{ $team->name }}" class="h-full w-full object-cover">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-4xl font-black text-[#2E7D32]">
                                        {{ strtoupper(substr($team->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-bold text-[#4B8B43]">Tim Aktif</p>
                                <p class="text-2xl font-black text-[#0B5D1E]">{{ $team->name }}</p>
                                <span class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-black text-white {{ $team->verification_status === 'verified' ? 'bg-[#22C55E]' : 'bg-yellow-500' }}">
                                    {{ ucfirst($team->verification_status) }}
                                </span>
                            </div>
                        </div>
                    @else
                        <div class="rounded-2xl border border-[#C8E6C9] bg-white p-5">
                            <p class="text-xl font-black text-[#0B5D1E]">Belum ada tim</p>
                            <a href="{{ route('teams.index') }}" class="mt-4 inline-flex rounded-2xl bg-[#2E8B3C] px-4 py-2 text-sm font-black text-white">
                                Buat Tim
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="mb-8 grid gap-4 md:grid-cols-3">
            <div class="rounded-2xl bg-white p-6 shadow">
                <p class="text-sm text-[#2E7D32]/70">Pemain</p>
                <p class="mt-2 text-3xl font-bold text-[#1B5E20]">{{ $team?->activePlayerCount() ?? 0 }}</p>
            </div>
            <div class="rounded-2xl bg-white p-6 shadow">
                <p class="text-sm text-[#2E7D32]/70">Pertandingan</p>
                <p class="mt-2 text-3xl font-bold text-[#1B5E20]">{{ $teamMatchesCount }}</p>
            </div>
            <div class="rounded-2xl bg-white p-6 shadow">
                <p class="text-sm text-[#2E7D32]/70">Notifikasi</p>
                <p class="mt-2 text-3xl font-bold text-[#1B5E20]">{{ $user->unreadNotifications()->count() }}</p>
            </div>
        </div>

        @if($team)
            <div class="mb-8 rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-xl shadow-[#1B5E20]/10 sm:p-8">
                <div class="mb-6">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#4B8B43]">Match Center</p>
                        <h2 class="mt-2 text-3xl font-black text-[#0B5D1E]">Pertandingan tim kamu</h2>
                        <p class="mt-1 text-[#4B8B43]">Buat tantangan, ambil jadwal dari tim lain, atau cari lawan otomatis.</p>
                    </div>
                </div>

                <div class="mb-6 grid gap-4 md:grid-cols-3">
                    <div class="rounded-2xl bg-[#F1F8E9] p-5">
                        <p class="text-sm text-[#4B8B43]">Tantangan Kamu</p>
                        <p class="mt-2 text-3xl font-black text-[#0B5D1E]">{{ $myChallenges->count() }}</p>
                    </div>
                    <div class="rounded-2xl bg-[#F1F8E9] p-5">
                        <p class="text-sm text-[#4B8B43]">Tantangan Tersedia</p>
                        <p class="mt-2 text-3xl font-black text-[#0B5D1E]">{{ $openChallenges->count() }}</p>
                    </div>
                    <div class="rounded-2xl bg-[#F1F8E9] p-5">
                        <p class="text-sm text-[#4B8B43]">Match Terbaru</p>
                        <p class="mt-2 text-3xl font-black text-[#0B5D1E]">{{ $recentMatches->count() }}</p>
                    </div>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <div>
                        <div class="mb-4 flex items-end justify-between gap-4">
                            <div>
                                <h3 class="text-xl font-black text-[#0B5D1E]">Tantangan Dari Tim Lain</h3>
                                <p class="text-sm text-[#4B8B43]">Ambil jadwal yang cocok.</p>
                            </div>
                            <a href="{{ route('matches.take') }}" class="shrink-0 rounded-full bg-[#F1F8E9] px-4 py-1.5 text-sm font-bold text-[#2E7D32] ring-1 ring-[#C8E6C9] transition hover:bg-[#E4F2DE]">Lihat semua</a>
                        </div>

                        @if($openChallenges->count() > 0)
                            <div class="grid gap-3">
                                @foreach($openChallenges->take(3) as $challenge)
                                    <div class="rounded-2xl border border-[#DDEED8] bg-white p-4 transition hover:bg-[#F8FCF4]">
                                        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                                            <div>
                                                <p class="font-black text-[#0B5D1E]">{{ $challenge->teamA->name }}</p>
                                                <p class="text-sm text-[#4B8B43]">{{ $challenge->match_date->format('d M Y') }} pukul {{ \Carbon\Carbon::parse($challenge->start_time)->format('H:i') }}</p>
                                                <p class="text-sm text-[#4B8B43]">{{ $challenge->field?->name ?? 'Lapangan belum dipilih' }}</p>
                                            </div>
                                            <a href="{{ route('matches.show', $challenge) }}" class="rounded-xl bg-[#F1F8E9] px-4 py-2 text-center text-sm font-bold text-[#2E7D32]">
                                                Detail
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-2xl border border-[#C8E6C9] bg-[#F1F8E9] p-6 text-center text-[#4B8B43]">
                                Belum ada tantangan terbuka dari tim lain.
                            </div>
                        @endif
                    </div>

                    <div>
                        <div class="mb-4 flex items-end justify-between gap-4">
                            <div>
                                <h3 class="text-xl font-black text-[#0B5D1E]">Tantangan Kamu</h3>
                                <p class="text-sm text-[#4B8B43]">Menunggu tim lawan mengambil jadwal.</p>
                            </div>
                            <a href="{{ route('matches.create') }}" class="shrink-0 rounded-full bg-[#F1F8E9] px-4 py-1.5 text-sm font-bold text-[#2E7D32] ring-1 ring-[#C8E6C9] transition hover:bg-[#E4F2DE]">Buat baru</a>
                        </div>

                        @if($myChallenges->count() > 0)
                            <div class="grid gap-3">
                                @foreach($myChallenges->take(3) as $challenge)
                                    <div class="rounded-2xl bg-[#F1F8E9] p-4">
                                        <p class="font-black text-[#0B5D1E]">{{ $challenge->field?->name ?? 'Lapangan' }}</p>
                                        <p class="text-sm text-[#4B8B43]">{{ $challenge->match_date->format('d M Y') }} pukul {{ \Carbon\Carbon::parse($challenge->start_time)->format('H:i') }}</p>
                                        <div class="mt-3 flex gap-2">
                                            <a href="{{ route('matches.show', $challenge) }}" class="rounded-xl bg-white px-3 py-2 text-sm font-bold text-[#2E7D32]">Detail</a>
                                            <form method="POST" action="{{ route('matches.cancel', $challenge) }}" onsubmit="return confirm('Batalkan tantangan ini?')">
                                                @csrf
                                                <button type="submit" class="rounded-xl bg-red-50 px-3 py-2 text-sm font-bold text-red-600">Batalkan</button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-2xl border border-[#C8E6C9] bg-[#F1F8E9] p-6 text-center text-[#4B8B43]">
                                Belum ada tantangan aktif.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <div class="mb-8 grid gap-6 lg:grid-cols-3">
            <a href="{{ $team ? route('teams.show', $team) : route('teams.index') }}" class="group rounded-3xl border border-[#81C784]/30 bg-white p-6 shadow transition hover:-translate-y-0.5 hover:shadow-lg">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#E8F5E9] text-[#2E7D32]">
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5s-3 1.34-3 3 1.34 3 3 3zM8 11c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                </div>
                <h2 class="text-xl font-bold text-[#1B5E20]">{{ $team ? 'Kelola Tim' : 'Buat Tim' }}</h2>
                <p class="mt-2 text-sm text-[#2E7D32]/80">{{ $team ? 'Lihat profil, logo, dan daftar pemain tim kamu.' : 'Daftarkan tim futsal kamu untuk mulai bermain.' }}</p>
            </a>

            <a href="{{ $canUseMatchFeatures ? route('matches.create') : $teamSetupRoute }}" class="group rounded-3xl border border-[#81C784]/30 bg-white p-6 shadow transition hover:-translate-y-0.5 hover:shadow-lg {{ $canUseMatchFeatures ? '' : 'opacity-75' }}">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#E3F2FD] text-[#1565C0]">
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V9h14v10z"/></svg>
                </div>
                <h2 class="text-xl font-bold text-[#1B5E20]">Buat Pertandingan</h2>
                <p class="mt-2 text-sm text-[#2E7D32]/80">{{ $canUseMatchFeatures ? 'Buat jadwal, pilih lapangan, dan tunggu lawan bergabung.' : 'Minimal 5 pemain diperlukan untuk memakai fitur ini.' }}</p>
            </a>

            <a href="{{ $canUseMatchFeatures ? route('matches.auto') : $teamSetupRoute }}" class="group rounded-3xl border border-[#81C784]/30 bg-white p-6 shadow transition hover:-translate-y-0.5 hover:shadow-lg {{ $canUseMatchFeatures ? '' : 'opacity-75' }}">
                <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-[#FFF8E1] text-[#F57F17]">
                    <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24"><path d="M9.5 3a6.5 6.5 0 0 1 5.18 10.43l4.45 4.44-1.41 1.41-4.44-4.45A6.5 6.5 0 1 1 9.5 3zm0 2a4.5 4.5 0 1 0 0 9 4.5 4.5 0 0 0 0-9z"/></svg>
                </div>
                <h2 class="text-xl font-bold text-[#1B5E20]">AutoMatching</h2>
                <p class="mt-2 text-sm text-[#2E7D32]/80">{{ $canUseMatchFeatures ? 'Cari lawan otomatis dengan level sama dan lapangan netral.' : 'Minimal 5 pemain diperlukan untuk memakai fitur ini.' }}</p>
            </a>
        </div>

        <div class="grid gap-8 xl:grid-cols-[420px_1fr]">
            <div class="rounded-3xl bg-white p-8 shadow-lg">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-[#1B5E20]">Anggota Tim</h2>
                        <p class="text-[#2E7D32]/80">Kapten ikut dihitung sebagai pemain.</p>
                    </div>
                    @if($team)
                        <a href="{{ route('teams.show', $team) }}" class="rounded-2xl border border-[#81C784]/40 px-4 py-2 text-sm font-semibold text-[#2E7D32] hover:bg-[#81C784]/10 transition">Kelola</a>
                    @endif
                </div>

                @if(! $team)
                    <div class="rounded-2xl bg-[#F1F8E9] p-6 text-center text-[#2E7D32]/80">
                        Buat tim terlebih dahulu untuk melihat anggota.
                    </div>
                @else
                    <div class="mb-4 rounded-2xl border border-[#81C784]/30 bg-[#F1F8E9] p-5">
                        <p class="text-sm text-[#2E7D32]/70">Kapten</p>
                        <h3 class="mt-1 text-xl font-bold text-[#1B5E20]">{{ $team->owner->name }}</h3>
                        <p class="text-sm text-[#2E7D32]/80">{{ $team->owner->email }} - Pemain</p>
                    </div>

                    @if($team->players->count() > 0)
                        <div class="space-y-3">
                            @foreach($team->players as $player)
                                <div class="rounded-2xl border border-[#81C784]/20 p-4">
                                    <p class="font-bold text-[#1B5E20]">{{ $player->player_name }}</p>
                                    <p class="text-sm text-[#2E7D32]/80">
                                        {{ $player->position }}@if($player->age) - {{ $player->age }} tahun @endif
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="rounded-2xl bg-[#F1F8E9] p-5 text-[#2E7D32]/80">Belum ada pemain yang ditambahkan.</p>
                    @endif
                @endif
            </div>

            <div class="rounded-3xl bg-white p-8 shadow-lg">
                <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-[#1B5E20]">History Pertandingan</h2>
                        <p class="text-[#2E7D32]/80">Riwayat match terbaru dari tim kamu.</p>
                    </div>
                    <a href="{{ route('matches.auto') }}" class="rounded-2xl border border-[#81C784]/40 px-5 py-3 text-center font-semibold text-[#2E7D32] hover:bg-[#81C784]/10 transition">
                        AutoMatching
                    </a>
                </div>

                @if(! $team)
                    <div class="rounded-2xl bg-[#F1F8E9] p-6 text-center text-[#2E7D32]/80">
                        Buat tim terlebih dahulu untuk melihat history pertandingan.
                    </div>
                @elseif($recentMatches->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentMatches as $match)
                            @php
                                $opponent = $match->team_a_id === $team->id ? $match->teamB : $match->teamA;
                                $teamScore = $match->team_a_id === $team->id ? $match->score_a : $match->score_b;
                                $opponentScore = $match->team_a_id === $team->id ? $match->score_b : $match->score_a;
                            @endphp
                            <div class="flex flex-col gap-4 rounded-2xl border border-[#81C784]/30 bg-[#F1F8E9] p-5 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <p class="text-sm text-[#2E7D32]/70">
                                        {{ $match->match_date->format('d M Y') }} pukul {{ \Carbon\Carbon::parse($match->start_time)->format('H:i') }}
                                    </p>
                                    <h3 class="text-xl font-bold text-[#1B5E20]">
                                        {{ $team->name }} vs {{ $opponent?->name ?? 'Menunggu Lawan' }}
                                    </h3>
                                    <p class="text-sm text-[#2E7D32]/80">{{ $match->field?->name ?? 'Lapangan belum dipilih' }}</p>
                                </div>
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                    <div class="rounded-xl bg-white px-4 py-2 text-center">
                                        <p class="text-xs text-[#2E7D32]/70">Skor</p>
                                        <p class="text-lg font-bold text-[#1B5E20]">
                                            @if($teamScore !== null && $opponentScore !== null)
                                                {{ $teamScore }} - {{ $opponentScore }}
                                            @else
                                                -
                                            @endif
                                        </p>
                                    </div>
                                    <span class="inline-flex justify-center rounded-xl px-4 py-2 text-sm font-semibold text-white {{ $match->status === 'completed' ? 'bg-green-500' : ($match->status === 'cancelled' ? 'bg-red-500' : 'bg-blue-500') }}">
                                        {{ ucfirst($match->status) }}
                                    </span>
                                    <a href="{{ route('matches.show', $match) }}" class="rounded-xl bg-white px-4 py-2 text-center font-semibold text-[#2E7D32] hover:bg-[#81C784]/10 transition">
                                        Detail
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-2xl bg-[#F1F8E9] p-6 text-center text-[#2E7D32]/80">
                        Belum ada history pertandingan.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
