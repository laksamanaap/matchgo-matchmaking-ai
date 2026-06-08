@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#F1F8E9]">
    <x-navbar />

    <main class="container mx-auto px-6 pb-12 pt-24">
        <section class="mx-auto max-w-6xl">
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

            <section class="rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-xl shadow-[#1B5E20]/10">
                <div class="mb-5">
                    <h2 class="text-2xl font-black text-[#0B5D1E]">Pertandingan Mendatang</h2>
                    <p class="text-sm text-[#4B8B43]">Match tim kamu yang sudah punya lawan dan belum lewat waktu main.</p>
                </div>

                @if($myMatches->count() > 0)
                    <div class="grid gap-3 md:grid-cols-2">
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
                        Belum ada pertandingan mendatang.
                    </div>
                @endif
            </section>
        </section>
    </main>
</div>
@endsection
