@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#F1F8E9]">
    <x-navbar />

    <main class="container mx-auto px-6 pb-12 pt-24">
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
                    <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#4B8B43]">Buat Pertandingan</p>
                    <h1 class="mt-2 text-4xl font-black text-[#0B5D1E]">Atur tantangan pertandingan</h1>
                    <p class="mt-2 max-w-2xl text-[#4B8B43]">Pilih lapangan, tanggal, dan jam. MatchGo akan membuat tantangan terbuka agar tim lain bisa mengambil pertandingan.</p>
                </div>
                <a href="{{ route('matches.take') }}" class="inline-flex items-center justify-center rounded-2xl bg-white px-5 py-3 text-sm font-bold text-[#2E7D32] shadow-sm ring-1 ring-[#DDEED8] transition hover:bg-[#F8FCF4]">
                    Lihat Tantangan
                </a>
            </div>

            <div class="grid gap-6 lg:grid-cols-[1.55fr_0.95fr]">
                <form method="POST" action="{{ route('matches.store') }}" class="rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-xl shadow-[#1B5E20]/10 sm:p-8">
                    @csrf

                    <div class="mb-8 rounded-2xl border border-[#C8E6C9] bg-[#F1F8E9] p-5">
                        <p class="text-sm text-[#4B8B43]">Tim Pembuat</p>
                        <div class="mt-3 flex items-center gap-4">
                            @if($team->logo_url)
                                <img src="{{ asset('storage/' . $team->logo_url) }}" alt="{{ $team->name }}" class="h-16 w-16 rounded-2xl border border-[#C8E6C9] object-cover">
                            @else
                                <div class="grid h-16 w-16 place-items-center rounded-2xl border border-[#C8E6C9] bg-[#F8FCF4] text-xl font-black text-[#0B5D1E]">
                                    {{ strtoupper(substr($team->name, 0, 1)) }}
                                </div>
                            @endif
                            <div>
                                <p class="text-2xl font-black text-[#0B5D1E]">{{ $team->name }}</p>
                                <p class="text-sm capitalize text-[#4B8B43]">{{ str_replace('_', ' ', $team->skill_level) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid gap-5">
                        <div>
                            <label class="mb-2 block text-sm font-bold text-[#1B5E20]">Lapangan</label>
                            <select id="field_id" name="field_id" required class="w-full rounded-2xl border border-[#C8E6C9] bg-[#F8FCF4] px-4 py-3 text-[#0B5D1E] outline-none transition focus:border-[#2E8B3C] focus:ring-2 focus:ring-[#4CAF50]/20 @error('field_id') border-red-500 @enderror">
                                <option value="" data-price="0">Pilih lapangan tersedia...</option>
                                @foreach($fields as $field)
                                    <option value="{{ $field->id }}" data-price="{{ $field->price_per_hour }}" {{ old('field_id') == $field->id ? 'selected' : '' }}>
                                        {{ $field->name }} - Rp {{ number_format($field->price_per_hour, 0, ',', '.') }}/jam
                                    </option>
                                @endforeach
                            </select>
                            @error('field_id') <span class="mt-1 block text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid gap-5 md:grid-cols-3">
                            <div>
                                <label class="mb-2 block text-sm font-bold text-[#1B5E20]">Tanggal</label>
                                <input type="date" name="match_date" value="{{ old('match_date', now()->addDays(1)->format('Y-m-d')) }}" required class="w-full rounded-2xl border border-[#C8E6C9] bg-[#F8FCF4] px-4 py-3 text-[#0B5D1E] outline-none transition focus:border-[#2E8B3C] focus:ring-2 focus:ring-[#4CAF50]/20 @error('match_date') border-red-500 @enderror" />
                                @error('match_date') <span class="mt-1 block text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold text-[#1B5E20]">Jam Mulai</label>
                                <input type="time" name="start_time" value="{{ old('start_time', '19:00') }}" required class="w-full rounded-2xl border border-[#C8E6C9] bg-[#F8FCF4] px-4 py-3 text-[#0B5D1E] outline-none transition focus:border-[#2E8B3C] focus:ring-2 focus:ring-[#4CAF50]/20 @error('start_time') border-red-500 @enderror" />
                                @error('start_time') <span class="mt-1 block text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-bold text-[#1B5E20]">Durasi</label>
                                <select id="duration_minutes" name="duration_minutes" required class="w-full rounded-2xl border border-[#C8E6C9] bg-[#F8FCF4] px-4 py-3 text-[#0B5D1E] outline-none transition focus:border-[#2E8B3C] focus:ring-2 focus:ring-[#4CAF50]/20 @error('duration_minutes') border-red-500 @enderror">
                                    <option value="60" {{ old('duration_minutes', 60) == 60 ? 'selected' : '' }}>60 menit</option>
                                    <option value="120" {{ old('duration_minutes') == 120 ? 'selected' : '' }}>120 menit</option>
                                </select>
                                @error('duration_minutes') <span class="mt-1 block text-sm text-red-500">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="mt-8 w-full rounded-2xl bg-[#2E8B3C] px-5 py-4 text-base font-black text-white shadow-md shadow-[#1B5E20]/15 transition hover:bg-[#23742F]">
                        Buat Pertandingan
                    </button>
                </form>

                <aside class="grid gap-6">
                    <div class="rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-xl shadow-[#1B5E20]/10">
                        <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#4B8B43]">Ringkasan Biaya</p>
                        <div class="mt-5 grid gap-3">
                            <div class="rounded-2xl bg-[#F1F8E9] p-4">
                                <p class="text-xs font-semibold text-[#4B8B43]">Total Biaya</p>
                                <p id="total_cost" class="mt-1 text-3xl font-black text-[#0B5D1E]">Rp 0</p>
                            </div>
                            <div class="rounded-2xl bg-[#F1F8E9] p-4">
                                <p class="text-xs font-semibold text-[#4B8B43]">Biaya Per Tim</p>
                                <p id="cost_per_team" class="mt-1 text-3xl font-black text-[#0B5D1E]">Rp 0</p>
                            </div>
                            <div class="rounded-2xl bg-[#F1F8E9] p-4">
                                <p class="text-xs font-semibold text-[#4B8B43]">Status Awal</p>
                                <p class="mt-1 text-2xl font-black text-[#0B5D1E]">Terbuka</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-xl shadow-[#1B5E20]/10">
                        <p class="text-sm font-bold text-[#1B5E20]">Cara kerja tantangan</p>
                        <div class="mt-4 space-y-3 text-sm text-[#4B8B43]">
                            <p>1. Captain membuat jadwal dan memilih lapangan.</p>
                            <p>2. Tantangan muncul di halaman Cari Pertandingan.</p>
                            <p>3. Tim lawan mengambil tantangan, lalu biaya dibagi untuk dua tim.</p>
                        </div>
                    </div>
                </aside>
            </div>

            <section class="mt-10 rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-xl shadow-[#1B5E20]/10 sm:p-8">
                <div class="mb-6">
                    <h2 class="text-2xl font-black text-[#0B5D1E]">Tantangan Kamu</h2>
                    <p class="text-[#4B8B43]">Pertandingan terbuka yang dibuat tim kamu dan masih menunggu lawan.</p>
                </div>

                @if($myChallenges->count() > 0)
                    <div class="grid gap-4 md:grid-cols-2">
                        @foreach($myChallenges as $challenge)
                            <div class="rounded-2xl border border-[#DDEED8] bg-white p-5 transition hover:bg-[#F8FCF4]">
                                <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-center">
                                    <div>
                                        <p class="font-black text-[#0B5D1E]">{{ $challenge->field?->name ?? 'Lapangan belum dipilih' }}</p>
                                        <p class="text-sm text-[#4B8B43]">{{ $challenge->match_date->format('d M Y') }} pukul {{ \Carbon\Carbon::parse($challenge->start_time)->format('H:i') }}</p>
                                        <p class="text-sm text-[#4B8B43]">{{ $challenge->duration_minutes }} menit</p>
                                    </div>

                                    <div class="flex flex-wrap items-center gap-2 lg:justify-end">
                                        <span class="rounded-full bg-[#F1F8E9] px-3 py-1.5 text-xs font-bold text-[#2E7D32]">
                                            Menunggu Lawan
                                        </span>
                                        <a href="{{ route('matches.show', $challenge) }}" class="rounded-xl bg-[#F1F8E9] px-4 py-2 text-sm font-bold text-[#2E7D32] transition hover:bg-[#E4F2DE]">
                                            Detail
                                        </a>
                                        <form method="POST" action="{{ route('matches.cancel', $challenge) }}" onsubmit="return confirm('Batalkan tantangan ini?')">
                                            @csrf
                                            <button type="submit" class="rounded-xl border border-red-200 bg-white px-4 py-2 text-sm font-bold text-red-600 transition hover:bg-red-50">
                                                Batalkan
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="rounded-2xl border border-[#C8E6C9] bg-[#F1F8E9] p-6 text-center text-[#4B8B43]">
                        Belum ada tantangan aktif dari tim kamu.
                    </div>
                @endif
            </section>

        </section>
    </main>
</div>

<script>
    const rupiah = (value) => new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);

    const updateCostPreview = () => {
        const field = document.getElementById('field_id');
        const duration = document.getElementById('duration_minutes');
        const selected = field.options[field.selectedIndex];
        const price = Number(selected?.dataset.price || 0);
        const hours = Math.ceil(Number(duration.value || 0) / 60);
        const total = price * hours;

        document.getElementById('total_cost').textContent = rupiah(total);
        document.getElementById('cost_per_team').textContent = rupiah(Math.round(total / 2));
    };

    document.getElementById('field_id').addEventListener('change', updateCostPreview);
    document.getElementById('duration_minutes').addEventListener('change', updateCostPreview);
    updateCostPreview();
</script>
@endsection
