@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#F1F8E9]">
    <x-navbar />

    <main class="container mx-auto px-6 pb-12 pt-24">
        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-[#C8E6C9] bg-white p-4 text-sm text-[#1B5E20] shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <section class="mx-auto max-w-6xl">
            <div class="mb-6">
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#4B8B43]">Pembayaran</p>
                <h1 class="mt-2 text-4xl font-black text-[#0B5D1E]">Tagihan pertandingan</h1>
                <p class="mt-2 max-w-2xl text-[#4B8B43]">Pertandingan biasa memakai biaya per tim + biaya pengelola web 10%. AutoMatching wajib lunas 100% + biaya admin 10%.</p>
            </div>

            @if($matches->count() > 0)
                <div class="grid gap-5">
                    @foreach($matches as $match)
                        @php
                            $booking = $match->booking;
                            $cost = $match->matchCost;
                            $opponent = $match->team_a_id === $team->id ? $match->teamB : $match->teamA;
                            $payment = $booking?->payments?->firstWhere('team_id', $team->id);
                            $isAutoMatch = $match->isAutoMatch();
                        $basePayment = $isAutoMatch
                            ? ($cost?->cost_per_team ?? 0)
                            : ($cost?->dp_per_team ?? ($cost?->cost_per_team ?? 0));
                            $fee = $cost?->handling_fee ?? (int) ceil($basePayment * 0.1);
                            $amount = $basePayment + $fee;
                        @endphp

                        <div class="rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-xl shadow-[#1B5E20]/10">
                            <div class="grid gap-5 lg:grid-cols-[1.2fr_0.8fr_auto] lg:items-center">
                                <div>
                                    <p class="text-sm font-bold text-[#4B8B43]">{{ $match->match_date->format('d M Y') }} pukul {{ \Carbon\Carbon::parse($match->start_time)->format('H:i') }}</p>
                                    <h2 class="mt-1 text-2xl font-black text-[#0B5D1E]">vs {{ $opponent?->name ?? 'Tim lawan' }}</h2>
                                    <p class="mt-1 text-sm text-[#4B8B43]">{{ $match->field?->name ?? $booking?->field?->name ?? 'Lapangan belum tersedia' }}</p>
                                    @if($isAutoMatch)
                                        <span class="mt-3 inline-flex rounded-full bg-[#E4F2DE] px-3 py-1 text-xs font-black text-[#2E7D32]">AutoMatching</span>
                                    @endif
                                </div>

                                <div class="grid gap-2 rounded-2xl bg-[#F1F8E9] p-4">
                                    <div class="flex justify-between gap-4 text-sm">
                                        <span class="text-[#4B8B43]">{{ $isAutoMatch ? 'Pelunasan 100%' : 'Biaya Per Tim' }}</span>
                                        <strong class="text-[#0B5D1E]">Rp {{ number_format($basePayment, 0, ',', '.') }}</strong>
                                    </div>
                                    <div class="flex justify-between gap-4 text-sm">
                                        <span class="text-[#4B8B43]">{{ $isAutoMatch ? 'Biaya Admin 10%' : 'Biaya Pengelola Web 10%' }}</span>
                                        <strong class="text-[#0B5D1E]">Rp {{ number_format($fee, 0, ',', '.') }}</strong>
                                    </div>
                                    <div class="flex justify-between gap-4 border-t border-[#C8E6C9] pt-2 text-sm">
                                        <span class="font-bold text-[#1B5E20]">Total Bayar</span>
                                        <strong class="text-[#0B5D1E]">Rp {{ number_format($amount, 0, ',', '.') }}</strong>
                                    </div>
                                </div>

                                <div class="lg:min-w-56">
                                    @if($payment?->payment_status === 'paid')
                                        <div class="rounded-2xl bg-green-50 px-5 py-4 text-center font-black text-green-700 ring-1 ring-green-200">
                                            Sudah Dibayar
                                        </div>
                                    @elseif($payment?->payment_status === 'refunded')
                                        <div class="rounded-2xl bg-blue-50 px-5 py-4 text-center font-black text-blue-700 ring-1 ring-blue-200">
                                            Sudah Direfund
                                        </div>
                                    @elseif($booking && $amount > 0)
                                        <form method="POST" action="{{ route('payments.store') }}" class="grid gap-3">
                                            @csrf
                                            <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                                            <select name="payment_method" required class="w-full rounded-2xl border border-[#C8E6C9] bg-[#F8FCF4] px-4 py-3 text-sm font-bold text-[#0B5D1E] outline-none focus:border-[#2E8B3C]">
                                                <option value="bank_transfer">Bank Transfer</option>
                                                <option value="e-wallet">E-Wallet</option>
                                                <option value="cash">Cash</option>
                                            </select>
                                            <button type="submit" class="w-full rounded-2xl bg-[#2E8B3C] px-5 py-3 text-sm font-black text-white transition hover:bg-[#23742F]">
                                                Bayar Sekarang
                                            </button>
                                        </form>
                                    @else
                                        <div class="rounded-2xl bg-yellow-50 px-5 py-4 text-center font-black text-yellow-700 ring-1 ring-yellow-200">
                                            Belum Ada Tagihan
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="rounded-3xl border border-[#DDEED8] bg-white p-10 text-center shadow-xl shadow-[#1B5E20]/10">
                    <p class="text-lg font-bold text-[#0B5D1E]">Belum ada tagihan pembayaran.</p>
                </div>
            @endif
        </section>
    </main>
</div>
@endsection
