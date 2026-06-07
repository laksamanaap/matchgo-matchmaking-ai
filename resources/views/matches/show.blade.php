@extends('layouts.app')

@php
    use Illuminate\Support\Facades\Storage;

    $teamA = $match->teamA;
    $teamB = $match->teamB;
    $field = $match->booking?->field ?? $match->field;
    $booking = $match->booking;
    $cost = $match->matchCost;
    $startAt = \Carbon\Carbon::parse($match->match_date->toDateString() . ' ' . $match->start_time);
    $currentTeamId = auth()->user()?->team?->id;
    $canManageMatch = $teamA && (auth()->id() === $teamA->owner_id || auth()->id() === $teamB?->owner_id);
    $currentPayment = $booking?->payments?->firstWhere('team_id', $currentTeamId);
    $basePayment = $match->isAutoMatch()
        ? ($cost?->cost_per_team ?? 0)
        : ($cost?->dp_per_team ?? (int) ceil(($cost?->cost_per_team ?? 0) * 0.5));
    $handlingFee = $cost?->handling_fee ?? (int) ceil(($cost?->total_cost ?? 0) * 0.1);
    $payableAmount = $basePayment + $handlingFee;

    $statusLabels = [
        'scheduled' => $teamB ? 'Dijadwalkan' : 'Menunggu Lawan',
        'pending' => 'Menunggu Pembayaran',
        'confirmed' => 'Terkonfirmasi',
        'ongoing' => 'Berlangsung',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
        'expired' => 'Kedaluwarsa',
    ];

    $statusLabel = $statusLabels[$match->status] ?? ucfirst($match->status);
    $statusClass = match ($match->status) {
        'completed', 'confirmed' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
        'cancelled', 'expired' => 'bg-red-100 text-red-700 ring-red-200',
        'pending' => 'bg-yellow-100 text-yellow-700 ring-yellow-200',
        'ongoing' => 'bg-blue-100 text-blue-700 ring-blue-200',
        default => 'bg-lime-100 text-[#2E7D32] ring-lime-200',
    };

    $steps = [
        [
            'title' => 'Tantangan Dikirim',
            'caption' => $match->created_at?->format('d M Y, H:i') ?? '-',
            'active' => true,
            'icon' => 'paper-airplane',
        ],
        [
            'title' => 'Match Dijadwalkan',
            'caption' => $match->status === 'scheduled' && ! $teamB ? 'Menunggu lawan' : 'Status: ' . $statusLabel,
            'active' => in_array($match->status, ['scheduled', 'pending', 'confirmed', 'ongoing', 'completed'], true),
            'icon' => 'calendar',
        ],
        [
            'title' => 'Pembayaran',
            'caption' => $match->status === 'pending' ? 'Menunggu pembayaran kedua tim' : ($cost ? 'Biaya tercatat' : 'Belum tersedia'),
            'active' => in_array($match->status, ['pending', 'confirmed', 'ongoing', 'completed'], true),
            'icon' => 'banknotes',
        ],
        [
            'title' => 'Match Selesai',
            'caption' => $match->status === 'completed' ? 'Selesai' : 'Menunggu',
            'active' => $match->status === 'completed',
            'icon' => 'trophy',
        ],
        [
            'title' => 'Terverifikasi',
            'caption' => $match->status === 'completed' ? 'Menunggu admin' : 'Menunggu',
            'active' => false,
            'icon' => 'shield-check',
        ],
    ];

    $teamLogo = function ($team) {
        if (! $team?->logo_url || ! Storage::disk('public')->exists($team->logo_url)) {
            return null;
        }

        return asset('storage/' . $team->logo_url);
    };
@endphp

