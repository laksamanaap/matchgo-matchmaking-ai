@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#F1F8E9]">
    <x-navbar />
    
    <div class="pt-24 pb-12 container mx-auto px-6">
        <div class="grid gap-8">
            {{-- Match Header --}}
            <div class="bg-gradient-to-r from-[#2E7D32] to-[#4CAF50] rounded-3xl shadow-lg p-8 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/80 mb-2">{{ $match->match_date->format('d M Y') }} {{ \Carbon\Carbon::parse($match->start_time)->format('H:i') }}</p>
                        <h1 class="text-4xl font-bold">{{ $match->teamA->name }} vs {{ $match->teamB?->name ?? 'Menunggu Lawan' }}</h1>
                    </div>
                    @if($match->status === 'pending' && (Auth::id() === $match->teamA->owner_id || Auth::id() === $match->teamB?->owner_id))
                        <div class="flex gap-3">
                            <form method="POST" action="{{ route('matches.auto_reject', $match) }}" class="inline">
                                @csrf
                                <button type="submit" class="px-6 py-3 rounded-2xl bg-red-500/80 text-white font-semibold hover:bg-red-600 transition">
                                    Reject
                                </button>
                            </form>
                            <form method="POST" action="{{ route('matches.auto_confirm', $match) }}" class="inline">
                                @csrf
                                <button type="submit" class="px-6 py-3 rounded-2xl bg-white text-[#1B5E20] font-semibold hover:bg-emerald-50 transition">
                                    Accept
                                </button>
                            </form>
                        </div>
                    @elseif(in_array($match->status, ['scheduled', 'confirmed'], true) && (Auth::id() === $match->teamA->owner_id || Auth::id() === $match->teamB?->owner_id))
                        <form method="POST" action="{{ route('matches.cancel', $match) }}" class="inline" onsubmit="return confirm('Batalkan pertandingan?')">
                            @csrf
                            <button type="submit" class="px-6 py-3 rounded-2xl bg-red-500/80 text-white font-semibold hover:bg-red-600 transition">
                                Batalkan
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- Match Details Grid --}}
            <div class="grid gap-6 md:grid-cols-3">
                {{-- Team A --}}
                <div class="bg-white rounded-3xl shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-[#1B5E20] mb-4">{{ $match->teamA->name }}</h2>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-[#2E7D32]/80">Level</p>
                            <p class="font-bold text-[#1B5E20] capitalize">{{ str_replace('_', '-', $match->teamA->skill_level) }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-[#2E7D32]/80">Lokasi</p>
                            <p class="font-bold text-[#1B5E20]">{{ $match->teamA->city }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-[#2E7D32]/80">Pemain Terdaftar</p>
                            <p class="font-bold text-[#1B5E20]">{{ $match->teamA->activePlayerCount() }} orang</p>
                        </div>
                    </div>
                </div>

                {{-- Match Info --}}
                <div class="bg-white rounded-3xl shadow-lg p-8">
                    <h2 class="text-lg font-bold text-[#1B5E20] mb-4">Detail Pertandingan</h2>
                    <div class="space-y-4">
                        <div>
                            <p class="text-sm text-[#2E7D32]/80">Tanggal & Jam</p>
                            <p class="font-bold text-[#1B5E20]">{{ $match->match_date->format('d M Y') }}</p>
                            <p class="font-bold text-[#1B5E20]">{{ \Carbon\Carbon::parse($match->start_time)->format('H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-[#2E7D32]/80">Durasi</p>
                            <p class="font-bold text-[#1B5E20]">{{ $match->duration_minutes }} menit</p>
                        </div>
                        <div>
                            <p class="text-sm text-[#2E7D32]/80">Status</p>
                            <span class="inline-block px-4 py-2 rounded-xl text-white text-sm font-semibold {{ $match->status === 'completed' ? 'bg-green-500' : ($match->status === 'cancelled' ? 'bg-red-500' : 'bg-blue-500') }}">
                                {{ ucfirst($match->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Team B --}}
                <div class="bg-white rounded-3xl shadow-lg p-8">
                    @if($match->teamB)
                        <h2 class="text-2xl font-bold text-[#1B5E20] mb-4">{{ $match->teamB->name }}</h2>
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-[#2E7D32]/80">Level</p>
                                <p class="font-bold text-[#1B5E20] capitalize">{{ str_replace('_', '-', $match->teamB->skill_level) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-[#2E7D32]/80">Lokasi</p>
                                <p class="font-bold text-[#1B5E20]">{{ $match->teamB->city }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-[#2E7D32]/80">Pemain Terdaftar</p>
                                <p class="font-bold text-[#1B5E20]">{{ $match->teamB->activePlayerCount() }} orang</p>
                            </div>
                        </div>
                    @else
                        <h2 class="text-2xl font-bold text-[#1B5E20] mb-4">Menunggu Lawan</h2>
                        <p class="text-[#2E7D32]/80 mb-6">Tantangan ini masih terbuka untuk tim lain.</p>
                        @if(Auth::user()->team?->id !== $match->team_a_id)
                            <form method="POST" action="{{ route('matches.accept', $match) }}">
                                @csrf
                                <button type="submit" class="w-full rounded-2xl bg-gradient-to-r from-[#2E7D32] to-[#4CAF50] px-4 py-3 text-white font-semibold hover:shadow-lg transition">
                                    Cari Pertandingan
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Booking & Cost --}}
            <div class="grid gap-6 md:grid-cols-2">
                {{-- Booking Info --}}
                <div class="bg-white rounded-3xl shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-[#1B5E20] mb-4">Info Lapangan</h2>
                    @if($match->booking)
                        <div class="space-y-4">
                            <div>
                                <p class="text-sm text-[#2E7D32]/80">Lapangan</p>
                                <p class="font-bold text-[#1B5E20]">{{ $match->booking->field->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-[#2E7D32]/80">Alamat</p>
                                <p class="font-bold text-[#1B5E20]">{{ $match->booking->field->address }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-[#2E7D32]/80">Jam Booking</p>
                                <p class="font-bold text-[#1B5E20]">{{ $match->booking->start_at->format('H:i') }} - {{ $match->booking->start_at->copy()->addHours($match->booking->duration_hours)->format('H:i') }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-[#2E7D32]/80">Status Booking</p>
                                <span class="inline-block px-4 py-2 rounded-xl text-white text-sm font-semibold bg-green-500">
                                    {{ ucfirst($match->booking->status) }}
                                </span>
                            </div>
                        </div>
                    @else
                        <p class="text-[#2E7D32]/80">Lapangan belum dibooking</p>
                    @endif
                </div>

                {{-- Cost Info --}}
                <div class="bg-white rounded-3xl shadow-lg p-8">
                    <h2 class="text-2xl font-bold text-[#1B5E20] mb-4">Biaya Pertandingan</h2>
                    @if($match->matchCost)
                        <div class="space-y-4">
                            <div class="flex justify-between items-center pb-4 border-b border-[#81C784]/20">
                                <p class="text-[#2E7D32]/80">Total Biaya</p>
                                <p class="font-bold text-[#1B5E20] text-lg">Rp {{ number_format($match->matchCost->total_cost) }}</p>
                            </div>
                            <div class="flex justify-between items-center pb-4 border-b border-[#81C784]/20">
                                <p class="text-[#2E7D32]/80">Per Tim</p>
                                <p class="font-bold text-[#1B5E20] text-lg">Rp {{ number_format($match->matchCost->cost_per_team) }}</p>
                            </div>
                            <p class="text-sm text-[#2E7D32]/80 mt-4">{{ $match->matchCost->payment_notes }}</p>
                        </div>
                    @else
                        <p class="text-[#2E7D32]/80">Biaya belum terhitung</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
