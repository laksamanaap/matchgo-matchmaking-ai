@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#F1F8E9]">
    <x-navbar />

    <main class="container mx-auto px-6 pb-12 pt-24">
        @if(session('success'))
            <div class="mx-auto mb-6 max-w-6xl rounded-2xl border border-[#C8E6C9] bg-white p-4 text-sm text-[#1B5E20] shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mx-auto mb-6 max-w-6xl rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <section class="mx-auto max-w-6xl">
            <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#4B8B43]">History</p>
                    <h1 class="mt-2 text-4xl font-black text-[#0B5D1E]">Riwayat pertandingan</h1>
                    <p class="mt-2 max-w-2xl text-[#4B8B43]">Semua pertandingan tim kamu, termasuk jadwal aktif, selesai, dan dibatalkan.</p>
                </div>
                <a href="{{ route('matches.index') }}" class="inline-flex items-center justify-center rounded-2xl bg-white px-5 py-3 text-sm font-bold text-[#2E7D32] shadow-sm ring-1 ring-[#DDEED8] transition hover:bg-[#F8FCF4]">
                    Daftar Pertandingan
                </a>
            </div>

            <div class="mb-8 grid gap-4 md:grid-cols-3">
                <div class="rounded-3xl border border-[#DDEED8] bg-white p-5 shadow-lg shadow-[#1B5E20]/5">
                    <p class="text-sm text-[#4B8B43]">Aktif</p>
                    <p class="mt-2 text-4xl font-black text-[#0B5D1E]">{{ $upcomingCount }}</p>
                </div>
                <div class="rounded-3xl border border-[#DDEED8] bg-white p-5 shadow-lg shadow-[#1B5E20]/5">
                    <p class="text-sm text-[#4B8B43]">Selesai</p>
                    <p class="mt-2 text-4xl font-black text-[#0B5D1E]">{{ $completedCount }}</p>
                </div>
                <div class="rounded-3xl border border-[#DDEED8] bg-white p-5 shadow-lg shadow-[#1B5E20]/5">
                    <p class="text-sm text-[#4B8B43]">Dibatalkan</p>
                    <p class="mt-2 text-4xl font-black text-[#0B5D1E]">{{ $cancelledCount }}</p>
                </div>
            </div>

            <section class="rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-xl shadow-[#1B5E20]/10 sm:p-8">
                <div class="mb-6 flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                    <div>
                        <h2 class="text-2xl font-black text-[#0B5D1E]">Semua Match</h2>
                        <p class="text-[#4B8B43]">Urutan terbaru berdasarkan tanggal pertandingan.</p>
                    </div>
                </div>

                @if($matches->count() > 0)
                    <div class="grid gap-4">
                        @foreach($matches as $match)
                            @php($opponent = $match->team_a_id === $team->id ? $match->teamB : $match->teamA)
                            <div class="rounded-2xl border border-[#DDEED8] bg-white p-5 transition hover:bg-[#F8FCF4]">
                                <div class="grid gap-4 lg:grid-cols-[1.2fr_0.9fr_auto] lg:items-center">
                                    <div class="flex flex-wrap items-center gap-3">
                                        <div class="grid h-12 w-12 place-items-center rounded-2xl border border-[#C8E6C9] bg-[#F1F8E9] text-sm font-black text-[#0B5D1E]">
                                            {{ strtoupper(substr($opponent?->name ?? 'M', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-black text-[#0B5D1E]">vs {{ $opponent?->name ?? 'Menunggu Lawan' }}</p>
                                            <p class="text-sm text-[#4B8B43]">{{ $match->field?->name ?? 'Lapangan belum dipilih' }}</p>
                                        </div>
                                    </div>

                                    <div>
                                        <p class="text-sm font-bold text-[#0B5D1E]">{{ $match->match_date->format('d M Y') }} pukul {{ \Carbon\Carbon::parse($match->start_time)->format('H:i') }}</p>
                                        <p class="text-sm text-[#4B8B43]">{{ $match->duration_minutes }} menit</p>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                                        <span class="rounded-full px-3 py-1.5 text-xs font-bold {{ $match->status === 'completed' ? 'bg-green-100 text-green-700' : ($match->status === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-[#F1F8E9] text-[#2E7D32]') }}">
                                            {{ $match->team_b_id ? ucfirst($match->status) : 'Terbuka' }}
                                        </span>
                                        <a href="{{ route('matches.show', $match) }}" class="rounded-xl bg-[#F1F8E9] px-4 py-2 text-sm font-bold text-[#2E7D32] transition hover:bg-[#E4F2DE]">
                                            Detail
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-2xl border border-[#C8E6C9] bg-[#F1F8E9] p-6 text-center text-[#4B8B43]">
                        Belum ada history pertandingan.
                    </div>
                @endif
            </section>
        </section>
    </main>
</div>
@endsection
