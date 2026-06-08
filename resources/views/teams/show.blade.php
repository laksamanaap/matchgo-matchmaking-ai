@extends('layouts.app')

@section('content')
@php
    $teamLogoExists = $team->logo_url && \Illuminate\Support\Facades\Storage::disk('public')->exists($team->logo_url);
    $levelLabel = str_replace('_', ' ', $team->skill_level);
    $statusLabel = match ($team->verification_status) {
        'verified' => 'Verified',
        'rejected' => 'Rejected',
        default => 'Pending',
    };
    $statusBadgeClass = match ($team->verification_status) {
        'verified' => 'bg-emerald-500 text-white shadow-emerald-700/15',
        'rejected' => 'bg-red-500 text-white shadow-red-700/15',
        default => 'bg-amber-400 text-amber-950 shadow-amber-700/10',
    };
@endphp

<div class="min-h-screen bg-[#F1F8E9]">
    <x-navbar />
    
    <div class="pt-24 pb-12 container mx-auto px-6">
        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-[#C8E6C9] bg-white p-4 text-sm font-bold text-[#1B5E20] shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any() && ! in_array(old('_form'), ['team', 'player', 'player_edit'], true))
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-bold text-red-700">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div class="grid gap-8">
            {{-- Team Header --}}
            <section class="overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#1F7A32] via-[#349E46] to-[#74B843] p-1 shadow-2xl shadow-[#1B5E20]/20">
                <div class="relative px-6 py-7 text-white sm:px-8 lg:px-10">
                    <div class="absolute inset-x-0 top-0 h-px bg-white/30"></div>
                    <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                        <div class="flex min-w-0 flex-col gap-5 sm:flex-row sm:items-center">
                            <div class="grid h-28 w-28 shrink-0 place-items-center overflow-hidden rounded-[1.7rem] border border-white/25 bg-white/20 shadow-xl shadow-[#0B3D1F]/10 ring-1 ring-white/20">
                            @if($teamLogoExists)
                                <img src="{{ asset('storage/' . $team->logo_url) }}" alt="{{ $team->name }}" class="h-full w-full object-contain p-2.5">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-4xl font-black">
                                    {{ strtoupper(substr($team->name, 0, 1)) }}
                                </div>
                            @endif
                            </div>
                            <div class="min-w-0">
                                <div class="mb-3 flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-black uppercase tracking-[0.14em] text-white ring-1 ring-white/20">
                                        Tim Futsal
                                    </span>
                                    <span class="rounded-full px-3 py-1 text-xs font-black {{ $team->verification_status === 'verified' ? 'bg-white text-[#1B7A32]' : 'bg-white/20 text-white ring-1 ring-white/20' }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>
                                <h1 class="truncate text-4xl font-black tracking-normal text-white sm:text-5xl">{{ $team->name }}</h1>
                                @if($team->description)
                                    <p class="mt-2 max-w-2xl text-sm font-semibold leading-6 text-white/90">{{ $team->description }}</p>
                                @endif
                            </div>
                        </div>

                        @if($team->owner_id === Auth::id())
                            <div class="flex shrink-0 gap-3">
                                <button type="button" data-open-team-edit class="inline-flex items-center gap-2 rounded-2xl bg-white/20 px-5 py-3 text-sm font-black text-white shadow-sm ring-1 ring-white/20 transition hover:bg-white/30">
                                    <x-heroicon-o-pencil-square class="h-5 w-5" />
                                    Edit
                                </button>
                                <form method="POST" action="{{ route('teams.destroy', $team) }}" class="inline" onsubmit="return confirm('Hapus tim ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="inline-flex items-center gap-2 rounded-2xl bg-red-500 px-5 py-3 text-sm font-black text-white shadow-lg shadow-red-900/15 transition hover:bg-red-600">
                                        <x-heroicon-o-trash class="h-5 w-5" />
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            {{-- Team Stats --}}
            <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-lg shadow-[#1B5E20]/7">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-[#4B8B43]">Level</p>
                            <p class="mt-2 text-2xl font-black capitalize text-[#0B5D1E]">{{ $levelLabel }}</p>
                        </div>
                        <span class="grid h-11 w-11 place-items-center rounded-2xl bg-[#F1F8E9] text-[#2E7D32]">
                            <x-heroicon-o-sparkles class="h-6 w-6" />
                        </span>
                    </div>
                </div>
                <div class="rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-lg shadow-[#1B5E20]/7">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-[#4B8B43]">Lokasi</p>
                            <p class="mt-2 text-2xl font-black text-[#0B5D1E]">{{ $team->city }}</p>
                        </div>
                        <span class="grid h-11 w-11 place-items-center rounded-2xl bg-[#F1F8E9] text-[#2E7D32]">
                            <x-heroicon-o-map-pin class="h-6 w-6" />
                        </span>
                    </div>
                </div>
                <div class="rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-lg shadow-[#1B5E20]/7">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-[#4B8B43]">Pemain</p>
                            <p class="mt-2 text-2xl font-black text-[#0B5D1E]">{{ $team->activePlayerCount() }}</p>
                        </div>
                        <span class="grid h-11 w-11 place-items-center rounded-2xl bg-[#F1F8E9] text-[#2E7D32]">
                            <x-heroicon-o-users class="h-6 w-6" />
                        </span>
                    </div>
                </div>
                <div class="rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-lg shadow-[#1B5E20]/7">
                    <div class="flex items-center justify-between gap-4">
                        <div>
                            <p class="text-sm font-semibold text-[#4B8B43]">Status</p>
                            <span class="mt-3 inline-flex rounded-xl px-4 py-2 text-sm font-black shadow-sm {{ $statusBadgeClass }}">
                                {{ $statusLabel }}
                            </span>
                        </div>
                        <span class="grid h-11 w-11 place-items-center rounded-2xl bg-[#F1F8E9] text-[#2E7D32]">
                            <x-heroicon-o-shield-check class="h-6 w-6" />
                        </span>
                    </div>
                </div>
            </section>

            {{-- Statistik Tim --}}
            @php
                $stats = $team->teamStats;
                $played = $stats->total_matches ?? 0;
                $wins = $stats->wins ?? 0;
                $draws = $stats->draws ?? 0;
                $losses = $stats->losses ?? 0;
                $goalsScored = $stats->goals_scored ?? 0;
                $goalsConceded = $stats->goals_conceded ?? 0;
                $goalDiff = $goalsScored - $goalsConceded;
                $winRate = $played > 0 ? round(($wins / $played) * 100) : 0;
                $winPct = $played > 0 ? ($wins / $played) * 100 : 0;
                $drawPct = $played > 0 ? ($draws / $played) * 100 : 0;
                $lossPct = $played > 0 ? ($losses / $played) * 100 : 0;
                $circumference = 2 * pi() * 52;
                $ringOffset = $circumference * (1 - $winRate / 100);
            @endphp
            <div class="overflow-hidden rounded-3xl bg-white shadow-lg">
                {{-- Header gradient --}}
                <div class="flex items-center justify-between gap-4 bg-gradient-to-r from-[#1B5E20] to-[#43A047] px-8 py-5">
                    <div class="flex items-center gap-3">
                        <span class="grid h-11 w-11 place-items-center rounded-2xl bg-white/15 text-white ring-1 ring-white/25">
                            <x-heroicon-o-chart-bar class="h-6 w-6" />
                        </span>
                        <div>
                            <h2 class="text-xl font-black text-white">Statistik Tim</h2>
                            <p class="text-xs font-semibold text-white/80">{{ $played }} pertandingan dimainkan</p>
                        </div>
                    </div>
                    <span class="hidden rounded-full bg-white/15 px-4 py-1.5 text-sm font-black text-white ring-1 ring-white/25 sm:block">
                        {{ $goalDiff > 0 ? '+' : '' }}{{ $goalDiff }} Selisih Gol
                    </span>
                </div>

                <div class="grid gap-8 p-8 lg:grid-cols-[auto_1fr] lg:items-center">
                    {{-- Win rate ring --}}
                    <div class="mx-auto flex flex-col items-center">
                        <div class="relative h-40 w-40">
                            <svg class="h-full w-full -rotate-90" viewBox="0 0 120 120">
                                <circle cx="60" cy="60" r="52" fill="none" stroke="#EAF2E5" stroke-width="12" />
                                <circle cx="60" cy="60" r="52" fill="none" stroke="url(#wr)" stroke-width="12" stroke-linecap="round"
                                    stroke-dasharray="{{ $circumference }}" stroke-dashoffset="{{ $ringOffset }}" />
                                <defs>
                                    <linearGradient id="wr" x1="0%" y1="0%" x2="100%" y2="100%">
                                        <stop offset="0%" stop-color="#2E7D32" />
                                        <stop offset="100%" stop-color="#7BD389" />
                                    </linearGradient>
                                </defs>
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center">
                                <span class="text-4xl font-black text-[#1B5E20]">{{ $winRate }}%</span>
                                <span class="text-xs font-bold uppercase tracking-wide text-[#4B8B43]">Win Rate</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right side --}}
                    <div class="grid gap-5">
                        {{-- W/D/L distribusi bar --}}
                        <div>
                            <div class="flex h-3 w-full overflow-hidden rounded-full bg-[#EAF2E5]">
                                <div class="bg-emerald-500" style="width: {{ $winPct }}%"></div>
                                <div class="bg-sky-400" style="width: {{ $drawPct }}%"></div>
                                <div class="bg-red-400" style="width: {{ $lossPct }}%"></div>
                            </div>
                            <div class="mt-3 grid grid-cols-3 gap-3">
                                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-3 text-center">
                                    <p class="text-2xl font-black text-emerald-700">{{ $wins }}</p>
                                    <p class="text-[11px] font-bold uppercase tracking-wide text-emerald-600">Menang</p>
                                </div>
                                <div class="rounded-2xl border border-sky-200 bg-sky-50 p-3 text-center">
                                    <p class="text-2xl font-black text-sky-700">{{ $draws }}</p>
                                    <p class="text-[11px] font-bold uppercase tracking-wide text-sky-600">Seri</p>
                                </div>
                                <div class="rounded-2xl border border-red-200 bg-red-50 p-3 text-center">
                                    <p class="text-2xl font-black text-red-700">{{ $losses }}</p>
                                    <p class="text-[11px] font-bold uppercase tracking-wide text-red-600">Kalah</p>
                                </div>
                            </div>
                        </div>

                        {{-- Goals --}}
                        <div class="grid grid-cols-3 gap-3">
                            <div class="flex items-center gap-3 rounded-2xl bg-[#F8FCF4] p-4 ring-1 ring-[#DDEED8]">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-[#E8F5E9] text-[#2E7D32]">
                                    <x-heroicon-o-arrow-trending-up class="h-5 w-5" />
                                </span>
                                <div>
                                    <p class="text-lg font-black leading-none text-[#1B5E20]">{{ $goalsScored }}</p>
                                    <p class="mt-1 text-[11px] font-bold uppercase tracking-wide text-[#4B8B43]">Gol</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 rounded-2xl bg-[#F8FCF4] p-4 ring-1 ring-[#DDEED8]">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-red-50 text-red-500">
                                    <x-heroicon-o-arrow-trending-down class="h-5 w-5" />
                                </span>
                                <div>
                                    <p class="text-lg font-black leading-none text-[#1B5E20]">{{ $goalsConceded }}</p>
                                    <p class="mt-1 text-[11px] font-bold uppercase tracking-wide text-[#4B8B43]">Kebobolan</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 rounded-2xl bg-[#F8FCF4] p-4 ring-1 ring-[#DDEED8]">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl bg-[#E8F5E9] text-[#2E7D32]">
                                    <x-heroicon-o-trophy class="h-5 w-5" />
                                </span>
                                <div>
                                    <p class="text-lg font-black leading-none {{ $goalDiff > 0 ? 'text-emerald-700' : ($goalDiff < 0 ? 'text-red-600' : 'text-[#1B5E20]') }}">{{ $goalDiff > 0 ? '+' : '' }}{{ $goalDiff }}</p>
                                    <p class="mt-1 text-[11px] font-bold uppercase tracking-wide text-[#4B8B43]">Selisih</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="edit-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-3xl bg-white p-8 shadow-2xl">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-[#1B5E20]">Edit Tim</h2>
                            <p class="text-sm text-[#2E7D32]/80">Perbarui identitas dan logo tim.</p>
                        </div>
                        <button type="button" onclick="document.getElementById('edit-modal').classList.add('hidden')" class="text-[#2E7D32] text-2xl leading-none">&times;</button>
                    </div>

                    @if($errors->any() && old('_form') === 'team')
                        <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 p-4 text-red-700">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('teams.update', $team) }}" enctype="multipart/form-data" class="grid gap-6">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_form" value="team">

                        <div class="flex items-center gap-4 rounded-2xl bg-[#F1F8E9] p-4">
                            <div class="h-20 w-20 overflow-hidden rounded-2xl border border-[#81C784]/30 bg-white">
                                @if($teamLogoExists)
                                    <img src="{{ asset('storage/' . $team->logo_url) }}" alt="{{ $team->name }}" class="h-full w-full object-contain p-2">
                                @else
                                    <div class="flex h-full w-full items-center justify-center text-2xl font-bold text-[#2E7D32]">
                                        {{ strtoupper(substr($team->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1">
                                <label class="block text-sm font-semibold text-[#2E7D32] mb-2">Logo Tim</label>
                                <input type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="w-full rounded-2xl border border-[#81C784]/40 bg-white px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50 @error('logo') border-red-500 @enderror" />
                                @error('logo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-semibold text-[#2E7D32] mb-2">Nama Tim</label>
                                <input type="text" name="team_name" value="{{ old('team_name', $team->name) }}" required class="w-full rounded-2xl border border-[#81C784]/40 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-[#2E7D32] mb-2">Level Tim</label>
                                <select name="team_level" required class="w-full rounded-2xl border border-[#81C784]/40 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50">
                                    <option value="casual" {{ old('team_level', $team->skill_level) === 'casual' ? 'selected' : '' }}>Casual</option>
                                    <option value="semi_pro" {{ old('team_level', $team->skill_level) === 'semi_pro' ? 'selected' : '' }}>Semi-Pro</option>
                                    <option value="competitive" {{ old('team_level', $team->skill_level) === 'competitive' ? 'selected' : '' }}>Competitive</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#2E7D32] mb-2">Deskripsi Tim</label>
                            <textarea name="description" rows="4" class="w-full rounded-2xl border border-[#81C784]/40 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50">{{ old('description', $team->description) }}</textarea>
                        </div>

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-semibold text-[#2E7D32] mb-2">Domisili</label>
                                <input id="team-domicile" type="text" name="domicile" value="{{ old('domicile', $team->city) }}" required class="w-full rounded-2xl border border-[#81C784]/40 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-[#2E7D32] mb-2">Nomor Kontak</label>
                                <input type="text" name="contact_number" value="{{ old('contact_number', $team->contact_number) }}" required class="w-full rounded-2xl border border-[#81C784]/40 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50" />
                            </div>
                        </div>

                        <x-location-map-picker
                            map-id="edit-team-map"
                            lat-id="team-latitude"
                            lng-id="team-longitude"
                            domicile-id="team-domicile"
                            :default-lat="old('latitude', $team->latitude ?? -6.2)"
                            :default-lng="old('longitude', $team->longitude ?? 106.8)" />

                        <div class="grid gap-6 md:grid-cols-2">
                            <div>
                                <label class="block text-sm font-semibold text-[#2E7D32] mb-2">Latitude</label>
                                <input id="team-latitude" type="number" step="any" name="latitude" value="{{ old('latitude', $team->latitude) }}" required class="w-full rounded-2xl border border-[#81C784]/40 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50" />
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-[#2E7D32] mb-2">Longitude</label>
                                <input id="team-longitude" type="number" step="any" name="longitude" value="{{ old('longitude', $team->longitude) }}" required class="w-full rounded-2xl border border-[#81C784]/40 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50" />
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2 justify-end">
                            <button type="button" onclick="document.getElementById('edit-modal').classList.add('hidden')" class="rounded-2xl border border-[#81C784]/40 px-5 py-3 text-[#2E7D32] font-semibold hover:bg-[#81C784]/10 transition">Batal</button>
                            <button type="submit" class="rounded-2xl bg-gradient-to-r from-[#2E7D32] to-[#4CAF50] px-5 py-3 text-white font-semibold hover:shadow-lg transition">Simpan Tim</button>
                        </div>
                    </form>
                </div>
            </div>

            @if($errors->any() && old('_form') === 'team')
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        document.getElementById('edit-modal').classList.remove('hidden');
                    });
                </script>
            @endif

            {{-- Players Section --}}
            <div class="bg-white rounded-3xl shadow-lg p-8">
                <div class="flex flex-col gap-3 mb-6 sm:flex-row sm:items-center sm:justify-between">
                    <h2 class="text-2xl font-bold text-[#1B5E20]">Daftar Pemain</h2>
                    @if($team->owner_id === Auth::id())
                        <div class="flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('players.demo_fill') }}" class="inline" onsubmit="return confirm('Tambahkan 5 pemain demo ke tim?')">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-xl bg-[#F1F8E9] text-[#2E7D32] font-semibold ring-1 ring-[#81C784]/40 hover:bg-[#E4F2DE] transition">
                                    Demo Auto Fill
                                </button>
                            </form>
                            <button type="button" onclick="document.getElementById('add-player-modal').classList.remove('hidden')" class="px-4 py-2 rounded-xl bg-[#4CAF50] text-white font-semibold hover:bg-[#45a049] transition">
                                + Tambah Pemain
                            </button>
                        </div>
                    @endif
                </div>

                <div class="space-y-3">
                    {{-- Kapten (pemilik tim) selalu tampil pertama --}}
                    <div class="flex items-center justify-between p-4 rounded-2xl bg-gradient-to-r from-[#E8F5E9] to-[#F1F8E9] border-2 border-[#4CAF50]/50 ring-1 ring-[#4CAF50]/20">
                        <div class="flex items-center gap-4">
                            <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-[#2E7D32] text-sm font-black text-white">1</span>
                            <span class="h-11 w-11 shrink-0 overflow-hidden rounded-full bg-[#DDF1D8]">
                                @if($team->owner->profile_photo)
                                    <img src="{{ asset('storage/' . $team->owner->profile_photo) }}" alt="{{ $team->owner->name }}" class="h-full w-full object-cover">
                                @else
                                    <span class="flex h-full w-full items-center justify-center text-base font-black text-[#2E7D32]">{{ strtoupper(substr($team->owner->name, 0, 1)) }}</span>
                                @endif
                            </span>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <p class="font-bold text-[#1B5E20]">{{ $team->owner->name }}</p>
                                    <span class="inline-flex items-center gap-1 rounded-full bg-[#2E7D32] px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wide text-white">
                                        ★ Kapten
                                    </span>
                                </div>
                                <p class="text-sm text-[#2E7D32]/80">Kapten Tim</p>
                            </div>
                        </div>
                    </div>

                    {{-- Pemain lain, bernomor mulai dari 2 --}}
                    @foreach($team->players as $index => $player)
                        <div class="flex items-center justify-between p-4 rounded-2xl bg-[#F1F8E9] border border-[#81C784]/30">
                            <div class="flex items-center gap-4">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-full bg-white text-sm font-black text-[#2E7D32] ring-1 ring-[#81C784]/40">{{ $index + 2 }}</span>
                                <div>
                                    <p class="font-semibold text-[#1B5E20]">{{ $player->player_name }}</p>
                                    <p class="text-sm text-[#2E7D32]/80">{{ $player->position }} • {{ $player->age }} tahun</p>
                                </div>
                            </div>
                            @if($team->owner_id === Auth::id())
                                <div class="flex gap-2">
                                    <button
                                        type="button"
                                        data-open-player-edit
                                        data-action="{{ route('players.update', $player) }}"
                                        data-name="{{ $player->player_name }}"
                                        data-position="{{ $player->position }}"
                                        data-age="{{ $player->age }}"
                                        class="px-3 py-1 rounded-lg text-sm text-[#2E7D32] hover:bg-[#81C784]/20 transition"
                                    >
                                        Edit
                                    </button>
                                    <form method="POST" action="/players/{{ $player->id }}" class="inline" onsubmit="return confirm('Hapus pemain?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1 rounded-lg text-sm text-red-600 hover:bg-red-50 transition">Hapus</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach

                    @if($team->players->count() === 0)
                        <p class="text-center text-[#2E7D32]/80 py-6">Belum ada pemain selain kapten. Tambahkan pemain atau pakai Demo Auto Fill!</p>
                    @endif
                </div>
            </div>

            <div id="add-player-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                <div class="w-full max-w-xl rounded-3xl bg-white p-8 shadow-2xl">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-[#1B5E20]">Tambah Pemain</h2>
                            <p class="text-sm text-[#2E7D32]/80">Isi informasi pemain untuk menambah ke tim.</p>
                        </div>
                        <button type="button" onclick="document.getElementById('add-player-modal').classList.add('hidden')" class="text-[#2E7D32] text-2xl leading-none">&times;</button>
                    </div>

                    @if($errors->any() && old('_form') === 'player')
                        <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 p-4 text-red-700">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('players.store') }}" class="grid gap-6">
                        @csrf
                        <input type="hidden" name="_form" value="player">
                        <div>
                            <label for="player_name" class="block text-sm font-semibold text-[#2E7D32] mb-2">Nama Pemain</label>
                            <input id="player_name" name="player_name" type="text" value="{{ old('player_name') }}" required class="w-full rounded-2xl border border-[#81C784]/40 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50 @error('player_name') border-red-500 @enderror" />
                            @error('player_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="position" class="block text-sm font-semibold text-[#2E7D32] mb-2">Posisi</label>
                            <select id="position" name="position" required class="w-full rounded-2xl border border-[#81C784]/40 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50 @error('position') border-red-500 @enderror">
                                <option value="">-- Pilih Posisi --</option>
                                @foreach(['Kiper', 'Anchor', 'Flank Kiri', 'Flank Kanan', 'Pivot', 'Universal'] as $position)
                                    <option value="{{ $position }}" {{ old('position') === $position ? 'selected' : '' }}>{{ $position }}</option>
                                @endforeach
                            </select>
                            @error('position') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label for="age" class="block text-sm font-semibold text-[#2E7D32] mb-2">Usia</label>
                            <input id="age" name="age" type="number" min="13" max="50" value="{{ old('age') }}" required class="w-full rounded-2xl border border-[#81C784]/40 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50 @error('age') border-red-500 @enderror" />
                            @error('age') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex gap-3 pt-4 justify-end">
                            <button type="button" onclick="document.getElementById('add-player-modal').classList.add('hidden')" class="rounded-2xl border border-[#81C784]/40 px-5 py-3 text-[#2E7D32] font-semibold hover:bg-[#81C784]/10 transition">Batal</button>
                            <button type="submit" class="rounded-2xl bg-gradient-to-r from-[#2E7D32] to-[#4CAF50] px-5 py-3 text-white font-semibold hover:shadow-lg transition">Tambah Pemain</button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="edit-player-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                <div class="w-full max-w-xl rounded-3xl bg-white p-8 shadow-2xl">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-[#1B5E20]">Edit Pemain</h2>
                            <p class="text-sm text-[#2E7D32]/80">Perbarui informasi pemain tim.</p>
                        </div>
                        <button type="button" data-close-player-edit class="text-[#2E7D32] text-2xl leading-none">&times;</button>
                    </div>

                    @if($errors->any() && old('_form') === 'player_edit')
                        <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 p-4 text-red-700">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form id="edit-player-form" method="POST" action="{{ old('player_id') ? route('players.update', old('player_id')) : '#' }}" class="grid gap-6">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="_form" value="player_edit">
                        <input id="edit-player-id" type="hidden" name="player_id" value="{{ old('player_id') }}">

                        <div>
                            <label for="edit_player_name" class="block text-sm font-semibold text-[#2E7D32] mb-2">Nama Pemain</label>
                            <input id="edit_player_name" name="player_name" type="text" value="{{ old('_form') === 'player_edit' ? old('player_name') : '' }}" required class="w-full rounded-2xl border border-[#81C784]/40 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50 @error('player_name') border-red-500 @enderror" />
                            @if(old('_form') === 'player_edit')
                                @error('player_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            @endif
                        </div>

                        <div>
                            <label for="edit_position" class="block text-sm font-semibold text-[#2E7D32] mb-2">Posisi</label>
                            <select id="edit_position" name="position" required class="w-full rounded-2xl border border-[#81C784]/40 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50 @error('position') border-red-500 @enderror">
                                <option value="">-- Pilih Posisi --</option>
                                @foreach(['Kiper', 'Anchor', 'Flank Kiri', 'Flank Kanan', 'Pivot', 'Universal'] as $position)
                                    <option value="{{ $position }}" {{ old('_form') === 'player_edit' && old('position') === $position ? 'selected' : '' }}>{{ $position }}</option>
                                @endforeach
                            </select>
                            @if(old('_form') === 'player_edit')
                                @error('position') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            @endif
                        </div>

                        <div>
                            <label for="edit_age" class="block text-sm font-semibold text-[#2E7D32] mb-2">Usia</label>
                            <input id="edit_age" name="age" type="number" min="13" max="50" value="{{ old('_form') === 'player_edit' ? old('age') : '' }}" required class="w-full rounded-2xl border border-[#81C784]/40 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50 @error('age') border-red-500 @enderror" />
                            @if(old('_form') === 'player_edit')
                                @error('age') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            @endif
                        </div>

                        <div class="flex gap-3 pt-4 justify-end">
                            <button type="button" data-close-player-edit class="rounded-2xl border border-[#81C784]/40 px-5 py-3 text-[#2E7D32] font-semibold hover:bg-[#81C784]/10 transition">Batal</button>
                            <button type="submit" class="rounded-2xl bg-gradient-to-r from-[#2E7D32] to-[#4CAF50] px-5 py-3 text-white font-semibold hover:shadow-lg transition">Simpan Pemain</button>
                        </div>
                    </form>
                </div>
            </div>

            @if($errors->any() && old('_form') === 'player')
                <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        document.getElementById('add-player-modal').classList.remove('hidden');
                    });
                </script>
            @endif

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const teamEditModal = document.getElementById('edit-modal');
                    const playerEditModal = document.getElementById('edit-player-modal');
                    const playerEditForm = document.getElementById('edit-player-form');
                    const playerEditName = document.getElementById('edit_player_name');
                    const playerEditPosition = document.getElementById('edit_position');
                    const playerEditAge = document.getElementById('edit_age');
                    const playerEditId = document.getElementById('edit-player-id');

                    document.querySelectorAll('[data-open-team-edit]').forEach((button) => {
                        button.addEventListener('click', () => {
                            teamEditModal?.classList.remove('hidden');
                        });
                    });

                    document.querySelectorAll('[data-open-player-edit]').forEach((button) => {
                        button.addEventListener('click', () => {
                            if (!playerEditModal || !playerEditForm) return;

                            playerEditForm.action = button.dataset.action;
                            playerEditName.value = button.dataset.name || '';
                            playerEditPosition.value = button.dataset.position || '';
                            playerEditAge.value = button.dataset.age || '';
                            playerEditId.value = button.dataset.action?.split('/').pop() || '';
                            playerEditModal.classList.remove('hidden');
                        });
                    });

                    document.querySelectorAll('[data-close-player-edit]').forEach((button) => {
                        button.addEventListener('click', () => {
                            playerEditModal?.classList.add('hidden');
                        });
                    });

                    @if(old('_form') === 'player_edit')
                        playerEditModal?.classList.remove('hidden');
                    @endif
                });
            </script>

            {{-- Match History --}}
            <div class="bg-white rounded-3xl shadow-lg p-8">
                <h2 class="text-2xl font-bold text-[#1B5E20] mb-6">Pertandingan Terakhir</h2>
                
                @if($team->sentMatchRequests->count() > 0 || $team->receivedMatchRequests->count() > 0)
                    <div class="space-y-3">
                        @foreach($team->sentMatchRequests->take(5) as $match)
                            <div class="flex items-center justify-between p-4 rounded-2xl bg-[#F1F8E9] border border-[#81C784]/30">
                                <div>
                                    <p class="font-semibold text-[#1B5E20]">Request vs {{ $match->opponentTeam->name }}</p>
                                    <p class="text-sm text-[#2E7D32]/80">{{ $match->preferred_date }}</p>
                                </div>
                                <span class="px-4 py-2 rounded-xl text-white text-sm font-semibold {{ $match->status === 'accepted' ? 'bg-green-500' : ($match->status === 'rejected' ? 'bg-red-500' : 'bg-blue-500') }}">
                                    {{ ucfirst($match->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-[#2E7D32]/80 py-8">Belum ada pertandingan. Mulai cari lawan!</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
