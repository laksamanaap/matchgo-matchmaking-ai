@extends('layouts.app')

@section('title', 'Cari Lawan')

@section('content')
<div class="bg-[#F1F8E9] min-h-screen">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-[#1B5E20]">Cari Lawan</h1>
        <p class="text-[#2E7D32]/60 text-sm mt-1">Temukan dan tantang tim futsal lain yang terverifikasi</p>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('discover.index') }}" class="bg-white border border-green-100 rounded-2xl p-5 mb-8 shadow-sm">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Level</label>
                <select name="skill_level"
                        class="w-full px-3 py-2.5 rounded-xl bg-gray-50 border border-gray-200 text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 transition">
                    <option value="">Semua Level</option>
                    <option value="casual"      {{ request('skill_level') === 'casual'      ? 'selected' : '' }}>Kasual</option>
                    <option value="semi_pro"    {{ request('skill_level') === 'semi_pro'    ? 'selected' : '' }}>Semi Pro</option>
                    <option value="competitive" {{ request('skill_level') === 'competitive' ? 'selected' : '' }}>Kompetitif</option>
                </select>
            </div>
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
                <label class="block text-xs font-semibold text-gray-500 mb-1.5">Hari Aktif</label>
                <select name="day"
                        class="w-full px-3 py-2.5 rounded-xl bg-gray-50 border border-gray-200 text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-green-500 transition">
                    <option value="">Semua Hari</option>
                    @foreach(['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'] as $i => $day)
                    <option value="{{ $i }}" {{ request('day') == $i ? 'selected' : '' }}>{{ $day }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="flex gap-3 mt-4">
            <button type="submit" class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl transition">
                Filter
            </button>
            @if(request()->hasAny(['skill_level','city','day']))
            <a href="{{ route('discover.index') }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-600 text-sm font-medium rounded-xl transition">
                Reset
            </a>
            @endif
        </div>
    </form>

    {{-- Results --}}
    @if($teams->count())
    <p class="text-gray-400 text-sm mb-5">Menampilkan {{ $teams->total() }} tim</p>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($teams as $team)
        <a href="{{ route('discover.show', $team->id) }}"
           class="group bg-white border border-gray-100 hover:border-green-400 hover:shadow-md rounded-2xl p-5 transition shadow-sm">

            <div class="flex items-start gap-4 mb-4">
                <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-[#2E7D32] font-black text-lg shrink-0 group-hover:bg-green-200 transition">
                    {{ strtoupper(substr($team->name, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-bold text-gray-900 group-hover:text-[#2E7D32] transition truncate">{{ $team->name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
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
                <span class="text-xs px-2 py-1 rounded-full shrink-0 font-medium {{ $levelBadge[$team->skill_level] ?? 'bg-gray-100 text-gray-500' }}">
                    {{ $levelLabels[$team->skill_level] ?? $team->skill_level }}
                </span>
            </div>

            {{-- Stats --}}
            @if($team->teamStats)
            <div class="grid grid-cols-3 gap-2 text-center">
                <div class="bg-gray-50 rounded-lg py-2">
                    <p class="text-sm font-bold text-gray-800">{{ $team->teamStats->total_matches }}</p>
                    <p class="text-xs text-gray-400">Main</p>
                </div>
                <div class="bg-green-50 rounded-lg py-2">
                    <p class="text-sm font-bold text-green-700">{{ $team->teamStats->wins }}</p>
                    <p class="text-xs text-gray-400">Menang</p>
                </div>
                <div class="bg-gray-50 rounded-lg py-2">
                    <p class="text-sm font-bold text-gray-700">{{ $team->teamMembers_count }}</p>
                    <p class="text-xs text-gray-400">Pemain</p>
                </div>
            </div>
            @endif

            {{-- Active days --}}
            @if($team->teamSchedules->count())
            <div class="mt-3 flex flex-wrap gap-1">
                @php $days = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'] @endphp
                @foreach($team->teamSchedules->where('is_active', true) as $sched)
                <span class="text-xs px-1.5 py-0.5 bg-blue-100 text-blue-600 rounded font-medium">{{ $days[$sched->day_of_week] ?? '' }}</span>
                @endforeach
            </div>
            @endif
        </a>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $teams->links() }}
    </div>

    @else
    <div class="text-center py-16 bg-white rounded-2xl border border-green-100 shadow-sm">
        <div class="text-5xl mb-4">🔍</div>
        <p class="text-gray-700 font-semibold mb-2">Tidak ada tim ditemukan</p>
        <p class="text-gray-400 text-sm">Coba ubah filter pencarian kamu.</p>
    </div>
    @endif

</div>
</div>
@endsection
