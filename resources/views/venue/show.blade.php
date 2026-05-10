@extends('layouts.app')

@section('title', $venue->name)

@section('content')
<div class="bg-[#F1F8E9] min-h-screen">
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <a href="{{ route('venue.index') }}" class="flex items-center gap-1.5 text-gray-500 hover:text-[#2E7D32] text-sm transition mb-6">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Lapangan
    </a>

    {{-- Venue info --}}
    <div class="bg-white border border-green-100 rounded-2xl p-6 mb-6 shadow-sm">
        <div class="flex items-start gap-5">
            <div class="w-16 h-16 rounded-2xl bg-green-100 flex items-center justify-center text-3xl shrink-0">🏟️</div>
            <div class="flex-1">
                <h1 class="text-2xl font-black text-[#1B5E20]">{{ $venue->name }}</h1>
                <p class="text-gray-500 mt-1">{{ $venue->address }}</p>
                <p class="text-gray-400 text-sm mt-0.5 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                    {{ $venue->city }}
                </p>
                <div class="flex items-center gap-6 mt-4">
                    <div>
                        <p class="text-xs text-gray-400">Harga per Jam</p>
                        <p class="text-xl font-black text-green-700">Rp {{ number_format($venue->price_per_hour, 0, ',', '.') }}</p>
                    </div>
                    @if($venue->contact_phone)
                    <div>
                        <p class="text-xs text-gray-400">Kontak</p>
                        <p class="text-gray-800 font-medium">{{ $venue->contact_phone }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Date picker --}}
    <div class="bg-white border border-green-100 rounded-2xl p-5 mb-6 shadow-sm">
        <form method="GET" action="{{ route('venue.show', $venue->id) }}" class="flex items-end gap-4">
            @if($preselectedMatchId)
            <input type="hidden" name="match_id" value="{{ $preselectedMatchId }}">
            @endif
            <div class="flex-1">
                <label class="block text-sm font-semibold text-[#1B5E20] mb-2">Pilih Tanggal</label>
                <input type="date" name="date" value="{{ $date }}" min="{{ date('Y-m-d') }}"
                       class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 transition">
            </div>
            <button type="submit" class="px-5 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-xl transition">
                Cek Slot
            </button>
        </form>
    </div>

    {{-- Slot booking --}}
    <div class="bg-white border border-green-100 rounded-2xl p-6 shadow-sm">
        <h2 class="text-lg font-bold text-[#1B5E20] mb-4">
            Jadwal Tersedia —
            <span class="text-[#4CAF50]">{{ \Carbon\Carbon::parse($date)->translatedFormat('l, d M Y') }}</span>
        </h2>

        @if($slots->isEmpty())
        <div class="text-center py-12">
            <p class="text-gray-500">Tidak ada slot jadwal untuk tanggal ini.</p>
            <p class="text-gray-400 text-sm mt-1">Hubungi lapangan langsung atau pilih tanggal lain.</p>
        </div>
        @else
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach($slots as $slot)
            <div class="p-4 rounded-xl border {{ $slot->is_booked ? 'border-gray-100 bg-gray-50 opacity-60' : 'border-gray-200 hover:border-green-400 cursor-pointer bg-white hover:bg-green-50' }} transition">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-gray-900">{{ substr($slot->start_time, 0, 5) }} – {{ substr($slot->end_time, 0, 5) }}</p>
                        @php
                            $duration = (strtotime($slot->end_time) - strtotime($slot->start_time)) / 3600;
                            $cost = (int)($venue->price_per_hour * $duration);
                        @endphp
                        <p class="text-xs text-gray-400 mt-0.5">{{ $duration }} jam · Rp {{ number_format($cost, 0, ',', '.') }}</p>
                    </div>
                    @if($slot->is_booked)
                    <span class="text-xs px-2 py-1 bg-red-100 text-red-600 rounded-lg font-medium">Terpesan</span>
                    @else
                    <span class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded-lg font-medium">Tersedia</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        {{-- Book form --}}
        @if($slots->where('is_booked', false)->count())
        <div class="mt-6 pt-6 border-t border-gray-100">
            <h3 class="text-sm font-semibold text-[#1B5E20] mb-4">Pesan Slot</h3>
            <form method="POST" action="{{ route('venue.book', $venue->id) }}" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-600 mb-2">Slot yang Dipilih</label>
                    <select name="venue_schedule_id" required
                            class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 transition">
                        <option value="">— Pilih slot waktu —</option>
                        @foreach($slots->where('is_booked', false) as $slot)
                        <option value="{{ $slot->id }}">
                            {{ substr($slot->start_time, 0, 5) }} – {{ substr($slot->end_time, 0, 5) }}
                        </option>
                        @endforeach
                    </select>
                </div>

                @if($myAcceptedMatches->count())
                <div>
                    @if($preselectedMatchId && $myAcceptedMatches->firstWhere('id', $preselectedMatchId))
                    {{-- Locked: came from a specific accepted match --}}
                    @php $lockedMatch = $myAcceptedMatches->firstWhere('id', $preselectedMatchId) @endphp
                    <label class="block text-sm font-semibold text-gray-600 mb-2">Pertandingan</label>
                    <div class="flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 rounded-xl">
                        <svg class="w-4 h-4 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span class="text-sm font-medium text-green-800">
                            {{ $lockedMatch->requesterTeam->name }} vs {{ $lockedMatch->opponentTeam->name }}
                            · {{ \Carbon\Carbon::parse($lockedMatch->preferred_date)->translatedFormat('d M Y') }}
                        </span>
                    </div>
                    <input type="hidden" name="match_request_id" value="{{ $preselectedMatchId }}">
                    @else
                    <label class="block text-sm font-semibold text-gray-600 mb-2">
                        Hubungkan ke Pertandingan
                        <span class="text-xs font-normal text-gray-400">(opsional)</span>
                    </label>
                    <select name="match_request_id"
                            class="w-full px-4 py-3 rounded-xl bg-gray-50 border border-gray-200 text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 transition">
                        <option value="">— Tidak dihubungkan —</option>
                        @foreach($myAcceptedMatches as $mr)
                        <option value="{{ $mr->id }}">
                            {{ $mr->requesterTeam->name }} vs {{ $mr->opponentTeam->name }}
                            · {{ \Carbon\Carbon::parse($mr->preferred_date)->format('d/m') }}
                        </option>
                        @endforeach
                    </select>
                    @endif
                </div>
                @endif

                <button type="submit"
                        class="w-full py-3.5 bg-gradient-to-r from-[#2E7D32] to-[#4CAF50] hover:from-[#1B5E20] hover:to-[#2E7D32] text-white font-bold rounded-xl shadow-sm transition-all duration-200">
                    Pesan Lapangan
                </button>
            </form>
        </div>
        @endif
        @endif
    </div>

</div>
</div>
@endsection
