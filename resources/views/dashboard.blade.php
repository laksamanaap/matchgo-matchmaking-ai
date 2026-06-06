@extends('layouts.app')

@section('content')
@php
    $user = Auth::user();
    $team = $user->team;

    if ($team) {
        $team->loadMissing(['owner', 'players']);
    }

    $now = now();
    $activePlayerCount = $team?->activePlayerCount() ?? 0;
    $canUseMatchFeatures = $team?->hasMinimumPlayers() ?? false;
    $teamSetupRoute = route('teams.index');

    $allMatches = $team
        ? \App\Models\FutsalMatch::query()
            ->where(fn ($query) => $query
                ->where('team_a_id', $team->id)
                ->orWhere('team_b_id', $team->id))
            ->with(['teamA', 'teamB', 'field', 'booking', 'matchCost'])
            ->latest('match_date')
            ->latest('start_time')
            ->get()
        : collect();

    $upcomingMatches = $allMatches
        ->filter(fn ($match) => ! in_array($match->status, ['completed', 'cancelled'], true))
        ->values();

    $nextMatch = $upcomingMatches
        ->sortBy(fn ($match) => $match->match_date->format('Y-m-d').' '.\Carbon\Carbon::parse($match->start_time)->format('H:i:s'))
        ->first();

    $recentMatches = $allMatches->take(5);
    $completedCount = $allMatches->where('status', 'completed')->count();
    $cancelledCount = $allMatches->where('status', 'cancelled')->count();

    $myChallenges = $team
        ? \App\Models\FutsalMatch::query()
            ->where('team_a_id', $team->id)
            ->whereNull('team_b_id')
            ->where('status', 'scheduled')
            ->whereDate('match_date', '>=', $now->toDateString())
            ->with(['field', 'matchCost'])
            ->orderBy('match_date')
            ->orderBy('start_time')
            ->get()
        : collect();

    $openChallenges = $team
        ? \App\Models\FutsalMatch::query()
            ->whereNull('team_b_id')
            ->where('team_a_id', '!=', $team->id)
            ->where('status', 'scheduled')
            ->whereDate('match_date', '>=', $now->toDateString())
            ->whereHas('teamA', fn ($query) => $query
                ->where('verification_status', 'verified')
                ->has('players', '>=', 4))
            ->with(['teamA', 'field', 'booking', 'matchCost'])
            ->orderBy('match_date')
            ->orderBy('start_time')
            ->get()
        : collect();

    $todayLabel = \Carbon\Carbon::now('Asia/Jakarta')->locale('id')->translatedFormat('l, d F Y');
    $verificationStyle = match ($team?->verification_status) {
        'verified' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
        'rejected' => 'bg-red-100 text-red-700 ring-red-200',
        'pending' => 'bg-amber-100 text-amber-700 ring-amber-200',
        default => 'bg-slate-100 text-slate-700 ring-slate-200',
    };
@endphp