@section('content')
<div class="min-h-screen bg-[#F2F8EC] text-[#14351d]">
    <x-navbar />

    <main class="mx-auto max-w-7xl px-4 pb-12 pt-24 sm:px-6 lg:px-8">
        <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm font-semibold text-[#737a6e]">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1 transition hover:text-[#2E7D32]">
                <x-heroicon-o-home class="h-4 w-4" />
                Dashboard
            </a>
            <x-heroicon-o-chevron-right class="h-4 w-4 text-[#a1a89b]" />
            <span class="text-[#1f241d]">Pertandingan</span>
        </nav>

        <section class="overflow-hidden rounded-[2rem] bg-gradient-to-br from-[#1D7A36] via-[#2E8B3C] to-[#7DBB43] p-1 shadow-xl shadow-[#1B5E20]/12">
            <div class="relative overflow-hidden rounded-[1.75rem] bg-white/10 px-6 py-7 text-white sm:px-9">
                <div class="absolute inset-0 field-pattern opacity-20"></div>
                <div class="relative flex justify-center">
                    <div class="flex w-full max-w-xl items-center justify-center gap-3 text-[#14351d] sm:gap-5">
                        <div class="w-36 rounded-3xl bg-white p-4 text-center shadow-xl shadow-[#0B3D1F]/15 sm:w-44">
                        @if($teamLogo($teamA))
                            <img src="{{ $teamLogo($teamA) }}" alt="{{ $teamA->name }}" class="mx-auto h-16 w-16 rounded-2xl border border-[#DDEED8] bg-white object-cover p-1 shadow-sm">
                        @else
                            <div class="mx-auto grid h-16 w-16 place-items-center rounded-2xl border border-[#DDEED8] bg-[#E9F5E2] text-2xl font-black text-[#2E7D32] shadow-sm">
                                {{ strtoupper(substr($teamA?->name ?? 'T', 0, 1)) }}
                            </div>
                        @endif
                        <p class="mt-3 truncate text-sm font-black leading-tight">{{ $teamA?->name ?? 'Tim A' }}</p>
                        <p class="mt-1 text-[0.68rem] font-black uppercase tracking-[0.16em] text-[#7A8474]">Tim Saya</p>
                    </div>

                        <div class="grid h-12 w-12 shrink-0 place-items-center rounded-full bg-[#F1F8E9] text-sm font-black uppercase text-[#2E7D32] shadow-md shadow-[#0B3D1F]/10 ring-4 ring-white/35">vs</div>

                        <div class="w-36 rounded-3xl bg-white p-4 text-center shadow-xl shadow-[#0B3D1F]/15 sm:w-44">
                        @if($teamLogo($teamB))
                            <img src="{{ $teamLogo($teamB) }}" alt="{{ $teamB->name }}" class="mx-auto h-16 w-16 rounded-2xl border border-[#DDEED8] bg-white object-cover p-1 shadow-sm">
                        @else
                            <div class="mx-auto grid h-16 w-16 place-items-center rounded-2xl border border-[#DDEED8] bg-white text-2xl font-black text-[#9aa093] shadow-sm">
                                ?
                            </div>
                        @endif
                        <p class="mt-3 truncate text-sm font-black leading-tight">{{ $teamB?->name ?? 'Menunggu Lawan' }}</p>
                        <p class="mt-1 text-[0.68rem] font-black uppercase tracking-[0.16em] text-[#7A8474]">Lawan</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="mt-8 grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
            <section class="overflow-hidden rounded-[1.75rem] border border-[#DFE8D8] bg-white shadow-lg shadow-[#1B5E20]/7">
                <div class="flex items-center gap-2 bg-[#FAFCF6] px-6 py-5">
                    <x-heroicon-o-information-circle class="h-5 w-5 text-[#6F945D]" />
                    <h2 class="text-lg font-black">Detail Pertandingan</h2>
                </div>

                <div class="grid gap-0 px-6 py-2 sm:grid-cols-2">
                    <div class="border-b border-[#E4ECDf] py-4 sm:border-r sm:pr-5">
                        <span class="font-semibold text-[#6e7569]">Tim Kandang</span>
                        <p class="mt-1 font-black">{{ $teamA?->name ?? '-' }}</p>
                    </div>
                    <div class="border-b border-[#E4ECDf] py-4 sm:pl-5">
                        <span class="font-semibold text-[#6e7569]">Tim Tamu</span>
                        <p class="mt-1 font-black">{{ $teamB?->name ?? 'Menunggu Lawan' }}</p>
                    </div>
                    <div class="border-b border-[#E4ECDf] py-4 sm:border-r sm:pr-5">
                        <span class="font-semibold text-[#6e7569]">Tanggal & Waktu</span>
                        <p class="mt-1 font-black">{{ $startAt->format('d M Y, H:i') }}</p>
                    </div>
                    <div class="border-b border-[#E4ECDf] py-4 sm:pl-5">
                        <span class="font-semibold text-[#6e7569]">Durasi</span>
                        <p class="mt-1 font-black">{{ $match->duration_minutes }} menit</p>
                    </div>
                    <div class="py-4 sm:border-r sm:pr-5">
                        <span class="font-semibold text-[#6e7569]">Lokasi</span>
                        <p class="mt-1 font-black">{{ $field?->name ?? '-' }}</p>
                    </div>
                    <div class="py-4 sm:pl-5">
                        <span class="font-semibold text-[#6e7569]">Status</span>
                        <p class="mt-2"><span class="rounded-full px-3 py-1 text-xs font-black ring-1 {{ $statusClass }}">{{ $statusLabel }}</span></p>
                    </div>
                </div>

                @if($field || $booking)
                    <div class="border-t border-[#DFE8D8] bg-[#FAFCF6] px-6 py-6">
                        <h3 class="text-lg font-black text-[#0B5D1E]">Info Lapangan</h3>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div>
                                <p class="text-sm font-semibold text-[#6F945D]">Lapangan</p>
                                <p class="mt-1 font-black text-[#0B5D1E]">{{ $field?->name ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-[#6F945D]">Jam Booking</p>
                                <p class="mt-1 font-black text-[#0B5D1E]">
                                    @if($booking)
                                        {{ $booking->start_at->format('H:i') }} - {{ $booking->start_at->copy()->addHours($booking->duration_hours)->format('H:i') }}
                                    @else
                                        -
                                    @endif
                                </p>
                            </div>
                            <div class="sm:col-span-2">
                                <p class="text-sm font-semibold text-[#6F945D]">Alamat</p>
                                <p class="mt-1 font-black text-[#0B5D1E]">{{ $field?->address ?? '-' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-[#6F945D]">Status Booking</p>
                                <span class="mt-2 inline-flex rounded-xl bg-emerald-500 px-4 py-2 text-sm font-black text-white">
                                    {{ ucfirst($booking?->status ?? 'belum tersedia') }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endif
            </section>

            <aside class="rounded-[1.75rem] border border-[#DFE8D8] bg-white p-6 shadow-lg shadow-[#1B5E20]/7">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-list-bullet class="h-5 w-5 text-[#6F945D]" />
                    <h2 class="text-lg font-black">Progress Match</h2>
                </div>

                <div class="mt-7 space-y-6">
                    @foreach($steps as $index => $step)
                        <div class="relative flex gap-4">
                            @if(! $loop->last)
                                <div class="absolute left-5 top-10 h-9 w-px bg-[#DFE8D8]"></div>
                            @endif
                            <div class="grid h-10 w-10 shrink-0 place-items-center rounded-full border {{ $step['active'] ? 'border-[#C8E6C9] bg-[#EDF6E8] text-[#2E7D32]' : 'border-[#E4E8DF] bg-[#F5F7F1] text-[#A4AA9D]' }}">
                                @if($step['icon'] === 'paper-airplane')
                                    <x-heroicon-o-paper-airplane class="h-5 w-5" />
                                @elseif($step['icon'] === 'calendar')
                                    <x-heroicon-o-calendar-days class="h-5 w-5" />
                                @elseif($step['icon'] === 'banknotes')
                                    <x-heroicon-o-banknotes class="h-5 w-5" />
                                @elseif($step['icon'] === 'trophy')
                                    <x-heroicon-o-trophy class="h-5 w-5" />
                                @else
                                    <x-heroicon-o-shield-check class="h-5 w-5" />
                                @endif
                            </div>
                            <div>
                                <p class="font-black {{ $step['active'] ? 'text-[#1f241d]' : 'text-[#777f70]' }}">{{ $step['title'] }}</p>
                                <p class="mt-1 text-sm leading-5 text-[#777f70]">{{ $step['caption'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-8 grid gap-3">
                    @if($match->status === 'pending' && ! $match->isAutoMatch() && $canManageMatch)
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-1">
                            <form method="POST" action="{{ route('matches.auto_confirm', $match) }}">
                                @csrf
                                <button type="submit" class="w-full rounded-2xl bg-[#2E8B3C] px-5 py-3 text-sm font-black text-white transition hover:bg-[#23742F]">
                                    Accept
                                </button>
                            </form>
                            <form method="POST" action="{{ route('matches.auto_reject', $match) }}">
                                @csrf
                                <button type="submit" class="w-full rounded-2xl bg-red-500 px-5 py-3 text-sm font-black text-white transition hover:bg-red-600">
                                    Reject
                                </button>
                            </form>
                        </div>
                    @elseif(in_array($match->status, ['scheduled', 'confirmed'], true) && ! $match->isAutoMatch() && $canManageMatch)
                        <form method="POST" action="{{ route('matches.cancel', $match) }}" onsubmit="return confirm('Batalkan pertandingan?')">
                            @csrf
                            <button type="submit" class="w-full rounded-2xl bg-red-500 px-5 py-3 text-sm font-black text-white transition hover:bg-red-600">
                                Batalkan
                            </button>
                        </form>
                    @endif

                    @if(! $teamB && $currentTeamId !== $match->team_a_id)
                        <form method="POST" action="{{ route('matches.accept', $match) }}">
                            @csrf
                            <button type="submit" class="w-full rounded-2xl bg-[#2E8B3C] px-5 py-3 text-sm font-black text-white transition hover:bg-[#23742F]">
                                Ambil Tantangan
                            </button>
                        </form>
                    @endif
                </div>
            </aside>
        </div>

        <section class="mt-6 overflow-hidden rounded-[1.75rem] border border-[#DFE8D8] bg-white shadow-lg shadow-[#1B5E20]/7">
            <div class="flex flex-wrap items-center justify-between gap-3 bg-[#FAFCF6] px-6 py-5">
                <div class="flex items-center gap-2">
                    <x-heroicon-o-document-text class="h-5 w-5 text-[#6F945D]" />
                    <h2 class="text-lg font-black">Detail Pembayaran</h2>
                </div>
                <span class="rounded-full bg-[#EEF2E8] px-4 py-2 text-xs font-black uppercase tracking-[0.12em] text-[#777f70]">
                    {{ $match->isAutoMatch() ? 'Lunas 100%' : 'Split 50:50' }}
                </span>
            </div>

            @if($cost)
                <div class="grid gap-6 p-6 lg:grid-cols-[1fr_0.9fr]">
                    <div class="divide-y divide-[#E4ECDf]">
                        <div class="flex items-center justify-between gap-5 py-4">
                            <span class="font-semibold text-[#6F945D]">Total Biaya</span>
                            <span class="text-right text-lg font-black text-[#0B5D1E]">Rp {{ number_format($cost->total_cost, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-5 py-4">
                            <span class="font-semibold text-[#6F945D]">Per Tim</span>
                            <span class="text-right text-lg font-black text-[#0B5D1E]">Rp {{ number_format($cost->cost_per_team, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-5 py-4">
                            <span class="font-semibold text-[#6F945D]">{{ $match->isAutoMatch() ? 'Pelunasan Tiap Tim' : 'DP 50% Tiap Tim' }}</span>
                            <span class="text-right text-lg font-black text-[#0B5D1E]">
                                Rp {{ number_format($match->isAutoMatch() ? $cost->cost_per_team : ($cost->dp_per_team ?? (int) ceil($cost->cost_per_team * 0.5)), 0, ',', '.') }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between gap-5 py-4">
                            <span class="font-semibold text-[#6F945D]">{{ $match->isAutoMatch() ? 'Biaya Admin 10%' : 'Biaya Pengelola Web 10%' }}</span>
                            <span class="text-right text-lg font-black text-[#0B5D1E]">Rp {{ number_format($cost->handling_fee ?? (int) ceil($cost->total_cost * 0.1), 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-[#F6FAF0] p-5 text-sm leading-6 text-[#5D8D54]">
                        {{ $cost->payment_notes ?? 'Biaya pertandingan sudah tercatat di sistem MatchGo.' }}
                    </div>
                </div>

                <div class="border-t border-[#DFE8D8] p-6">
                    <div class="grid gap-5 lg:grid-cols-[1fr_22rem] lg:items-center">
                        <div>
                            <h3 class="text-lg font-black text-[#0B5D1E]">Pembayaran Tim Kamu</h3>
                            <p class="mt-1 text-sm font-semibold text-[#6F945D]">
                                Total yang perlu dibayar: <span class="text-[#0B5D1E]">Rp {{ number_format($payableAmount, 0, ',', '.') }}</span>
                            </p>
                        </div>

                        @if($currentPayment?->payment_status === 'paid')
                            <div class="rounded-2xl bg-green-50 px-5 py-4 text-center font-black text-green-700 ring-1 ring-green-200">
                                Sudah Dibayar
                            </div>
                        @elseif($currentPayment?->payment_status === 'refunded')
                            <div class="rounded-2xl bg-blue-50 px-5 py-4 text-center font-black text-blue-700 ring-1 ring-blue-200">
                                Sudah Direfund
                            </div>
                        @elseif($booking && $currentTeamId && in_array($currentTeamId, [$match->team_a_id, $match->team_b_id], true) && $payableAmount > 0)
                            <form method="POST" action="{{ route('payments.store') }}" class="grid gap-3">
                                @csrf
                                <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                                <input type="hidden" name="return_to_match" value="1">
                                <select name="payment_method" required class="w-full rounded-2xl border border-[#C8E6C9] bg-[#F8FCF4] px-4 py-3 text-sm font-bold text-[#0B5D1E] outline-none focus:border-[#2E8B3C]">
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="e-wallet">E-Wallet</option>
                                    <option value="cash">Cash</option>
                                </select>
                                <button type="submit" class="w-full rounded-2xl bg-[#2E8B3C] px-5 py-3 text-sm font-black text-white transition hover:bg-[#23742F]">
                                    Bayar Sekarang
                                </button>
                            </form>
                        @elseif(! $booking)
                            <div class="rounded-2xl bg-yellow-50 px-5 py-4 text-center font-black text-yellow-700 ring-1 ring-yellow-200">
                                Booking Belum Tersedia
                            </div>
                        @else
                            <div class="rounded-2xl bg-[#F6FAF0] px-5 py-4 text-center font-black text-[#6F945D] ring-1 ring-[#DFE8D8]">
                                Tidak Ada Tagihan
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="p-6 text-[#6F945D]">Biaya belum terhitung.</div>
            @endif
        </section>
    </main>
</div>
@endsection
