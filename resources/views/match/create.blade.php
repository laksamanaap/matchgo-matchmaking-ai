@extends('layouts.app')

@section('title', 'Posting Tantangan')

@section('content')
<div class="bg-[#F1F8E9] min-h-screen">
<div class="max-w-xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="mb-6">
        <a href="{{ route('match.index') }}" class="flex items-center gap-1.5 text-gray-500 hover:text-[#2E7D32] text-sm transition mb-4">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
        <h1 class="text-2xl font-bold text-[#1B5E20]">Posting Tantangan</h1>
        <p class="text-[#2E7D32]/60 text-sm mt-1">Pasang tantangan terbuka — siapapun bisa merespons dan bermain denganmu!</p>
    </div>

    @if($myTeams->isEmpty())
    <div class="text-center py-16 bg-white rounded-2xl border border-green-100 shadow-sm">
        <div class="text-5xl mb-4">🏆</div>
        <p class="text-gray-800 font-semibold mb-2">Timmu belum terverifikasi</p>
        <p class="text-gray-500 text-sm max-w-sm mx-auto">
            Untuk memposting tantangan, tim kamu harus sudah diverifikasi oleh admin.
        </p>
        <a href="/app" class="mt-4 inline-block px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-xl transition">
            Kelola Tim
        </a>
    </div>
    @else
    <form method="POST" action="{{ route('match.store') }}" class="space-y-5">
        @csrf

        {{-- Pick your team --}}
        <div class="bg-white rounded-2xl border border-green-100 p-6 shadow-sm">
            <label class="block text-sm font-semibold text-[#1B5E20] mb-3">Tim Kamu</label>
            <div class="space-y-3">
                @foreach($myTeams as $team)
                <label class="flex items-center gap-4 p-4 rounded-xl border border-gray-200 hover:border-green-400 cursor-pointer transition has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                    <input type="radio" name="requester_team_id" value="{{ $team->id }}"
                           class="text-green-600 border-gray-300 focus:ring-green-500"
                           {{ (old('requester_team_id') == $team->id || $myTeams->count() === 1) ? 'checked' : '' }}>
                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center text-[#2E7D32] font-bold shrink-0">
                        {{ strtoupper(substr($team->name, 0, 1)) }}
                    </div>
                    <div class="flex-1">
                        <p class="font-semibold text-gray-900">{{ $team->name }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $team->city }} · {{ ucfirst(str_replace('_', ' ', $team->skill_level)) }}</p>
                    </div>
                    <span class="text-xs px-2 py-1 bg-green-100 text-green-700 rounded-full font-medium">Terverifikasi</span>
                </label>
                @endforeach
            </div>
            @error('requester_team_id')
                <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Preferred date --}}
        <div class="bg-white rounded-2xl border border-green-100 p-6 shadow-sm">
            <label for="preferred_date" class="block text-sm font-semibold text-[#1B5E20] mb-3">
                Kapan Kamu Bisa Main?
            </label>
            <input
                type="date"
                id="preferred_date"
                name="preferred_date"
                value="{{ old('preferred_date') }}"
                min="{{ date('Y-m-d') }}"
                class="w-full px-4 py-3 rounded-xl bg-white border @error('preferred_date') border-red-400 @else border-gray-300 @enderror text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                required
            >
            @error('preferred_date')
                <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Notes --}}
        <div class="bg-white rounded-2xl border border-green-100 p-6 shadow-sm">
            <label for="notes" class="block text-sm font-semibold text-[#1B5E20] mb-1">
                Pesan / Catatan
                <span class="text-xs font-normal text-gray-400 ml-1">(opsional)</span>
            </label>
            <p class="text-xs text-gray-400 mb-3">Contoh: "Cari lawan level semi pro area Jakarta Selatan"</p>
            <textarea
                id="notes"
                name="notes"
                rows="3"
                maxlength="255"
                placeholder="Tulis pesan singkat untuk tim yang mau merespons..."
                class="w-full px-4 py-3 rounded-xl bg-white border border-gray-300 text-gray-900 text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition resize-none"
            >{{ old('notes') }}</textarea>
            @error('notes')
                <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit"
                class="w-full py-3.5 bg-gradient-to-r from-[#2E7D32] to-[#4CAF50] hover:from-[#1B5E20] hover:to-[#2E7D32] text-white font-bold rounded-xl shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
            ⚡ Posting Tantangan
        </button>
    </form>
    @endif

</div>
</div>
@endsection
