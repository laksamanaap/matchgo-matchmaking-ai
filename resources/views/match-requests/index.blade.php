@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#F1F8E9]">
    <x-navbar />

    <div class="pt-24 pb-12 container mx-auto px-6">
        @if(session('success'))
            <div class="mb-6 rounded-2xl bg-green-50 border border-green-200 p-4 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-2xl bg-red-50 border border-red-200 p-4 text-red-700">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h1 class="text-4xl font-bold text-[#1B5E20] mb-2">Request Pertandingan</h1>
                <p class="text-[#2E7D32]/80">Pantau request yang dibuat dan request yang masuk untuk {{ $team->name }}.</p>
            </div>
            <a href="{{ route('matchmaking.index') }}" class="inline-flex items-center justify-center rounded-2xl bg-gradient-to-r from-[#2E7D32] to-[#4CAF50] px-5 py-3 text-white font-semibold shadow-lg hover:shadow-xl transition">
                Buat Request
            </a>
        </div>

        @if($requests->count() > 0)
            <div class="grid gap-4">
                @foreach($requests as $request)
                    @php
                        $isOutgoing = $request->requester_team_id === $team->id;
                        $statusClass = match ($request->status) {
                            'accepted' => 'bg-green-500',
                            'rejected' => 'bg-red-500',
                            'cancelled' => 'bg-gray-500',
                            default => 'bg-blue-500',
                        };
                    @endphp

                    <div class="bg-white rounded-3xl shadow-lg p-6">
                        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="text-sm font-semibold text-[#2E7D32]/70">{{ $isOutgoing ? 'Request Keluar' : 'Request Masuk' }}</p>
                                <h2 class="text-2xl font-bold text-[#1B5E20]">
                                    {{ $request->requesterTeam->name }} vs {{ $request->opponentTeam->name }}
                                </h2>
                            </div>
                            <span class="inline-flex w-fit rounded-xl px-4 py-2 text-sm font-semibold text-white {{ $statusClass }}">
                                {{ ucfirst($request->status) }}
                            </span>
                        </div>

                        <div class="mt-6 grid gap-4 md:grid-cols-3">
                            <div class="rounded-2xl bg-[#F1F8E9] p-4">
                                <p class="text-xs text-[#2E7D32]/70">Tanggal Pertandingan</p>
                                <p class="font-bold text-[#1B5E20]">{{ $request->preferred_date->format('d M Y') }}</p>
                            </div>
                            <div class="rounded-2xl bg-[#F1F8E9] p-4">
                                <p class="text-xs text-[#2E7D32]/70">Lokasi Pertandingan</p>
                                <p class="font-bold text-[#1B5E20]">{{ $request->preferred_location ?? '-' }}</p>
                            </div>
                            <div class="rounded-2xl bg-[#F1F8E9] p-4">
                                <p class="text-xs text-[#2E7D32]/70">Dibuat</p>
                                <p class="font-bold text-[#1B5E20]">{{ $request->created_at->format('d M Y H:i') }}</p>
                            </div>
                        </div>

                        @if($isOutgoing && $request->status === 'pending')
                            <form method="POST" action="{{ route('match_requests.cancel', $request) }}" class="mt-6" onsubmit="return confirm('Batalkan request pertandingan ini?')">
                                @csrf
                                <button type="submit" class="rounded-2xl border border-red-200 px-5 py-3 font-semibold text-red-600 hover:bg-red-50 transition">
                                    Batalkan Request
                                </button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-3xl shadow-lg p-10 text-center">
                <p class="text-xl text-[#2E7D32]/80 mb-6">Belum ada request pertandingan.</p>
                <a href="{{ route('matchmaking.index') }}" class="inline-flex rounded-2xl bg-gradient-to-r from-[#2E7D32] to-[#4CAF50] px-6 py-3 text-white font-semibold hover:shadow-lg transition">
                    Cari Lawan
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
