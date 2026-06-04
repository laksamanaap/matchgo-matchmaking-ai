@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#F1F8E9]">
    <x-navbar />

    <main class="container mx-auto px-6 pb-12 pt-24">
        <section class="mx-auto max-w-6xl">
            <div class="mb-6 rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-xl shadow-[#1B5E20]/10 sm:p-8">
                <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#4B8B43]">Match Center</p>
                        <h1 class="mt-2 text-4xl font-black text-[#0B5D1E]">Kelola pertandingan tim kamu</h1>
                        <p class="mt-2 max-w-2xl text-[#4B8B43]">Buat tantangan, ambil pertandingan dari tim lain, atau gunakan AutoMatching untuk cari lawan hari ini.</p>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-3 lg:min-w-[520px]">
                        <a href="{{ route('matches.auto') }}" class="rounded-2xl bg-[#2E8B3C] px-5 py-4 text-center text-sm font-black text-white transition hover:bg-[#23742F]">
                            AutoMatching
                        </a>
                        <a href="{{ route('matches.create') }}" class="rounded-2xl bg-[#F1F8E9] px-5 py-4 text-center text-sm font-black text-[#1B5E20] ring-1 ring-[#C8E6C9] transition hover:bg-[#E4F2DE]">
                            Buat Tantangan
                        </a>
                        <a href="{{ route('matches.take') }}" class="rounded-2xl bg-white px-5 py-4 text-center text-sm font-black text-[#2E7D32] ring-1 ring-[#C8E6C9] transition hover:bg-[#F8FCF4]">
                            Cari Tantangan
                        </a>
                    </div>
                </div>
            </div>

            @if(! $team->isVerified())
                <div class="mb-6 rounded-2xl border border-yellow-200 bg-yellow-50 p-5 text-yellow-800">
                    Tim kamu belum diverifikasi admin. Setelah verified, kamu bisa membuat tantangan, mengambil tantangan, dan memakai AutoMatching.
                </div>
            @endif

            @if(! $canUseMatchFeatures)
                <div class="mb-6 rounded-2xl border border-yellow-200 bg-yellow-50 p-5 text-yellow-800">
                    Tim kamu baru memiliki {{ $team->activePlayerCount() }} pemain termasuk captain. Minimal 5 pemain diperlukan.
                </div>
            @endif

            <div class="mb-8 grid gap-4 md:grid-cols-3">
                <div class="rounded-3xl border border-[#DDEED8] bg-white p-5 shadow-lg shadow-[#1B5E20]/5">
                    <p class="text-sm text-[#4B8B43]">Tantangan Kamu</p>
                    <p class="mt-2 text-4xl font-black text-[#0B5D1E]">{{ $myChallenges->count() }}</p>
                </div>
                <div class="rounded-3xl border border-[#DDEED8] bg-white p-5 shadow-lg shadow-[#1B5E20]/5">
                    <p class="text-sm text-[#4B8B43]">Tantangan Tersedia</p>
                    <p class="mt-2 text-4xl font-black text-[#0B5D1E]">{{ $openChallenges->count() }}</p>
                </div>
                <div class="rounded-3xl border border-[#DDEED8] bg-white p-5 shadow-lg shadow-[#1B5E20]/5">
                    <p class="text-sm text-[#4B8B43]">Riwayat Match</p>
                    <p class="mt-2 text-4xl font-black text-[#0B5D1E]">{{ $myMatches->count() }}</p>
                </div>
            </div>

            <div class="grid gap-8 lg:grid-cols-[1.1fr_0.9fr]">
                <section class="rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-xl shadow-[#1B5E20]/10">
                    <div class="mb-5 flex items-end justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-black text-[#0B5D1E]">Tantangan Dari Tim Lain</h2>
                            <p class="text-sm text-[#4B8B43]">Ambil jadwal yang cocok untuk tim kamu.</p>
                        </div>
                        <a href="{{ route('matches.take') }}" class="text-sm font-bold text-[#2E7D32]">Lihat semua</a>
                    </div>

                    @if($openChallenges->count() > 0)
                        <div class="grid gap-4">
                            @foreach($openChallenges->take(4) as $challenge)
                                <div class="rounded-2xl border border-[#DDEED8] bg-white p-5 transition hover:bg-[#F8FCF4]">
                                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                                        <div>
                                            <p class="text-sm text-[#4B8B43]">Dibuat oleh</p>
                                            <h3 class="text-xl font-black text-[#0B5D1E]">{{ $challenge->teamA->name }}</h3>
                                            <p class="mt-1 text-sm text-[#4B8B43]">{{ $challenge->match_date->format('d M Y') }} pukul {{ \Carbon\Carbon::parse($challenge->start_time)->format('H:i') }}</p>
                                            <p class="text-sm text-[#4B8B43]">{{ $challenge->field?->name ?? 'Lapangan belum dipilih' }}</p>
                                        </div>
                                        <div class="grid gap-2 sm:grid-cols-2 md:min-w-[220px]">
                                            <a href="{{ route('matches.show', $challenge) }}" class="rounded-xl bg-[#F1F8E9] px-4 py-2 text-center text-sm font-bold text-[#2E7D32] transition hover:bg-[#E4F2DE]">
                                                Detail
                                            </a>
                                            @if($team->isVerified() && $canUseMatchFeatures)
                                                <form method="POST" action="{{ route('matches.accept', $challenge) }}">
                                                    @csrf
                                                    <button type="submit" class="w-full rounded-xl bg-[#2E8B3C] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#23742F]">
                                                        Ambil
                                                    </button>
                                                </form>
                                            @else
                                                <button type="button" disabled class="rounded-xl bg-gray-200 px-4 py-2 text-sm font-bold text-gray-500">
                                                    Terkunci
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="rounded-2xl border border-[#C8E6C9] bg-[#F1F8E9] p-8 text-center text-[#4B8B43]">
                            Belum ada tantangan terbuka dari tim lain.
                        </div>
                    @endif
                </section>

                <aside class="grid gap-8">
                    <section class="rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-xl shadow-[#1B5E20]/10">
                        <div class="mb-5">
                            <h2 class="text-2xl font-black text-[#0B5D1E]">Tantangan Kamu</h2>
                            <p class="text-sm text-[#4B8B43]">Pertandingan terbuka yang menunggu lawan.</p>
                        </div>

                        @if($myChallenges->count() > 0)
                            <div class="grid gap-3">
                                @foreach($myChallenges->take(4) as $challenge)
                                    <div class="rounded-2xl bg-[#F1F8E9] p-4">
                                        <p class="font-black text-[#0B5D1E]">{{ $challenge->field?->name ?? 'Lapangan' }}</p>
                                        <p class="text-sm text-[#4B8B43]">{{ $challenge->match_date->format('d M Y') }} pukul {{ \Carbon\Carbon::parse($challenge->start_time)->format('H:i') }}</p>
                                        <div class="mt-3 flex gap-2">
                                            <a href="{{ route('matches.show', $challenge) }}" class="rounded-xl bg-white px-3 py-2 text-sm font-bold text-[#2E7D32]">Detail</a>
                                            <form method="POST" action="{{ route('matches.cancel', $challenge) }}" onsubmit="return confirm('Batalkan tantangan ini?')">
                                                @csrf
                                                <button type="submit" class="rounded-xl bg-red-50 px-3 py-2 text-sm font-bold text-red-600">Batalkan</button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-2xl border border-[#C8E6C9] bg-[#F1F8E9] p-6 text-center text-[#4B8B43]">
                                Belum ada tantangan aktif.
                            </div>
                        @endif
                    </section>

                    <section class="rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-xl shadow-[#1B5E20]/10">
                        <div class="mb-5">
                            <h2 class="text-2xl font-black text-[#0B5D1E]">Match Terbaru</h2>
                            <p class="text-sm text-[#4B8B43]">Pertandingan tim kamu yang sudah punya lawan.</p>
                        </div>

                        @if($myMatches->count() > 0)
                            <div class="grid gap-3">
                                @foreach($myMatches as $match)
                                    @php($opponent = $match->team_a_id === $team->id ? $match->teamB : $match->teamA)
                                    <a href="{{ route('matches.show', $match) }}" class="block rounded-2xl bg-[#F1F8E9] p-4 transition hover:bg-[#E4F2DE]">
                                        <p class="font-black text-[#0B5D1E]">vs {{ $opponent?->name ?? 'Tim lawan' }}</p>
                                        <p class="text-sm text-[#4B8B43]">{{ $match->match_date->format('d M Y') }} pukul {{ \Carbon\Carbon::parse($match->start_time)->format('H:i') }}</p>
                                    </a>
                                @endforeach
                            </div>
                        @else
                            <div class="rounded-2xl border border-[#C8E6C9] bg-[#F1F8E9] p-6 text-center text-[#4B8B43]">
                                Belum ada match terbaru.
                            </div>
                        @endif
                    </section>
                </aside>
            </div>
        </section>
    </main>
</div>
@endsection
