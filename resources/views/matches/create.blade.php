@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#F1F8E9]">
    <x-navbar />

    <main class="container mx-auto px-6 pb-12 pt-24">
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
                            @php
                                $teamLogoExists = $team->logo_url && \Illuminate\Support\Facades\Storage::disk('public')->exists($team->logo_url);
                            @endphp
                            @if($teamLogoExists)
                                <img src="{{ asset('storage/' . $team->logo_url) }}" alt="{{ $team->name }}" class="h-16 w-16 rounded-2xl border border-[#C8E6C9] object-contain p-2">
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
                            <div id="field-combobox" class="relative">
                                <input type="hidden" name="field_id" id="field_id" value="{{ old('field_id') }}" data-price="0">

                                <div class="flex gap-2">
                                    <button type="button" id="field-trigger" aria-haspopup="listbox" aria-expanded="false"
                                        class="flex flex-1 items-center justify-between gap-3 rounded-2xl border border-[#C8E6C9] bg-[#F8FCF4] px-4 py-3 text-left text-[#0B5D1E] outline-none transition focus:border-[#2E8B3C] focus:ring-2 focus:ring-[#4CAF50]/20 @error('field_id') border-red-500 @enderror">
                                        <span id="field-trigger-label" class="truncate text-[#7BA877]">Pilih lapangan tersedia...</span>
                                        <x-heroicon-o-chevron-up-down class="h-5 w-5 shrink-0 text-[#4B8B43]" />
                                    </button>
                                    <button type="button" id="field-map-open"
                                        class="inline-flex shrink-0 items-center gap-2 rounded-2xl bg-[#2E8B3C] px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#23742F]">
                                        <x-heroicon-o-map class="h-5 w-5" />
                                        <span class="hidden sm:inline">Pilih lewat Peta</span>
                                    </button>
                                </div>

                                <div id="field-panel" class="absolute z-30 mt-2 hidden w-full overflow-hidden rounded-2xl border border-[#C8E6C9] bg-white shadow-xl shadow-[#1B5E20]/10">
                                    <div class="flex items-center gap-2 border-b border-[#EAF4E6] px-3 py-2.5">
                                        <x-heroicon-o-magnifying-glass class="h-4 w-4 shrink-0 text-[#4B8B43]" />
                                        <input type="text" id="field-search" autocomplete="off" placeholder="Cari nama, kota, atau alamat lapangan..."
                                            class="w-full bg-transparent text-sm text-[#0B5D1E] outline-none placeholder:text-[#9CC298]" />
                                    </div>

                                    <ul id="field-options" role="listbox" class="max-h-72 overflow-y-auto overflow-x-hidden p-1.5">
                                        @foreach($fields as $field)
                                            @include('matches.partials.field-option', ['field' => $field])
                                        @endforeach
                                    </ul>
                                    <p id="field-empty" class="hidden px-4 py-6 text-center text-sm font-semibold text-[#4B8B43]">Lapangan tidak ditemukan.</p>
                                </div>
                            </div>
                            @error('field_id') <span class="mt-1 block text-sm text-red-500">{{ $message }}</span> @enderror
                        </div>

                        {{-- Modal peta untuk memilih lapangan --}}
                        <div id="field-map-modal" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/50 p-3 sm:p-6">
                            <div class="flex h-[88vh] w-full max-w-5xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl">
                                <div class="flex items-center justify-between gap-4 border-b border-[#EAF4E6] px-5 py-4">
                                    <div>
                                        <h3 class="text-lg font-black text-[#0B5D1E]">Pilih Lapangan di Peta</h3>
                                        <p class="text-sm text-[#4B8B43]">Klik pin lalu "Pilih lapangan ini", atau pilih dari daftar.</p>
                                    </div>
                                    <button type="button" id="field-map-close" class="grid h-10 w-10 place-items-center rounded-xl text-[#2E7D32] transition hover:bg-[#F1F8E9]">
                                        <x-heroicon-o-x-mark class="h-6 w-6" />
                                    </button>
                                </div>

                                <div class="flex min-h-0 flex-1 flex-col md:flex-row">
                                    <div id="field-modal-map" class="z-0 h-1/2 w-full md:h-full md:w-3/5"></div>

                                    <div class="flex min-h-0 w-full flex-col border-t border-[#EAF4E6] md:w-2/5 md:border-l md:border-t-0">
                                        <div class="flex items-center gap-2 border-b border-[#EAF4E6] px-3 py-2.5">
                                            <x-heroicon-o-magnifying-glass class="h-4 w-4 shrink-0 text-[#4B8B43]" />
                                            <input type="text" id="field-modal-search" autocomplete="off" placeholder="Cari nama, kota, atau alamat..."
                                                class="w-full bg-transparent text-sm text-[#0B5D1E] outline-none placeholder:text-[#9CC298]" />
                                        </div>
                                        <ul id="field-modal-options" class="min-h-0 flex-1 overflow-y-auto overflow-x-hidden p-1.5">
                                            @foreach($fields as $field)
                                                @include('matches.partials.field-option', ['field' => $field, 'itemClass' => 'field-modal-option'])
                                            @endforeach
                                        </ul>
                                        <p id="field-modal-empty" class="hidden px-4 py-6 text-center text-sm font-semibold text-[#4B8B43]">Lapangan tidak ditemukan.</p>
                                    </div>
                                </div>
                            </div>
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
                        Buat Pertandingan & Bayar DP
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
                                <p class="text-xs font-semibold text-[#4B8B43]">Wajib Dibayar Sekarang</p>
                                <p id="pay_now" class="mt-1 text-3xl font-black text-[#0B5D1E]">Rp 0</p>
                                <p class="mt-1 text-xs font-semibold text-[#4B8B43]">DP 50% + biaya pengelola web 10%</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-xl shadow-[#1B5E20]/10">
                        <p class="text-sm font-bold text-[#1B5E20]">Cara kerja tantangan</p>
                        <div class="mt-4 space-y-3 text-sm text-[#4B8B43]">
                            <p>1. Captain membuat jadwal, memilih lapangan, lalu membayar DP 50% + biaya pengelola web 10%.</p>
                            <p>2. Tantangan muncul di halaman Cari Pertandingan.</p>
                            <p>3. Pembatalan bisa direfund maksimal 6 jam setelah pertandingan dibuat. Lewat dari itu DP hangus.</p>
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

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

<script>
    const rupiah = (value) => new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);

    const fieldInput = document.getElementById('field_id');
    const durationSelect = document.getElementById('duration_minutes');

    const updateCostPreview = () => {
        const price = Number(fieldInput.dataset.price || 0);
        const hours = Math.ceil(Number(durationSelect.value || 0) / 60);
        const total = price * hours;
        const costPerTeam = Math.round(total / 2);
        const dp = Math.ceil(costPerTeam * 0.5);
        const webFee = Math.ceil(total * 0.1);

        document.getElementById('total_cost').textContent = rupiah(total);
        document.getElementById('cost_per_team').textContent = rupiah(costPerTeam);
        document.getElementById('pay_now').textContent = rupiah(dp + webFee);
    };

    // Field selection: combobox list + map modal
    (function () {
        const combo = document.getElementById('field-combobox');
        const trigger = document.getElementById('field-trigger');
        const label = document.getElementById('field-trigger-label');
        const panel = document.getElementById('field-panel');
        const search = document.getElementById('field-search');
        const emptyState = document.getElementById('field-empty');
        const comboOptions = Array.from(document.querySelectorAll('#field-options .field-option'));
        const modalOptions = Array.from(document.querySelectorAll('#field-modal-options .field-modal-option'));
        const allOptions = comboOptions.concat(modalOptions);
        let activeIndex = -1;

        const teamLat = {{ $team->latitude ?? 'null' }};
        const teamLng = {{ $team->longitude ?? 'null' }};

        // ── Selection (shared by both lists + map) ──
        const selectByValue = (value) => {
            const src = allOptions.find((o) => o.dataset.value === value);
            if (!src) return;
            allOptions.forEach((o) => o.setAttribute('aria-selected', o.dataset.value === value ? 'true' : 'false'));
            fieldInput.value = value;
            fieldInput.dataset.price = src.dataset.price;
            label.textContent = src.dataset.label;
            label.classList.remove('text-[#7BA877]');
            label.classList.add('text-[#0B5D1E]', 'font-semibold');
            updateCostPreview();
        };

        // ── Combobox dropdown (text search) ──
        const visibleOptions = () => comboOptions.filter((o) => !o.parentElement.classList.contains('hidden'));
        const setActive = (index) => {
            const vis = visibleOptions();
            comboOptions.forEach((o) => o.classList.remove('bg-[#F1F8E9]'));
            activeIndex = index;
            if (vis[index]) { vis[index].classList.add('bg-[#F1F8E9]'); vis[index].scrollIntoView({ block: 'nearest' }); }
        };
        const filterCombo = (term) => {
            const q = term.trim().toLowerCase();
            let shown = 0;
            comboOptions.forEach((o) => {
                const match = o.dataset.search.includes(q);
                o.parentElement.classList.toggle('hidden', !match);
                if (match) shown++;
            });
            emptyState.classList.toggle('hidden', shown > 0);
            setActive(shown > 0 ? 0 : -1);
        };
        const openPanel = () => {
            panel.classList.remove('hidden');
            trigger.setAttribute('aria-expanded', 'true');
            search.value = '';
            filterCombo('');
            setTimeout(() => search.focus(), 0);
        };
        const closePanel = () => {
            panel.classList.add('hidden');
            trigger.setAttribute('aria-expanded', 'false');
            activeIndex = -1;
        };

        trigger.addEventListener('click', () => { panel.classList.contains('hidden') ? openPanel() : closePanel(); });
        search.addEventListener('input', (e) => filterCombo(e.target.value));
        search.addEventListener('keydown', (e) => {
            const vis = visibleOptions();
            if (e.key === 'ArrowDown') { e.preventDefault(); setActive(Math.min(activeIndex + 1, vis.length - 1)); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); setActive(Math.max(activeIndex - 1, 0)); }
            else if (e.key === 'Enter') { e.preventDefault(); if (vis[activeIndex]) { selectByValue(vis[activeIndex].dataset.value); closePanel(); } }
            else if (e.key === 'Escape') { closePanel(); trigger.focus(); }
        });
        comboOptions.forEach((o) => o.addEventListener('click', () => { selectByValue(o.dataset.value); closePanel(); }));
        document.addEventListener('click', (e) => { if (!combo.contains(e.target)) closePanel(); });

        // ── Map modal ──
        const modal = document.getElementById('field-map-modal');
        const openBtn = document.getElementById('field-map-open');
        const closeBtn = document.getElementById('field-map-close');
        const mapEl = document.getElementById('field-modal-map');
        const mSearch = document.getElementById('field-modal-search');
        const mEmpty = document.getElementById('field-modal-empty');
        const markers = new Map();
        let modalMap = null;

        const buildModalMap = () => {
            if (modalMap || typeof L === 'undefined' || !mapEl) return;

            const center = (teamLat && teamLng) ? [teamLat, teamLng] : [-6.2, 106.8];
            modalMap = L.map(mapEl).setView(center, 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(modalMap);

            const icon = L.icon({
                iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
                shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41],
            });

            const bounds = [];
            if (teamLat && teamLng) {
                L.circleMarker([teamLat, teamLng], { radius: 8, color: '#1B5E20', weight: 2, fillColor: '#4CAF50', fillOpacity: 0.9 })
                    .addTo(modalMap).bindPopup('<b>Lokasi Tim</b>');
                bounds.push([teamLat, teamLng]);
            }

            modalOptions.forEach((o) => {
                const lat = parseFloat(o.dataset.lat), lng = parseFloat(o.dataset.lng);
                if (Number.isNaN(lat) || Number.isNaN(lng)) return;
                const price = o.dataset.label.split(' - ')[1] || '';
                const m = L.marker([lat, lng], { icon }).addTo(modalMap);
                m.bindPopup(`<b>${o.dataset.name}</b><br>${price}<br><a href="#" data-select="${o.dataset.value}" style="color:#2E7D32;font-weight:700;">Pilih lapangan ini</a> &nbsp;·&nbsp; <a href="${o.dataset.detail}" target="_blank" style="color:#2E7D32;font-weight:700;">Detail</a>`);
                markers.set(o.dataset.value, m);
                bounds.push([lat, lng]);
            });

            if (bounds.length) modalMap.fitBounds(bounds, { padding: [40, 40], maxZoom: 14 });

            modalMap.on('popupopen', (e) => {
                const link = e.popup.getElement()?.querySelector('[data-select]');
                if (!link) return;
                link.addEventListener('click', (ev) => {
                    ev.preventDefault();
                    selectByValue(link.dataset.select);
                    closeModal();
                }, { once: true });
            });
        };

        const filterModal = (term) => {
            const q = term.trim().toLowerCase();
            let shown = 0;
            modalOptions.forEach((o) => {
                const match = o.dataset.search.includes(q);
                o.parentElement.classList.toggle('hidden', !match);
                const marker = markers.get(o.dataset.value);
                if (marker && modalMap) {
                    if (match && !modalMap.hasLayer(marker)) marker.addTo(modalMap);
                    if (!match && modalMap.hasLayer(marker)) modalMap.removeLayer(marker);
                }
                if (match) shown++;
            });
            mEmpty.classList.toggle('hidden', shown > 0);
        };

        const openModal = () => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            buildModalMap();
            mSearch.value = '';
            filterModal('');
            setTimeout(() => modalMap && modalMap.invalidateSize(), 150);
        };
        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        openBtn.addEventListener('click', () => { closePanel(); openModal(); });
        closeBtn.addEventListener('click', closeModal);
        modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal(); });
        mSearch.addEventListener('input', (e) => filterModal(e.target.value));
        modalOptions.forEach((o) => o.addEventListener('click', () => { selectByValue(o.dataset.value); closeModal(); }));

        // Preselect from old('field_id') after validation error
        if (fieldInput.value) selectByValue(fieldInput.value);
    })();

    durationSelect.addEventListener('change', updateCostPreview);
    updateCostPreview();
</script>
@endsection
