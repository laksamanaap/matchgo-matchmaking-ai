@extends('layouts.app')

@php
    $activeMatch = $latestMatchedQueue?->match;
    $activeOpponent = $activeMatch
        ? ($activeMatch->team_a_id === $team->id ? $activeMatch->teamB : $activeMatch->teamA)
        : null;
    $secondsRemaining = $waitingQueue?->expired_at ? (int) max(0, floor(now()->diffInSeconds($waitingQueue->expired_at, false))) : 300;
@endphp

@section('content')
<div
    id="auto-match-page"
    class="min-h-screen bg-[#F1F8E9] text-[#0B5D1E]"
    data-has-active-queue="{{ $waitingQueue ? 'true' : 'false' }}"
    data-has-active-match="{{ $activeMatch ? 'true' : 'false' }}"
    data-status-url="{{ route('matches.auto_status') }}"
    data-auto-url="{{ route('matches.auto') }}"
>
    <x-navbar />

    <main class="mx-auto flex min-h-screen max-w-6xl flex-col px-4 pb-12 pt-24 sm:px-6">
        <section class="grid flex-1 items-center gap-6 lg:grid-cols-[1.08fr_0.92fr]">
            <div class="relative overflow-hidden rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-xl shadow-[#1B5E20]/10 sm:p-8">
                <div class="absolute inset-0 field-pattern opacity-30"></div>
                <div class="relative">
                    <div class="mb-5 flex flex-wrap items-center gap-3 text-xs font-black uppercase tracking-[0.18em] text-[#2E7D32]">
                        <span class="rounded-full border border-[#C8E6C9] bg-[#F1F8E9] px-3 py-1">Auto Matchmaking</span>
                        <span class="text-[#4B8B43]">Main hari ini</span>
                    </div>

                    <h1 class="max-w-2xl text-4xl font-black leading-tight text-[#0B5D1E] sm:text-6xl">
                        Cari lawan futsal setara. Kick-off otomatis +3 jam.
                    </h1>

                    <p class="mt-4 max-w-xl text-sm leading-6 text-[#4B8B43] sm:text-base">
                        Sistem hanya mempertemukan level yang sama, menghitung radius dari basecamp tim, memilih lapangan terdekat dari midpoint, lalu reserve slot lapangan.
                    </p>

                    <div class="mt-8 grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl bg-[#F1F8E9] p-4">
                            <p class="text-xs text-[#4B8B43]">Level tim</p>
                            <p class="mt-1 text-lg font-black capitalize">{{ str_replace('_', ' ', $team->skill_level) }}</p>
                        </div>
                        <div class="rounded-2xl bg-[#F1F8E9] p-4">
                            <p class="text-xs text-[#4B8B43]">Jadwal</p>
                            <p class="mt-1 text-lg font-black">+3 jam</p>
                        </div>
                        <div class="rounded-2xl bg-[#F1F8E9] p-4">
                            <p class="text-xs text-[#4B8B43]">Durasi</p>
                            <p class="mt-1 text-lg font-black">{{ $autoParams['duration_minutes'] }} menit</p>
                        </div>
                    </div>
                </div>
            </div>

            <aside class="rounded-3xl border border-[#DDEED8] bg-white p-5 shadow-xl shadow-[#1B5E20]/10 sm:p-6">
                @if(! $team->isVerified())
                    <div class="mb-5 rounded-2xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800">
                        Tim kamu belum diverifikasi admin.
                    </div>
                @endif

                @if(! $canUseMatchFeatures)
                    <div class="mb-5 rounded-2xl border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-800">
                        Tim kamu baru memiliki {{ $team->activePlayerCount() }} pemain termasuk captain. Minimal 5 pemain diperlukan.
                    </div>
                @endif

                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#4B8B43]">Queue Status</p>
                        <h2 id="queue-title" class="mt-1 text-2xl font-black">{{ $waitingQueue ? 'Searching...' : ($activeMatch ? 'Match Found' : 'Ready') }}</h2>
                    </div>
                    <div class="grid h-16 w-16 place-items-center rounded-2xl border border-[#C8E6C9] bg-[#F1F8E9]">
                        <div class="h-8 w-8 rounded-full border-4 border-[#2E7D32] border-t-transparent {{ $waitingQueue ? 'animate-spin' : '' }}"></div>
                    </div>
                </div>

                <div class="my-6 rounded-2xl bg-[#F1F8E9] p-5 text-center">
                    @if($activeMatch)
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#4B8B43]">Match Found</p>
                        <div class="mt-2 text-2xl font-black text-[#0B5D1E]">
                            {{ $activeOpponent?->name ?? 'Lawan ditemukan' }}
                        </div>
                        <p id="queue-caption" class="mt-2 text-sm text-[#4B8B43]">
                            {{ $activeMatch->field?->name ?? 'Lapangan terpilih' }} - {{ \Carbon\Carbon::parse($activeMatch->match_date->toDateString().' '.$activeMatch->start_time)->format('H:i') }}
                        </p>
                    @else
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-[#4B8B43]">Countdown</p>
                        <div id="countdown" data-seconds="{{ $secondsRemaining }}" class="mt-2 font-mono text-5xl font-black tabular-nums text-[#0B5D1E]">
                            {{ gmdate('i:s', $secondsRemaining) }}
                        </div>
                        <p id="queue-caption" class="mt-2 text-sm text-[#4B8B43]">
                            {{ $waitingQueue ? 'Mencari lawan dengan level dan radius yang cocok.' : 'Tekan tombol untuk masuk queue.' }}
                        </p>
                    @endif
                </div>

                @if($activeMatch)
                    @if($activeMatch->status === 'pending')
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <form method="POST" action="{{ route('matches.auto_reject', $activeMatch) }}" onsubmit="return confirm('Tolak match ini? Slot lapangan akan dilepas.')">
                                @csrf
                                <button type="submit" class="w-full rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-base font-black text-red-700 transition hover:bg-red-100">
                                    Reject
                                </button>
                            </form>
                            <form method="POST" action="{{ route('matches.auto_confirm', $activeMatch) }}">
                                @csrf
                                <button type="submit" class="w-full rounded-2xl bg-[#2E8B3C] px-5 py-4 text-base font-black text-white transition hover:bg-[#23742F]">
                                    Accept
                                </button>
                            </form>
                        </div>
                    @endif
                @else
                    <form id="auto-match-form" method="POST" action="{{ route('matches.auto_store') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label class="mb-2 block text-sm font-bold text-[#1B5E20]">Durasi main</label>
                            <select name="duration_minutes" class="w-full rounded-2xl border border-[#C8E6C9] bg-[#F8FCF4] px-4 py-3 text-[#0B5D1E] outline-none focus:border-[#2E7D32]">
                                <option value="60" @selected($autoParams['duration_minutes'] === 60)>60 menit</option>
                                <option value="120" @selected($autoParams['duration_minutes'] === 120)>120 menit</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-bold text-[#1B5E20]">Radius maksimal pencarian</label>
                            <select name="radius_km" class="w-full rounded-2xl border border-[#C8E6C9] bg-[#F8FCF4] px-4 py-3 text-[#0B5D1E] outline-none focus:border-[#2E7D32]">
                                @foreach([5, 10, 15, 25, 50] as $radius)
                                    <option value="{{ $radius }}" @selected($autoParams['radius_km'] === $radius)>{{ $radius }} km</option>
                                @endforeach
                            </select>
                        </div>

                        <button type="button" data-open-start-modal @disabled(! $team->isVerified() || ! $canUseMatchFeatures) class="w-full rounded-2xl bg-[#2E8B3C] px-5 py-4 text-base font-black text-white transition hover:bg-[#23742F] disabled:cursor-not-allowed disabled:bg-gray-200 disabled:text-gray-500">
                            {{ $waitingQueue ? 'Cari Ulang' : 'Cari Lawan' }}
                        </button>
                    </form>
                @endif

                @if($waitingQueue && ! $activeMatch)
                    <form method="POST" action="{{ route('matches.auto_cancel') }}" class="mt-3">
                        @csrf
                        <button class="w-full rounded-2xl border border-red-200 bg-red-50 px-5 py-3 text-sm font-black text-red-700 transition hover:bg-red-100">
                            Cancel Queue
                        </button>
                    </form>
                @endif
            </aside>
        </section>
    </main>

    <div id="start-modal" class="hidden fixed inset-0 z-50 items-center justify-center bg-[#143D1F]/45 p-4">
        <div class="w-full max-w-md rounded-3xl border border-[#DDEED8] bg-white p-6 text-[#0B5D1E] shadow-2xl shadow-[#1B5E20]/20">
            <p class="text-xs font-black uppercase tracking-[0.2em] text-[#2E7D32]">Mulai Matchmaking</p>
            <h2 class="mt-2 text-2xl font-black">Mulai cari lawan sekarang?</h2>
            <p class="mt-3 text-sm leading-6 text-[#4B8B43]">
                Setelah dikonfirmasi, tim kamu masuk queue selama 5 menit. Kalau lawan cocok ditemukan, sistem otomatis membuat jadwal di jam penuh +3 jam dan memilih lapangan netral.
            </p>
            <div class="mt-6 grid gap-3 sm:grid-cols-2">
                <button type="button" data-close-start-modal class="rounded-2xl border border-[#C8E6C9] bg-[#F8FCF4] px-4 py-3 text-sm font-black text-[#1B5E20]">
                    Batal
                </button>
                <button type="submit" form="auto-match-form" class="rounded-2xl bg-[#2E8B3C] px-4 py-3 text-sm font-black text-white">
                    Ya, Cari Lawan
                </button>
            </div>
        </div>
    </div>

    <div id="match-modal" class="hidden fixed inset-0 z-50 items-center justify-center bg-[#143D1F]/45 p-4">
        <div class="w-full max-w-lg rounded-3xl border border-[#DDEED8] bg-white p-6 text-[#0B5D1E] shadow-2xl shadow-[#1B5E20]/20">
            <p class="text-xs font-black uppercase tracking-[0.2em] text-[#2E7D32]">Match Found</p>
            <h2 class="mt-2 text-3xl font-black">{{ $activeOpponent?->name ?? 'Lawan ditemukan' }}</h2>
            <div class="mt-5 space-y-3 text-sm text-[#4B8B43]">
                <div class="flex justify-between gap-4 rounded-2xl bg-[#F1F8E9] p-3">
                    <span>Lapangan</span>
                    <strong class="text-right text-[#0B5D1E]">{{ $activeMatch?->field?->name ?? '-' }}</strong>
                </div>
                <div class="flex justify-between gap-4 rounded-2xl bg-[#F1F8E9] p-3">
                    <span>Kick-off</span>
                    <strong class="text-right text-[#0B5D1E]">
                        {{ $activeMatch ? \Carbon\Carbon::parse($activeMatch->match_date->toDateString().' '.$activeMatch->start_time)->format('d M Y H:i') : '-' }}
                    </strong>
                </div>
                <div class="flex justify-between gap-4 rounded-2xl bg-[#F1F8E9] p-3">
                    <span>Status</span>
                    <strong class="text-right text-[#0B5D1E] capitalize">{{ $activeMatch?->status ?? '-' }}</strong>
                </div>
            </div>

            @if($activeMatch)
                <div class="mt-6 grid gap-3 sm:grid-cols-3">
                    <a href="{{ route('matches.show', $activeMatch) }}" class="rounded-2xl border border-[#C8E6C9] bg-[#F8FCF4] px-4 py-3 text-center text-sm font-black text-[#1B5E20]">Detail</a>
                    @if($activeMatch->status === 'pending')
                        <form method="POST" action="{{ route('matches.auto_reject', $activeMatch) }}">
                            @csrf
                            <button class="w-full rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-black text-red-700">Reject</button>
                        </form>
                        <form method="POST" action="{{ route('matches.auto_confirm', $activeMatch) }}">
                            @csrf
                            <button class="w-full rounded-2xl bg-[#2E8B3C] px-4 py-3 text-sm font-black text-white">Accept</button>
                        </form>
                    @else
                        <button type="button" data-close-modal class="rounded-2xl bg-[#2E8B3C] px-4 py-3 text-sm font-black text-white sm:col-span-2">Tutup</button>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const page = document.getElementById('auto-match-page');
    const countdown = document.getElementById('countdown');
    const queueTitle = document.getElementById('queue-title');
    const queueCaption = document.getElementById('queue-caption');
    const startModal = document.getElementById('start-modal');
    const modal = document.getElementById('match-modal');
    const hasActiveQueue = page?.dataset.hasActiveQueue === 'true';
    const hasActiveMatch = page?.dataset.hasActiveMatch === 'true';
    const statusUrl = page?.dataset.statusUrl;
    const autoUrl = page?.dataset.autoUrl;
    let seconds = Number(countdown?.dataset.seconds || 0);
    let matchFoundRedirectStarted = false;

    function renderCountdown() {
        if (!countdown) return;

        const safeSeconds = Math.max(0, seconds);
        const minutes = String(Math.floor(safeSeconds / 60)).padStart(2, '0');
        const remainder = String(safeSeconds % 60).padStart(2, '0');
        countdown.textContent = `${minutes}:${remainder}`;
    }

    if (hasActiveQueue) {
        setInterval(() => {
            if (seconds > 0) {
                seconds -= 1;
                renderCountdown();
            }
        }, 1000);
    }

    async function pollMatchmaking() {
        if (!statusUrl || !autoUrl) return;

        try {
            const response = await fetch(statusUrl, {
                headers: { 'Accept': 'application/json' },
            });
            const payload = await response.json();

            if (payload.status === 'matched') {
                if (hasActiveMatch) {
                    if (queueTitle) queueTitle.textContent = 'Match Found';
                    if (payload.match_status === 'confirmed' && payload.match_url) {
                        window.location.href = payload.match_url;
                    }
                    return;
                }

                if (matchFoundRedirectStarted) return;
                matchFoundRedirectStarted = true;
                if (queueTitle) queueTitle.textContent = 'Match Found';
                if (queueCaption) queueCaption.textContent = `Match ditemukan: ${payload.opponent}. Menyiapkan detail match...`;
                if (countdown) {
                    countdown.textContent = 'MATCH';
                }
                setTimeout(() => {
                    window.location.href = autoUrl;
                }, 5000);
                return;
            }

            if (payload.status === 'searching') {
                seconds = payload.seconds_remaining;
                renderCountdown();
                if (queueTitle) queueTitle.textContent = 'Searching...';
                if (queueCaption) queueCaption.textContent = 'Mencari lawan dengan level dan radius yang cocok.';
                return;
            }

            if (payload.status === 'idle' && seconds === 0) {
                if (queueTitle) queueTitle.textContent = 'Expired';
                if (queueCaption) queueCaption.textContent = 'Lawan tidak ditemukan. Kamu bisa mencari ulang.';
                if (hasActiveMatch) {
                    window.location.href = autoUrl;
                }
            }
        } catch (error) {
            if (queueCaption) queueCaption.textContent = 'Koneksi realtime terputus sementara. Halaman tetap bisa di-refresh.';
        }
    }

    if (hasActiveQueue || hasActiveMatch) {
        setInterval(pollMatchmaking, 5000);
    }

    document.querySelectorAll('[data-open-start-modal]').forEach((button) => {
        button.addEventListener('click', () => {
            startModal?.classList.remove('hidden');
            startModal?.classList.add('flex');
        });
    });

    document.querySelectorAll('[data-close-start-modal]').forEach((button) => {
        button.addEventListener('click', () => {
            startModal?.classList.add('hidden');
            startModal?.classList.remove('flex');
        });
    });

    document.querySelectorAll('[data-close-modal]').forEach((button) => {
        button.addEventListener('click', () => {
            modal?.classList.add('hidden');
            modal?.classList.remove('flex');
        });
    });
</script>
@endpush
