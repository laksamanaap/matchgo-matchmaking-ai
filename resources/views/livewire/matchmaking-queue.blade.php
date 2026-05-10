@php
    $matchExpiresMs = 0;
    if (! empty($foundMatch['expires_at'])) {
        try {
            $matchExpiresMs = \Carbon\Carbon::parse($foundMatch['expires_at'])->valueOf();
        } catch (\Throwable $e) {
            $matchExpiresMs = 0;
        }
    }
@endphp
<div
    x-data="{
        waitSeconds: 0,
        acceptSeconds: 30,
    }"
    x-init="
        const tick = () => {
            const queuedAt   = parseInt($root.dataset.queuedAtMs   || '0', 10);
            const expiresAt  = parseInt($root.dataset.matchExpiresMs || '0', 10);
            const state      = $root.dataset.queueState || 'idle';
            if (state === 'queued' && queuedAt > 0) {
                waitSeconds = Math.max(0, Math.floor((Date.now() - queuedAt) / 1000));
            }
            if (state === 'match_found' && expiresAt > 0) {
                acceptSeconds = Math.max(0, Math.ceil((expiresAt - Date.now()) / 1000));
            }
            if (state !== 'match_found') {
                acceptSeconds = 30;
            }
        };
        tick();
        setInterval(tick, 250);
    "
    data-queued-at-ms="{{ (int) ($this->status['queued_at_ms'] ?? 0) }}"
    data-match-expires-ms="{{ $matchExpiresMs }}"
    data-queue-state="{{ $this->queueState }}"
    wire:poll.3s
    class="max-w-3xl mx-auto"