<div class="min-h-screen bg-[#F1F8E9] text-[#0B5D1E]">
    <x-navbar />

    <main class="mx-auto max-w-7xl px-4 pb-12 pt-24 sm:px-6 lg:px-8">
        <section class="mb-6 grid gap-6 lg:grid-cols-[1.35fr_0.65fr]">
            <div class="overflow-hidden rounded-3xl border border-[#DDEED8] bg-white shadow-xl shadow-[#1B5E20]/10">
                <div class="p-6 sm:p-8">
                    <p class="text-sm font-black uppercase tracking-[0.16em] text-[#4B8B43]">{{ $todayLabel }}</p>
                    <div class="mt-4 flex flex-col gap-5 md:flex-row md:items-end md:justify-between">
                        <div>
                            <h1 class="text-4xl font-black leading-tight sm:text-5xl">
                                Halo, {{ $user->name }}
                            </h1>
                            <p class="mt-3 max-w-2xl text-[#4B8B43]">
                                Pantau kesiapan tim, jadwal pertandingan, dan peluang match dari satu tempat.
                            </p>
                        </div>

                        <div class="flex flex-col gap-3 sm:flex-row">
                            <a href="{{ $canUseMatchFeatures ? route('matches.create') : $teamSetupRoute }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#2E8B3C] px-5 py-3 text-sm font-black text-white shadow-md shadow-[#1B5E20]/15 transition hover:bg-[#23742F]">
                                <x-heroicon-o-plus class="h-5 w-5" />
                                Buat Match
                            </a>
                            <a href="{{ $canUseMatchFeatures ? route('matches.auto') : $teamSetupRoute }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border border-[#C8E6C9] bg-[#F8FCF4] px-5 py-3 text-sm font-black text-[#2E7D32] transition hover:bg-[#F1F8E9]">
                                <x-heroicon-o-bolt class="h-5 w-5" />
                                AutoMatching
                            </a>
                        </div>
                    </div>
                </div>

                <div class="grid border-t border-[#DDEED8] bg-[#F8FCF4] sm:grid-cols-4">
                    <div class="border-b border-[#DDEED8] p-5 sm:border-b-0 sm:border-r">
                        <p class="text-xs font-bold text-[#4B8B43]">Pemain Aktif</p>
                        <p class="mt-2 text-3xl font-black">{{ $activePlayerCount }}</p>
                    </div>
                    <div class="border-b border-[#DDEED8] p-5 sm:border-b-0 sm:border-r">
                        <p class="text-xs font-bold text-[#4B8B43]">Upcoming</p>
                        <p class="mt-2 text-3xl font-black">{{ $upcomingMatches->count() }}</p>
                    </div>
                    <div class="border-b border-[#DDEED8] p-5 sm:border-b-0 sm:border-r">
                        <p class="text-xs font-bold text-[#4B8B43]">Selesai</p>
                        <p class="mt-2 text-3xl font-black">{{ $completedCount }}</p>
                    </div>
                    <div class="p-5">
                        <p class="text-xs font-bold text-[#4B8B43]">Tantangan</p>
                        <p class="mt-2 text-3xl font-black">{{ $myChallenges->count() + $openChallenges->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-xl shadow-[#1B5E20]/10">
                @if($team)
                    <div class="flex items-center gap-4">
                        <div class="h-20 w-20 shrink-0 overflow-hidden rounded-3xl border border-[#C8E6C9] bg-[#E4F2DE]">
                            @php
                                $teamLogoExists = $team->logo_url && \Illuminate\Support\Facades\Storage::disk('public')->exists($team->logo_url);
                            @endphp
                            @if($teamLogoExists)
                                <img src="{{ asset('storage/' . $team->logo_url) }}" alt="{{ $team->name }}" class="h-full w-full object-contain p-2">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-3xl font-black text-[#2E7D32]">
                                    {{ strtoupper(substr($team->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-[#4B8B43]">Tim Aktif</p>
                            <h2 class="truncate text-2xl font-black">{{ $team->name }}</h2>
                            <span class="mt-2 inline-flex rounded-full px-3 py-1 text-xs font-black ring-1 {{ $verificationStyle }}">
                                {{ ucfirst($team->verification_status) }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-3">
                        <div class="flex items-center justify-between rounded-2xl bg-[#F1F8E9] px-4 py-3">
                            <span class="text-sm font-bold text-[#4B8B43]">Level</span>
                            <strong class="capitalize">{{ str_replace('_', ' ', $team->skill_level) }}</strong>
                        </div>
                    </div>

                    <a href="{{ route('teams.index') }}" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-2xl border border-[#C8E6C9] bg-[#F8FCF4] px-5 py-3 text-sm font-black text-[#2E7D32] transition hover:bg-[#F1F8E9]">
                        <x-heroicon-o-users class="h-5 w-5" />
                        Kelola Tim
                    </a>
                @else
                    <div class="rounded-2xl bg-[#F1F8E9] p-5">
                        <p class="text-2xl font-black">Belum ada tim</p>
                        <p class="mt-2 text-sm font-semibold text-[#4B8B43]">Buat tim untuk membuka fitur pertandingan dan AutoMatching.</p>
                        <a href="{{ route('teams.index') }}" class="mt-5 inline-flex rounded-2xl bg-[#2E8B3C] px-5 py-3 text-sm font-black text-white">
                            Buat Tim
                        </a>
                    </div>
                @endif
            </div>
        </section>

        @if($team && ! $canUseMatchFeatures)
            <div class="mb-6 rounded-3xl border border-amber-200 bg-amber-50 p-5 text-amber-800 shadow-sm">
                <div class="flex gap-3">
                    <x-heroicon-o-exclamation-triangle class="h-6 w-6 shrink-0" />
                    <p class="text-sm font-bold">Minimal 5 pemain diperlukan untuk membuat pertandingan dan memakai AutoMatching. Tambahkan pemain di halaman tim.</p>
                </div>
            </div>
        @endif

        <section class="mb-6 grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
            <div class="rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-xl shadow-[#1B5E20]/10">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.16em] text-[#4B8B43]">Jadwal</p>
                        <h2 class="mt-1 text-2xl font-black">Match Berikutnya</h2>
                    </div>
                    <x-heroicon-o-calendar-days class="h-8 w-8 text-[#43A047]" />
                </div>

                @if($nextMatch)
                    @php
                        $nextOpponent = $nextMatch->team_a_id === $team->id ? $nextMatch->teamB : $nextMatch->teamA;
                    @endphp
                    <div class="rounded-2xl bg-[#F1F8E9] p-5">
                        <p class="text-sm font-bold text-[#4B8B43]">{{ $nextMatch->match_date->format('d M Y') }} pukul {{ \Carbon\Carbon::parse($nextMatch->start_time)->format('H:i') }}</p>
                        <h3 class="mt-2 text-2xl font-black">vs {{ $nextOpponent?->name ?? 'Menunggu Lawan' }}</h3>
                        <p class="mt-1 text-sm font-semibold text-[#4B8B43]">{{ $nextMatch->field?->name ?? 'Lapangan belum dipilih' }}</p>
                        <div class="mt-5 flex flex-col gap-2 sm:flex-row">
                            <a href="{{ route('matches.show', $nextMatch) }}" class="inline-flex flex-1 items-center justify-center rounded-xl bg-white px-4 py-2 text-sm font-black text-[#2E7D32] ring-1 ring-[#C8E6C9]">Detail</a>
                            <a href="{{ route('matches.history') }}" class="inline-flex flex-1 items-center justify-center rounded-xl bg-[#2E8B3C] px-4 py-2 text-sm font-black text-white">Riwayat</a>
                        </div>
                    </div>
                @else
                    <div class="rounded-2xl bg-[#F1F8E9] p-6 text-center">
                        <p class="font-bold text-[#4B8B43]">Belum ada jadwal aktif.</p>
                        <a href="{{ $canUseMatchFeatures ? route('matches.create') : $teamSetupRoute }}" class="mt-4 inline-flex rounded-xl bg-[#2E8B3C] px-4 py-2 text-sm font-black text-white">Buat Jadwal</a>
                    </div>
                @endif
            </div>

            <div class="rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-xl shadow-[#1B5E20]/10">
                <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.16em] text-[#4B8B43]">Match Center</p>
                        <h2 class="mt-1 text-2xl font-black">Peluang pertandingan</h2>
                    </div>
                    <a href="{{ route('matches.take') }}" class="inline-flex items-center justify-center rounded-xl bg-[#F1F8E9] px-4 py-2 text-sm font-black text-[#2E7D32] ring-1 ring-[#C8E6C9]">Lihat Semua</a>
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="font-black">Tantangan tersedia</h3>
                            <span class="rounded-full bg-sky-100 px-3 py-1 text-xs font-black text-sky-700">{{ $openChallenges->count() }}</span>
                        </div>
                        <div class="grid gap-3">
                            @forelse($openChallenges->take(3) as $challenge)
                                <a href="{{ route('matches.show', $challenge) }}" class="rounded-2xl border border-[#DDEED8] bg-[#F8FCF4] p-4 transition hover:bg-[#F1F8E9]">
                                    <p class="font-black">{{ $challenge->teamA->name }}</p>
                                    <p class="mt-1 text-sm font-semibold text-[#4B8B43]">{{ $challenge->match_date->format('d M Y') }} - {{ \Carbon\Carbon::parse($challenge->start_time)->format('H:i') }}</p>
                                    <p class="text-sm text-[#4B8B43]">{{ $challenge->field?->name ?? 'Lapangan belum dipilih' }}</p>
                                </a>
                            @empty
                                <div class="rounded-2xl bg-[#F1F8E9] p-5 text-sm font-bold text-[#4B8B43]">Belum ada tantangan dari tim lain.</div>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <div class="mb-3 flex items-center justify-between">
                            <h3 class="font-black">Tantangan kamu</h3>
                            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-black text-amber-700">{{ $myChallenges->count() }}</span>
                        </div>
                        <div class="grid gap-3">
                            @forelse($myChallenges->take(3) as $challenge)
                                <div class="rounded-2xl border border-[#DDEED8] bg-[#F8FCF4] p-4">
                                    <p class="font-black">{{ $challenge->field?->name ?? 'Lapangan belum dipilih' }}</p>
                                    <p class="mt-1 text-sm font-semibold text-[#4B8B43]">{{ $challenge->match_date->format('d M Y') }} - {{ \Carbon\Carbon::parse($challenge->start_time)->format('H:i') }}</p>
                                    <div class="mt-3 flex gap-2">
                                        <a href="{{ route('matches.show', $challenge) }}" class="rounded-xl bg-white px-3 py-2 text-xs font-black text-[#2E7D32] ring-1 ring-[#C8E6C9]">Detail</a>
                                        <form method="POST" action="{{ route('matches.cancel', $challenge) }}" onsubmit="return confirm('Batalkan tantangan ini?')">
                                            @csrf
                                            <button type="submit" class="rounded-xl bg-red-50 px-3 py-2 text-xs font-black text-red-600 ring-1 ring-red-100">Batalkan</button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-2xl bg-[#F1F8E9] p-5 text-sm font-bold text-[#4B8B43]">Belum ada tantangan aktif.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[0.85fr_1.15fr]">
            <div class="rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-xl shadow-[#1B5E20]/10">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.16em] text-[#4B8B43]">Squad</p>
                        <h2 class="mt-1 text-2xl font-black">Anggota Tim</h2>
                    </div>
                    @if($team)
                        <a href="{{ route('teams.index') }}" class="rounded-xl bg-[#F1F8E9] px-4 py-2 text-sm font-black text-[#2E7D32] ring-1 ring-[#C8E6C9]">Kelola</a>
                    @endif
                </div>

                @if(! $team)
                    <div class="rounded-2xl bg-[#F1F8E9] p-6 text-center text-sm font-bold text-[#4B8B43]">Buat tim terlebih dahulu untuk melihat anggota.</div>
                @else
                    <div class="mb-3 rounded-2xl bg-[#F1F8E9] p-4">
                        <p class="text-xs font-bold text-[#4B8B43]">Kapten</p>
                        <p class="mt-1 text-lg font-black">{{ $team->owner->name }}</p>
                        <p class="text-sm text-[#4B8B43]">{{ $team->owner->email }}</p>
                    </div>

                    <div class="grid gap-3">
                        @forelse($team->players->take(6) as $player)
                            <div class="flex items-center justify-between rounded-2xl border border-[#DDEED8] px-4 py-3">
                                <div>
                                    <p class="font-black">{{ $player->player_name }}</p>
                                    <p class="text-sm text-[#4B8B43]">{{ $player->position }}{{ $player->age ? ' - '.$player->age.' tahun' : '' }}</p>
                                </div>
                                <x-heroicon-o-user class="h-5 w-5 text-[#43A047]" />
                            </div>
                        @empty
                            <div class="rounded-2xl bg-[#F1F8E9] p-5 text-sm font-bold text-[#4B8B43]">Belum ada pemain yang ditambahkan.</div>
                        @endforelse
                    </div>
                @endif
            </div>

            <div class="rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-xl shadow-[#1B5E20]/10">
                <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.16em] text-[#4B8B43]">Riwayat</p>
                        <h2 class="mt-1 text-2xl font-black">Pertandingan terbaru</h2>
                    </div>
                    <a href="{{ route('matches.history') }}" class="inline-flex items-center justify-center rounded-xl bg-[#F1F8E9] px-4 py-2 text-sm font-black text-[#2E7D32] ring-1 ring-[#C8E6C9]">History</a>
                </div>

                @if(! $team)
                    <div class="rounded-2xl bg-[#F1F8E9] p-6 text-center text-sm font-bold text-[#4B8B43]">Buat tim terlebih dahulu untuk melihat history pertandingan.</div>
                @elseif($recentMatches->count() > 0)
                    <div class="grid gap-3">
                        @foreach($recentMatches as $match)
                            @php
                                $opponent = $match->team_a_id === $team->id ? $match->teamB : $match->teamA;
                                $teamScore = $match->team_a_id === $team->id ? $match->score_a : $match->score_b;
                                $opponentScore = $match->team_a_id === $team->id ? $match->score_b : $match->score_a;
                                $statusStyle = match ($match->status) {
                                    'completed' => 'bg-emerald-100 text-emerald-700',
                                    'cancelled' => 'bg-red-100 text-red-700',
                                    default => 'bg-sky-100 text-sky-700',
                                };
                            @endphp
                            <a href="{{ route('matches.show', $match) }}" class="grid gap-3 rounded-2xl border border-[#DDEED8] bg-[#F8FCF4] p-4 transition hover:bg-[#F1F8E9] md:grid-cols-[1fr_auto] md:items-center">
                                <div>
                                    <p class="text-sm font-bold text-[#4B8B43]">{{ $match->match_date->format('d M Y') }} pukul {{ \Carbon\Carbon::parse($match->start_time)->format('H:i') }}</p>
                                    <p class="mt-1 text-lg font-black">{{ $team->name }} vs {{ $opponent?->name ?? 'Menunggu Lawan' }}</p>
                                    <p class="text-sm text-[#4B8B43]">{{ $match->field?->name ?? 'Lapangan belum dipilih' }}</p>
                                </div>
                                <div class="flex items-center gap-2 md:justify-end">
                                    <span class="rounded-xl bg-white px-4 py-2 text-sm font-black text-[#0B5D1E] ring-1 ring-[#DDEED8]">
                                        {{ $teamScore !== null && $opponentScore !== null ? $teamScore.' - '.$opponentScore : '-' }}
                                    </span>
                                    <span class="rounded-xl px-4 py-2 text-sm font-black {{ $statusStyle }}">{{ ucfirst($match->status) }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-2xl bg-[#F1F8E9] p-6 text-center text-sm font-bold text-[#4B8B43]">Belum ada history pertandingan.</div>
                @endif
            </div>
        </section>
    </main>
</div>
@endsection
