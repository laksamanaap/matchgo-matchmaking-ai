@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#F1F8E9]">
    <x-navbar />

    <div class="pt-24 pb-12 container mx-auto px-6">
        <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-4xl font-bold text-[#1B5E20] mb-2">Cari Pertandingan</h1>
                <p class="text-[#2E7D32]/80">Lihat tantangan terbuka dari tim lain dan terima jadwal yang cocok untuk tim kamu.</p>
            </div>
        </div>

        @if(! $team->isVerified())
            <div class="mb-8 rounded-2xl border border-yellow-200 bg-yellow-50 p-5 text-yellow-800">
                Tim kamu belum diverifikasi admin. Setelah status tim menjadi verified, kamu bisa mengambil tantangan dari tim lain.
            </div>
        @endif

        @if(! $canUseMatchFeatures)
            <div class="mb-8 rounded-2xl border border-yellow-200 bg-yellow-50 p-5 text-yellow-800">
                Tim kamu baru memiliki {{ $team->activePlayerCount() }} pemain termasuk kapten. Minimal 5 pemain diperlukan untuk mencari pertandingan.
            </div>
        @endif

        <div>
            <h2 class="text-2xl font-bold text-[#1B5E20] mb-4">Tantangan Dari Tim Lain</h2>

            @if($openChallenges->count() > 0)
                <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    @foreach($openChallenges as $challenge)
                        <div class="bg-white rounded-3xl shadow-lg p-6 hover:shadow-xl transition">
                            <div class="mb-5">
                                <p class="text-sm font-semibold text-[#2E7D32]/70">Dibuat oleh</p>
                                <h3 class="text-2xl font-bold text-[#1B5E20]">{{ $challenge->teamA->name }}</h3>
                            </div>

                            <div class="space-y-3 mb-6 pb-6 border-b border-[#81C784]/20">
                                <div>
                                    <p class="text-xs text-[#2E7D32]/80">Lapangan</p>
                                    <p class="font-semibold text-[#1B5E20]">{{ $challenge->field?->name ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-[#2E7D32]/80">Jadwal</p>
                                    <p class="font-semibold text-[#1B5E20]">{{ $challenge->match_date->format('d M Y') }} pukul {{ \Carbon\Carbon::parse($challenge->start_time)->format('H:i') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-[#2E7D32]/80">Durasi</p>
                                    <p class="font-semibold text-[#1B5E20]">{{ $challenge->duration_minutes }} menit</p>
                                </div>
                            </div>

                            <div class="grid gap-3 mb-6">
                                <div class="rounded-2xl bg-[#F1F8E9] p-4">
                                    <p class="text-xs text-[#2E7D32]/70">Total Biaya Lapangan</p>
                                    <p class="text-xl font-bold text-[#1B5E20]">Rp {{ number_format($challenge->matchCost?->total_cost ?? 0, 0, ',', '.') }}</p>
                                </div>
                                <div class="rounded-2xl bg-[#F1F8E9] p-4">
                                    <p class="text-xs text-[#2E7D32]/70">Biaya Per Tim</p>
                                    <p class="text-xl font-bold text-[#1B5E20]">Rp {{ number_format($challenge->matchCost?->cost_per_team ?? 0, 0, ',', '.') }}</p>
                                </div>
                                <div class="rounded-2xl bg-[#F1F8E9] p-4">
                                    <p class="text-xs text-[#2E7D32]/70">Biaya Pengelola Web 10%</p>
                                    <p class="text-xl font-bold text-[#1B5E20]">Rp {{ number_format($challenge->matchCost?->handling_fee ?? (int) ceil(($challenge->matchCost?->dp_per_team ?? ($challenge->matchCost?->cost_per_team ?? 0)) * 0.1), 0, ',', '.') }}</p>
                                </div>
                                <div class="rounded-2xl bg-[#F1F8E9] p-4">
                                    <p class="text-xs text-[#2E7D32]/70">Total Bayar Tim Kamu</p>
                                    <p class="text-xl font-bold text-[#1B5E20]">Rp {{ number_format(($challenge->matchCost?->dp_per_team ?? ($challenge->matchCost?->cost_per_team ?? 0)) + ($challenge->matchCost?->handling_fee ?? (int) ceil(($challenge->matchCost?->dp_per_team ?? ($challenge->matchCost?->cost_per_team ?? 0)) * 0.1)), 0, ',', '.') }}</p>
                                </div>
                            </div>

                            <div class="grid gap-3">
                                @if($team->isVerified() && $canUseMatchFeatures)
                                    <form method="POST" action="{{ route('matches.accept', $challenge) }}">
                                        @csrf
                                        <button type="submit" class="w-full rounded-2xl bg-gradient-to-r from-[#2E7D32] to-[#4CAF50] px-4 py-3 text-white font-semibold hover:shadow-lg transition">
                                            Terima Tantangan
                                        </button>
                                    </form>
                                @else
                                    <button type="button" disabled class="w-full cursor-not-allowed rounded-2xl bg-gray-200 px-4 py-3 text-gray-500 font-semibold">
                                        {{ ! $team->isVerified() ? 'Menunggu Verifikasi Admin' : 'Minimal 5 Pemain' }}
                                    </button>
                                @endif
                                <a href="{{ route('matches.show', $challenge) }}" class="text-center rounded-2xl border border-[#81C784]/40 px-4 py-3 text-[#2E7D32] font-semibold hover:bg-[#81C784]/10 transition">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white rounded-3xl shadow-lg p-10 text-center">
                    <p class="text-xl text-[#2E7D32]/80 mb-6">Belum ada tantangan terbuka dari tim lain.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