>
    {{-- ERROR / INFO TOASTS --}}
    @if ($errorMessage)
        <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            {{ $errorMessage }}
        </div>
    @endif

    {{-- TEAM PICKER --}}
    @if ($this->myTeams->count() === 0)
        <div class="p-6 bg-yellow-50 border border-yellow-200 rounded-2xl text-center">
            <p class="text-sm text-yellow-800">
                Kamu belum punya tim terverifikasi. Buat dan verifikasi tim dulu di
                <a href="/app/my-teams" class="underline font-semibold">Kelola Tim</a>.
            </p>
        </div>
    @else

    {{-- IDLE STATE --}}
    @if ($this->queueState === 'idle')
        <div class="bg-white border border-green-100 rounded-3xl shadow-sm p-8">
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
                    <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-[#1B5E20] mb-1">Mulai Matchmaking</h2>
                <p class="text-sm text-gray-500">Sistem akan mencari lawan setara dengan tim kamu secara otomatis.</p>
            </div>

            @if ($this->myTeams->count() > 1)
                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Tim</label>
                <select wire:model.live="selectedTeamId"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent mb-5">
                    <option value="">— Pilih Tim —</option>
                    @foreach ($this->myTeams as $team)
                        <option value="{{ $team->id }}">{{ $team->name }} ({{ ucfirst(str_replace('_', ' ', $team->skill_level)) }})</option>
                    @endforeach
                </select>
            @endif

            @if ($selectedTeamId)
                @php $team = $this->myTeams->firstWhere('id', $selectedTeamId); @endphp
                <div class="flex items-center gap-3 p-4 bg-green-50 rounded-xl mb-5">
                    <div class="w-12 h-12 bg-green-200 rounded-xl flex items-center justify-center font-bold text-green-700">
                        {{ strtoupper(substr($team->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">{{ $team->name }}</p>
                        <p class="text-xs text-gray-500">{{ $team->city }} · {{ ucfirst(str_replace('_', ' ', $team->skill_level)) }}</p>
                    </div>
                </div>
            @endif

            <button wire:click="startMatchmaking"
                    @class([
                        'w-full py-4 rounded-2xl font-bold text-white text-lg transition shadow-md',
                        'bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 hover:to-green-600' => $selectedTeamId,
                        'bg-gray-300 cursor-not-allowed' => ! $selectedTeamId,
                    ])
                    @disabled(! $selectedTeamId)>
                Mulai Cari Lawan
            </button>
        </div>
    @endif

    {{-- QUEUED STATE --}}
    @if ($this->queueState === 'queued')
        <div class="bg-gradient-to-br from-green-600 to-green-500 rounded-3xl shadow-xl p-8 text-white text-center">
            <div class="relative inline-block mb-6">
                <div class="absolute inset-0 bg-white/30 rounded-full animate-ping"></div>
                <div class="relative w-24 h-24 bg-white/20 backdrop-blur rounded-full flex items-center justify-center">
                    <svg class="w-12 h-12 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke-width="3" class="opacity-25"/>
                        <path stroke-width="3" stroke-linecap="round" d="M12 2a10 10 0 0110 10"/>
                    </svg>
                </div>
            </div>

            <h2 class="text-3xl font-black mb-1">Mencari Lawan...</h2>
            <p class="text-white/80 text-sm mb-6">Sistem sedang membandingkan ribuan tim untuk kamu</p>

            <div class="grid grid-cols-3 gap-3 mb-6">
                <div class="bg-white/10 backdrop-blur rounded-xl p-3">
                    <p class="text-xs text-white/70 uppercase tracking-wider">Waktu</p>
                    <p class="text-2xl font-black mt-1 tabular-nums"
                       x-text="`${String(Math.floor(Math.floor(waitSeconds)/60)).padStart(2,'0')}:${String(Math.floor(waitSeconds)%60).padStart(2,'0')}`">
                        00:00
                    </p>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-xl p-3">
                    <p class="text-xs text-white/70 uppercase tracking-wider">Toleransi Level</p>
                    <p class="text-2xl font-black mt-1">±{{ $this->status['level_tolerance'] ?? 1 }}</p>
                </div>
                <div class="bg-white/10 backdrop-blur rounded-xl p-3">
                    <p class="text-xs text-white/70 uppercase tracking-wider">Radius</p>
                    <p class="text-2xl font-black mt-1">{{ $this->status['search_range_km'] ?? 5 }}km</p>
                </div>
            </div>

            <div class="text-xs text-white/70 mb-5">
                Toleransi otomatis melebar setelah 30 dan 60 detik.
            </div>

            <button wire:click="cancelMatchmaking"
                    class="w-full py-3 bg-white/10 hover:bg-white/20 backdrop-blur border border-white/30 rounded-xl font-semibold transition">
                Batalkan Antrian
            </button>
        </div>
    @endif

    {{-- MATCH FOUND POPUP --}}
    @if ($this->queueState === 'match_found' && $foundMatch)
        <div class="fixed inset-0 z-[60] bg-black/70 backdrop-blur flex items-center justify-center p-4">
            <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden animate-pulse-slow">
                <div class="bg-gradient-to-br from-green-600 to-green-500 p-6 text-center text-white">
                    <p class="text-xs uppercase tracking-widest text-white/80 mb-1">Lawan ditemukan</p>
                    <h2 class="text-3xl font-black">MATCH FOUND!</h2>
                </div>

                <div class="p-6">
                    <div class="flex items-center justify-between mb-5">
                        <div class="text-center flex-1">
                            <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center font-black text-green-700 text-xl mx-auto mb-2">
                                {{ strtoupper(substr($foundMatch['my_team']['name'] ?? '?', 0, 1)) }}
                            </div>
                            <p class="font-bold text-sm">{{ $foundMatch['my_team']['name'] ?? 'Tim Kamu' }}</p>
                        </div>
                        <div class="px-4 text-2xl font-black text-gray-300">VS</div>
                        <div class="text-center flex-1">
                            <div class="w-16 h-16 bg-red-100 rounded-2xl flex items-center justify-center font-black text-red-700 text-xl mx-auto mb-2">
                                {{ strtoupper(substr($foundMatch['opponent']['name'] ?? '?', 0, 1)) }}
                            </div>
                            <p class="font-bold text-sm">{{ $foundMatch['opponent']['name'] ?? 'Lawan' }}</p>
                            <p class="text-xs text-gray-400">{{ $foundMatch['opponent']['city'] ?? '' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-5 text-xs">
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-gray-400 uppercase tracking-wider">Level</p>
                            <p class="font-bold text-gray-700 mt-1">{{ ucfirst(str_replace('_', ' ', $foundMatch['opponent']['skill_level'] ?? '')) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-3">
                            <p class="text-gray-400 uppercase tracking-wider">Cocok</p>
                            <p class="font-bold text-green-600 mt-1">{{ round(($foundMatch['compatibility_score'] ?? 0) * 100) }}%</p>
                        </div>
                    </div>

                    <div class="text-center mb-5">
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">Konfirmasi dalam</p>
                        <p class="text-3xl font-black text-orange-500" x-text="`${acceptSeconds}s`">30s</p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <button wire:click="rejectMatch"
                                class="py-3 border-2 border-gray-200 rounded-xl font-semibold text-gray-600 hover:bg-gray-50 transition">
                            Tolak
                        </button>
                        <button wire:click="acceptMatch"
                                class="py-3 bg-gradient-to-r from-green-600 to-green-500 hover:from-green-700 text-white rounded-xl font-bold shadow-lg transition">
                            Terima
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @endif

    @push('styles')
    <style>
        @keyframes pulse-slow {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }
        .animate-pulse-slow { animation: pulse-slow 1.5s ease-in-out infinite; }
    </style>
    @endpush
</div>
