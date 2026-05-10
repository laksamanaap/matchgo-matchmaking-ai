@extends('layouts.app')

@section('title', 'Pertandingan')

@section('content')
<div class="bg-[#F1F8E9] min-h-screen">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-[#1B5E20]">Pertandingan</h1>
            <p class="text-[#2E7D32]/60 text-sm mt-1">Lihat tantangan terbuka atau kelola pertandinganmu</p>
        </div>
        <a href="{{ route('match.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-[#2E7D32] to-[#4CAF50] hover:from-[#1B5E20] hover:to-[#2E7D32] text-white text-sm font-semibold rounded-xl shadow-sm transition-all duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Posting Tantangan
        </a>
    </div>

    {{-- Incoming direct challenges --}}
    @if($incoming->count())
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-[#1B5E20] mb-4 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
            Tantangan Langsung Untukmu
            <span class="ml-1 px-2 py-0.5 text-xs bg-amber-100 text-amber-700 rounded-full font-medium">{{ $incoming->count() }}</span>
        </h2>
        <div class="space-y-3">
            @foreach($incoming as $req)
            <div class="bg-white border border-amber-200 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 font-bold text-lg">
                            {{ strtoupper(substr($req->requesterTeam->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">{{ $req->requesterTeam->name }}</p>
                            <p class="text-sm text-gray-500">{{ $req->requesterTeam->city }} · {{ ucfirst(str_replace('_', ' ', $req->requesterTeam->skill_level)) }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">Tanggal: {{ \Carbon\Carbon::parse($req->preferred_date)->translatedFormat('d M Y') }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <form method="POST" action="{{ route('match.accept', $req->id) }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl transition">
                                Terima
                            </button>
                        </form>
                        <form method="POST" action="{{ route('match.reject', $req->id) }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 text-sm font-semibold rounded-xl border border-red-200 transition">
                                Tolak
                            </button>
                        </form>
                        <a href="{{ route('match.show', $req->id) }}" class="px-3 py-2 text-gray-400 hover:text-[#2E7D32] text-sm rounded-xl hover:bg-green-50 transition">
                            Detail
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Accepted — awaiting venue booking --}}
    @if($accepted->count())
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-[#1B5E20] mb-4 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
            Diterima — Segera Booking Lapangan
            <span class="ml-1 px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded-full font-medium">{{ $accepted->count() }}</span>
        </h2>
        <div class="space-y-3">
            @foreach($accepted as $req)
            @php
                $myTeamIds = auth()->user()->ownedTeams()->pluck('id');
                $isRequester = $myTeamIds->contains($req->requester_team_id);
                $opponent = $isRequester ? $req->opponentTeam : $req->requesterTeam;
                $captain = $opponent->teamMembers->firstWhere('role', 'captain');
                $captainWa = $captain?->user?->whatsapp;
            @endphp
            <div class="bg-white border border-green-300 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-green-700 font-bold text-lg">
                            {{ strtoupper(substr($opponent->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-gray-900">
                                vs <span class="text-[#2E7D32]">{{ $opponent->name }}</span>
                            </p>
                            <p class="text-sm text-gray-500">{{ $opponent->city }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                Tanggal preferensi: {{ \Carbon\Carbon::parse($req->preferred_date)->translatedFormat('d M Y') }}
                            </p>
                            @if($captainWa)
                            <a href="https://wa.me/{{ $captainWa }}"
                               target="_blank"
                               class="inline-flex items-center gap-1.5 mt-1.5 text-xs font-medium text-green-700 hover:text-green-900 transition">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                                    <path d="M12 0C5.373 0 0 5.373 0 12c0 2.118.553 4.103 1.521 5.833L.057 23.885a.5.5 0 00.606.63l6.288-1.649A11.945 11.945 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.9a9.855 9.855 0 01-5.032-1.378l-.36-.214-3.733.979.997-3.645-.235-.374A9.855 9.855 0 012.1 12C2.1 6.534 6.534 2.1 12 2.1c5.466 0 9.9 4.434 9.9 9.9 0 5.466-4.434 9.9-9.9 9.9z"/>
                                </svg>
                                Hubungi Kapten via WhatsApp
                            </a>
                            @else
                            <p class="text-xs text-gray-400 mt-1 italic">Kapten belum mengisi nomor WA</p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-xs px-2.5 py-1 rounded-full bg-green-100 text-green-700 font-medium">✓ Diterima</span>
                        <a href="{{ route('venue.index', ['match_id' => $req->id]) }}"
                           class="px-4 py-2 bg-[#2E7D32] hover:bg-[#1B5E20] text-white text-sm font-semibold rounded-xl transition">
                            Booking Lapangan
                        </a>
                        <a href="{{ route('match.show', $req->id) }}"
                           class="px-3 py-2 text-gray-400 hover:text-[#2E7D32] text-sm rounded-xl hover:bg-green-50 transition">
                            Detail
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Open challenge board --}}
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-[#1B5E20] mb-4 flex items-center gap-2">
            <span class="text-xl">📢</span>
            Tantangan Terbuka
            @if($openChallenges->count())
            <span class="ml-1 px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded-full font-medium">{{ $openChallenges->count() }} aktif</span>
            @endif
        </h2>

        @if($openChallenges->isEmpty())
        <div class="text-center py-12 bg-white rounded-2xl border border-dashed border-green-200">
            <div class="text-4xl mb-3">🏟️</div>
            <p class="text-gray-500 font-medium">Belum ada tantangan terbuka saat ini</p>
            <p class="text-gray-400 text-sm mt-1">Jadilah yang pertama posting tantangan!</p>
            <a href="{{ route('match.create') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl transition">
                Posting Tantangan
            </a>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($openChallenges as $req)
            @php
                $levelBadge = [
                    'casual'      => 'bg-green-100 text-green-700',
                    'semi_pro'    => 'bg-amber-100 text-amber-700',
                    'competitive' => 'bg-red-100 text-red-600',
                ];
                $levelLabels = ['casual'=>'Kasual','semi_pro'=>'Semi Pro','competitive'=>'Kompetitif'];
            @endphp
            <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm hover:border-green-300 hover:shadow-md transition"
                 x-data="{ showAccept: false }">

                {{-- Team info --}}
                <div class="flex items-start gap-3 mb-4">
                    <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-[#2E7D32] font-black text-lg shrink-0">
                        {{ strtoupper(substr($req->requesterTeam->name, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-gray-900 truncate">{{ $req->requesterTeam->name }}</p>
                        <p class="text-xs text-gray-400 flex items-center gap-1 mt-0.5">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            {{ $req->requesterTeam->city }}
                        </p>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full font-medium shrink-0 {{ $levelBadge[$req->requesterTeam->skill_level] ?? 'bg-gray-100 text-gray-500' }}">
                        {{ $levelLabels[$req->requesterTeam->skill_level] ?? $req->requesterTeam->skill_level }}
                    </span>
                </div>

                {{-- Notes --}}
                @if($req->notes)
                <p class="text-sm text-gray-600 bg-gray-50 rounded-xl px-3 py-2 mb-4 italic">"{{ $req->notes }}"</p>
                @endif

                {{-- Stats + date --}}
                <div class="flex items-center justify-between text-xs text-gray-400 mb-4">
                    <span class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ \Carbon\Carbon::parse($req->preferred_date)->translatedFormat('d M Y') }}
                    </span>
                    @if($req->requesterTeam->teamStats)
                    <span>{{ $req->requesterTeam->teamStats->total_matches }} main · <span class="text-green-600">{{ $req->requesterTeam->teamStats->wins }}W</span></span>
                    @endif
                </div>

                {{-- Accept inline --}}
                @if($myTeams->isNotEmpty())
                <div x-show="!showAccept">
                    <button @click="showAccept = true"
                            class="w-full py-2.5 bg-gradient-to-r from-[#2E7D32] to-[#4CAF50] hover:from-[#1B5E20] hover:to-[#2E7D32] text-white text-sm font-semibold rounded-xl transition">
                        Terima Tantangan ⚡
                    </button>
                </div>
                <div x-show="showAccept" x-transition class="mt-1">
                    <form method="POST" action="{{ route('match.accept', $req->id) }}" class="space-y-2">
                        @csrf
                        @if($myTeams->count() > 1)
                        <select name="team_id" required
                                class="w-full px-3 py-2.5 rounded-xl bg-gray-50 border border-gray-200 text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">— Pilih timmu —</option>
                            @foreach($myTeams as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                        @else
                        <input type="hidden" name="team_id" value="{{ $myTeams->first()->id }}">
                        <p class="text-xs text-gray-500 text-center">sebagai <span class="font-semibold text-[#2E7D32]">{{ $myTeams->first()->name }}</span></p>
                        @endif
                        <div class="flex gap-2">
                            <button type="button" @click="showAccept = false"
                                    class="flex-1 py-2 text-sm text-gray-500 hover:text-gray-700 rounded-xl border border-gray-200 hover:bg-gray-50 transition">
                                Batal
                            </button>
                            <button type="submit"
                                    class="flex-1 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl transition">
                                Konfirmasi
                            </button>
                        </div>
                    </form>
                </div>
                @else
                <p class="text-xs text-center text-gray-400">
                    <a href="/app" class="text-[#4CAF50] hover:underline font-medium">Verifikasi timmu</a> untuk bisa merespons
                </p>
                @endif

            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Confirmed matches (scheduled + ongoing) --}}
    @if($upcoming->count())
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-[#1B5E20] mb-4 flex items-center gap-2">
            <span>📅</span> Pertandingan Terjadwal
            <span class="ml-1 px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded-full font-medium">{{ $upcoming->count() }}</span>
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($upcoming as $match)
            @php
                $isPast = \Carbon\Carbon::parse($match->match_date)->isPast();
                $statusBadge = $match->status === 'ongoing'
                    ? 'bg-orange-100 text-orange-700'
                    : ($isPast ? 'bg-gray-100 text-gray-500' : 'bg-blue-100 text-blue-700');
                $statusLabel = $match->status === 'ongoing' ? 'Berlangsung' : 'Dijadwalkan';

                $myIsTeamA       = $myTeamIds->contains($match->team_a_id);
                $opponentTeam    = $myIsTeamA ? $match->teamB : $match->teamA;
                $opponentCaptain = $opponentTeam?->owner;
                $opponentWa      = $opponentCaptain?->whatsapp;
                $waLabel         = $opponentCaptain?->name ? 'Kapten ' . $opponentCaptain->name : 'Kapten Lawan';

                $waMessage = sprintf(
                    "Halo %s, saya kapten dari %s. Pertandingan kita dijadwalkan pada %s pukul %s di %s. Mohon konfirmasi ya 🙏",
                    $opponentCaptain?->name ?? 'Kapten',
                    $myIsTeamA ? $match->teamA->name : $match->teamB->name,
                    \Carbon\Carbon::parse($match->match_date)->translatedFormat('d M Y'),
                    substr((string) $match->start_time, 0, 5),
                    $match->venue?->name ?? 'lapangan'
                );
            @endphp
            <div class="bg-white border border-green-100 rounded-2xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs px-2 py-1 rounded-full font-medium {{ $statusBadge }}">{{ $statusLabel }}</span>
                    <span class="text-xs text-gray-400 font-medium">
                        {{ \Carbon\Carbon::parse($match->match_date)->translatedFormat('d M Y') }}
                        @if($match->start_time)
                        · {{ substr($match->start_time, 0, 5) }}
                        @endif
                    </span>
                </div>
                <div class="flex items-center justify-between my-3">
                    <div class="text-center flex-1">
                        <p class="font-bold text-gray-900 text-sm">{{ $match->teamA->name }}</p>
                        <p class="text-xs {{ $myIsTeamA ? 'text-green-600 font-semibold' : 'text-gray-400' }}">{{ $myIsTeamA ? 'Tim Kamu' : 'Tim A' }}</p>
                    </div>
                    <div class="px-3 text-gray-300 font-bold text-sm">VS</div>
                    <div class="text-center flex-1">
                        <p class="font-bold text-gray-900 text-sm">{{ $match->teamB->name }}</p>
                        <p class="text-xs {{ ! $myIsTeamA ? 'text-green-600 font-semibold' : 'text-gray-400' }}">{{ ! $myIsTeamA ? 'Tim Kamu' : 'Tim B' }}</p>
                    </div>
                </div>
                <div class="pt-3 border-t border-gray-100 flex items-center gap-1 text-xs text-gray-400">
                    <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span class="truncate">{{ $match->venue->name }}</span>
                </div>

                {{-- WhatsApp captain contact --}}
                @if($opponentWa)
                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $opponentWa) }}?text={{ urlencode($waMessage) }}"
                   target="_blank"
                   rel="noopener noreferrer"
                   class="mt-3 flex items-center justify-center gap-2 w-full px-3 py-2.5 bg-green-50 hover:bg-green-100 border border-green-200 text-green-700 hover:text-green-900 text-xs font-semibold rounded-xl transition group">
                    <svg class="w-4 h-4 shrink-0 transition group-hover:scale-110" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                        <path d="M12 0C5.373 0 0 5.373 0 12c0 2.118.553 4.103 1.521 5.833L.057 23.885a.5.5 0 00.606.63l6.288-1.649A11.945 11.945 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.9a9.855 9.855 0 01-5.032-1.378l-.36-.214-3.733.979.997-3.645-.235-.374A9.855 9.855 0 012.1 12C2.1 6.534 6.534 2.1 12 2.1c5.466 0 9.9 4.434 9.9 9.9 0 5.466-4.434 9.9-9.9 9.9z"/>
                    </svg>
                    <span class="truncate">Chat {{ $waLabel }}</span>
                </a>
                @else
                <p class="mt-3 text-xs text-gray-400 italic text-center px-2">Kapten lawan belum mengisi nomor WhatsApp</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- My own challenges --}}
    <div>
        <h2 class="text-lg font-semibold text-[#1B5E20] mb-4">Riwayat Tantangan</h2>
        @if($myChallenges->count())
        <div class="space-y-3">
            @foreach($myChallenges as $req)
            @php
                $myTeamIds = auth()->user()->ownedTeams()->pluck('id');
                $isRequester = $myTeamIds->contains($req->requester_team_id);
            @endphp
            <a href="{{ route('match.show', $req->id) }}"
               class="flex items-center justify-between p-4 bg-white border border-gray-100 rounded-2xl hover:border-green-300 hover:shadow-sm transition group">
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-[#2E7D32] font-bold">
                        ⚽
                    </div>
                    <div>
                        @if($isRequester)
                            @if($req->opponent_team_id)
                            <p class="font-medium text-gray-900 group-hover:text-[#2E7D32] transition">
                                vs {{ $req->opponentTeam->name }}
                            </p>
                            @else
                            <p class="font-medium text-gray-900 group-hover:text-[#2E7D32] transition">
                                Tantangan Terbuka ({{ $req->requesterTeam->name }})
                            </p>
                            @endif
                        @else
                        <p class="font-medium text-gray-900 group-hover:text-[#2E7D32] transition">
                            vs {{ $req->requesterTeam->name }}
                            <span class="text-xs text-gray-400 font-normal ml-1">(kamu ditantang)</span>
                        </p>
                        @endif
                        <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($req->preferred_date)->translatedFormat('d M Y') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @php
                        $badges = [
                            'pending'   => 'bg-amber-100 text-amber-700',
                            'accepted'  => 'bg-green-100 text-green-700',
                            'rejected'  => 'bg-red-100 text-red-600',
                            'cancelled' => 'bg-gray-100 text-gray-500',
                        ];
                        $labels = ['pending'=>'Menunggu','accepted'=>'Diterima','rejected'=>'Ditolak','cancelled'=>'Dibatalkan'];
                    @endphp
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium {{ $badges[$req->status] ?? 'bg-gray-100 text-gray-500' }}">
                        {{ $labels[$req->status] ?? $req->status }}
                    </span>
                    <svg class="w-4 h-4 text-gray-300 group-hover:text-[#4CAF50] transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </div>
            </a>
            @endforeach
        </div>
        {{ $myChallenges->links() }}
        @else
        <div class="text-center py-12 bg-white rounded-2xl border border-gray-100 shadow-sm">
            <div class="text-4xl mb-3">⚽</div>
            <p class="text-gray-500 font-medium">Kamu belum pernah posting tantangan</p>
            <a href="{{ route('match.create') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl transition">
                Posting Tantangan Pertama
            </a>
        </div>
        @endif
    </div>

    {{-- Match history --}}
    @if($history->count())
    <div class="mt-8">
        <h2 class="text-lg font-semibold text-[#1B5E20] mb-4 flex items-center gap-2">
            <span>🏆</span> Riwayat Pertandingan
        </h2>
        <div class="space-y-3">
            @foreach($history as $match)
            @php
                $won = null;
                $myTeamIds = auth()->user()->ownedTeams()->pluck('id');
                if ($match->score_a !== null && $match->score_b !== null) {
                    $myIsA = $myTeamIds->contains($match->team_a_id);
                    $myScore = $myIsA ? $match->score_a : $match->score_b;
                    $oppScore = $myIsA ? $match->score_b : $match->score_a;
                    $won = $myScore > $oppScore ? 'W' : ($myScore < $oppScore ? 'L' : 'D');
                }
            @endphp
            <div class="flex items-center justify-between p-4 bg-white border border-gray-100 rounded-2xl shadow-sm">
                <div class="flex items-center gap-4">
                    @if($won)
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-xs font-black
                        {{ $won === 'W' ? 'bg-green-100 text-green-700' : ($won === 'L' ? 'bg-red-100 text-red-600' : 'bg-gray-100 text-gray-500') }}">
                        {{ $won }}
                    </div>
                    @else
                    <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-400 text-lg">⚽</div>
                    @endif
                    <div>
                        <p class="font-medium text-gray-900 text-sm">
                            {{ $match->teamA->name }} vs {{ $match->teamB->name }}
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ \Carbon\Carbon::parse($match->match_date)->translatedFormat('d M Y') }}
                            · {{ $match->venue->name }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    @if($match->score_a !== null)
                    <span class="text-lg font-black text-gray-700">{{ $match->score_a }} — {{ $match->score_b }}</span>
                    @endif
                    <span class="text-xs px-2.5 py-1 rounded-full font-medium
                        {{ $match->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $match->status === 'completed' ? 'Selesai' : 'Dibatalkan' }}
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
</div>
@endsection
