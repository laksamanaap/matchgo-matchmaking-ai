@extends('layouts.app')

@section('title', 'Lapangan')

@section('content')
<div class="bg-[#F1F8E9] min-h-screen">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-[#1B5E20]">Lapangan Futsal</h1>
        <p class="text-[#2E7D32]/60 text-sm mt-1">Temukan dan pesan lapangan futsal terdekat</p>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('venue.index') }}" class="bg-white border border-green-100 rounded-2xl p-5 mb-8 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Kota</label>
                <select name="city"
                        class="w-full px-3 py-2.5 rounded-xl bg-gray-50 border border-gray-200 text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 transition">
                    <option value="">Semua Kota</option>
                    @foreach($cities as $city)
                    <option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Tersedia Tanggal</label>
                <input type="date" name="date" value="{{ request('date') }}" min="{{ date('Y-m-d') }}"
                       class="w-full px-3 py-2.5 rounded-xl bg-gray-50 border border-gray-200 text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 transition">
            </div>
        </div>
        <div class="flex gap-3 mt-4">
            <button type="submit" class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl transition">Filter</button>
            @if(request()->hasAny(['city','date']))
            <a href="{{ route('venue.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-medium rounded-xl transition">Reset</a>
            @endif
        </div>
    </form>

    {{-- Grid --}}
    @if($venues->count())
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($venues as $venue)
        <a href="{{ route('venue.show', $venue->id) . ($matchId ? '?match_id=' . $matchId : '') }}"
           class="group bg-white border border-gray-100 hover:border-green-400 hover:shadow-md rounded-2xl overflow-hidden transition shadow-sm">

            {{-- Placeholder header --}}
            <div class="h-32 bg-gradient-to-br from-green-100 to-green-50 flex items-center justify-center">
                <span class="text-4xl">🏟️</span>
            </div>

            <div class="p-5">
                <div class="flex items-start justify-between gap-2 mb-2">
                    <h3 class="font-bold text-gray-900 group-hover:text-[#2E7D32] transition">{{ $venue->name }}</h3>
                    @if($venue->available_slots > 0)
                    <span class="text-xs px-2 py-0.5 bg-green-100 text-green-700 rounded-full shrink-0 font-medium">Tersedia</span>
                    @else
                    <span class="text-xs px-2 py-0.5 bg-red-100 text-red-600 rounded-full shrink-0 font-medium">Penuh</span>
                    @endif
                </div>

                <p class="text-xs text-gray-400 flex items-center gap-1 mb-3">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                    {{ $venue->city }}
                </p>

                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-400">Harga / Jam</p>
                        <p class="text-lg font-black text-green-700">Rp {{ number_format($venue->price_per_hour, 0, ',', '.') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-400">Slot Kosong</p>
                        <p class="text-lg font-black text-gray-800">{{ $venue->available_slots }}</p>
                    </div>
                </div>
            </div>
        </a>
        @endforeach
    </div>

    <div class="mt-8">{{ $venues->links() }}</div>

    @else
    <div class="text-center py-16 bg-white rounded-2xl border border-green-100 shadow-sm">
        <div class="text-5xl mb-4">🏟️</div>
        <p class="text-gray-700 font-semibold mb-2">Tidak ada lapangan ditemukan</p>
        <p class="text-gray-400 text-sm">Coba ubah filter pencarian kamu.</p>
    </div>
    @endif

</div>
</div>
@endsection
