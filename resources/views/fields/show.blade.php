@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#F1F8E9]">
    <x-navbar />

    <main class="container mx-auto px-6 pb-12 pt-24">
        <div class="mx-auto max-w-5xl">
            <a href="{{ url()->previous() }}" class="mb-5 inline-flex items-center gap-2 text-sm font-bold text-[#2E7D32] transition hover:text-[#0B5D1E]">
                <x-heroicon-o-arrow-left class="h-5 w-5" />
                Kembali
            </a>

            {{-- Galeri (1 besar + 4 kecil = 5 slot, placeholder kalau kosong) --}}
            @php
                $gallery = $field->gallery_urls;
                $placeholder = asset('placeholder-image.png');
            @endphp
            <div class="grid h-80 grid-cols-2 gap-2 sm:h-[26rem] sm:grid-cols-4 sm:grid-rows-2">
                <a @if($gallery[0] !== $placeholder) href="{{ $gallery[0] }}" data-gallery="venue-gallery" @endif
                    class="relative col-span-2 row-span-2 block overflow-hidden rounded-3xl bg-[#E8F0E4] {{ $gallery[0] !== $placeholder ? 'glightbox cursor-zoom-in' : '' }}">
                    <img src="{{ $gallery[0] }}" alt="{{ $field->name }}" class="h-full w-full object-cover"
                        onerror="this.onerror=null;this.src='{{ $placeholder }}';" />
                    <div class="pointer-events-none absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/75 to-transparent p-6">
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1 text-xs font-black uppercase tracking-wide text-white {{ $field->is_available ? 'bg-[#2E8B3C]' : 'bg-gray-500' }}">
                            <span class="h-1.5 w-1.5 rounded-full bg-white"></span>
                            {{ $field->is_available ? 'Tersedia' : 'Tidak Tersedia' }}
                        </span>
                        <h1 class="mt-2 text-3xl font-black text-white sm:text-4xl">{{ $field->name }}</h1>
                        <p class="mt-1 flex items-center gap-1.5 text-sm font-semibold text-white/90">
                            <x-heroicon-o-map-pin class="h-4 w-4" />
                            {{ $field->city }}
                        </p>
                    </div>
                </a>

                @for($i = 1; $i < 5; $i++)
                    <a @if($gallery[$i] !== $placeholder) href="{{ $gallery[$i] }}" data-gallery="venue-gallery" @endif
                        class="hidden overflow-hidden rounded-2xl bg-[#E8F0E4] sm:block {{ $gallery[$i] !== $placeholder ? 'glightbox cursor-zoom-in' : '' }}">
                        <img src="{{ $gallery[$i] }}" alt="{{ $field->name }} {{ $i + 1 }}" class="h-full w-full object-cover transition duration-300 hover:scale-105"
                            onerror="this.onerror=null;this.src='{{ $placeholder }}';" />
                    </a>
                @endfor
            </div>

            <div class="mt-6 grid gap-6 lg:grid-cols-[1.4fr_1fr]">
                {{-- Info --}}
                <div class="grid gap-6">
                    <div class="rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-sm">
                        <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#4B8B43]">Harga Sewa</p>
                        <p class="mt-1 text-4xl font-black text-[#0B5D1E]">Rp {{ number_format($field->price_per_hour, 0, ',', '.') }}<span class="text-base font-bold text-[#4B8B43]">/jam</span></p>
                    </div>

                    <div class="grid gap-4 rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-sm sm:grid-cols-2">
                        <div class="flex items-start gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-[#F1F8E9] text-[#2E7D32]">
                                <x-heroicon-o-map-pin class="h-5 w-5" />
                            </span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-[#4B8B43]">Alamat</p>
                                <p class="text-sm font-semibold text-[#0B5D1E]">{{ $field->address ?? '-' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-[#F1F8E9] text-[#2E7D32]">
                                <x-heroicon-o-phone class="h-5 w-5" />
                            </span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-[#4B8B43]">Kontak</p>
                                <p class="text-sm font-semibold text-[#0B5D1E]">{{ $field->contact_phone ?: '-' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-[#F1F8E9] text-[#2E7D32]">
                                <x-heroicon-o-clock class="h-5 w-5" />
                            </span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-[#4B8B43]">Jam Operasional</p>
                                <p class="text-sm font-semibold text-[#0B5D1E]">
                                    @if($field->open_time && $field->close_time)
                                        {{ \Illuminate\Support\Str::of($field->open_time)->substr(0, 5) }} - {{ \Illuminate\Support\Str::of($field->close_time)->substr(0, 5) }}
                                    @else
                                        24 Jam / Sesuai Jadwal
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-[#F1F8E9] text-[#2E7D32]">
                                <x-heroicon-o-building-office-2 class="h-5 w-5" />
                            </span>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-[#4B8B43]">Kota</p>
                                <p class="text-sm font-semibold text-[#0B5D1E]">{{ $field->city }}</p>
                            </div>
                        </div>
                    </div>

                    <a href="{{ route('matches.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#2E8B3C] px-5 py-4 text-base font-black text-white shadow-md shadow-[#1B5E20]/15 transition hover:bg-[#23742F]">
                        <x-heroicon-o-plus-circle class="h-5 w-5" />
                        Buat Pertandingan di Sini
                    </a>
                </div>

                {{-- Map --}}
                <div class="rounded-3xl border border-[#DDEED8] bg-white p-4 shadow-sm">
                    <p class="mb-3 px-2 pt-2 text-sm font-bold text-[#1B5E20]">Lokasi Lapangan</p>
                    @if($field->latitude && $field->longitude)
                        <div id="field-detail-map" class="z-0 h-80 w-full overflow-hidden rounded-2xl border border-[#DDEED8]"></div>
                    @else
                        <div class="grid h-80 w-full place-items-center rounded-2xl border border-dashed border-[#C8E6C9] bg-[#F8FCF4] text-sm font-semibold text-[#4B8B43]">
                            Koordinat lapangan belum tersedia.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
</div>

@if($field->latitude && $field->longitude)
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
    <script>
        (function () {
            function init() {
                if (typeof L === 'undefined') { return window.setTimeout(init, 100); }
                const el = document.getElementById('field-detail-map');
                if (!el || el.dataset.init) return;
                el.dataset.init = '1';

                const lat = {{ $field->latitude }}, lng = {{ $field->longitude }};
                const map = L.map(el).setView([lat, lng], 15);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; OpenStreetMap contributors',
                }).addTo(map);

                const icon = L.icon({
                    iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                    iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
                    shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                    iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41],
                });
                L.marker([lat, lng], { icon }).addTo(map).bindPopup(@json($field->name));

                window.setTimeout(() => map.invalidateSize(), 200);
            }
            document.addEventListener('DOMContentLoaded', init);
        })();
    </script>
@endif

{{-- Lightbox galeri dengan zoom in / zoom out --}}
<div id="zoom-lightbox" class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/90">
    <div class="absolute right-4 top-4 z-20 flex items-center gap-2">
        <button type="button" data-zoom-out class="grid h-10 w-10 place-items-center rounded-full bg-white/10 text-white transition hover:bg-white/20" aria-label="Perkecil">
            <x-heroicon-o-minus class="h-5 w-5" />
        </button>
        <button type="button" data-zoom-reset class="grid h-10 min-w-16 place-items-center rounded-full bg-white/10 px-3 text-xs font-bold text-white transition hover:bg-white/20" aria-label="Reset zoom">
            <span id="zoom-level">100%</span>
        </button>
        <button type="button" data-zoom-in class="grid h-10 w-10 place-items-center rounded-full bg-white/10 text-white transition hover:bg-white/20" aria-label="Perbesar">
            <x-heroicon-o-plus class="h-5 w-5" />
        </button>
        <button type="button" data-lb-close class="grid h-10 w-10 place-items-center rounded-full bg-white/10 text-white transition hover:bg-white/20" aria-label="Tutup">
            <x-heroicon-o-x-mark class="h-5 w-5" />
        </button>
    </div>

    <button type="button" data-lb-prev class="absolute left-3 top-1/2 z-10 grid h-12 w-12 -translate-y-1/2 place-items-center rounded-full bg-white/10 text-white transition hover:bg-white/20 sm:left-6" aria-label="Sebelumnya">
        <x-heroicon-o-chevron-left class="h-7 w-7" />
    </button>

    <div id="zoom-stage" class="flex h-full w-full items-center justify-center overflow-hidden p-6">
        <img id="zoom-img" src="" alt="" draggable="false" class="max-h-[85vh] max-w-[90vw] select-none rounded-lg object-contain shadow-2xl will-change-transform" />
    </div>

    <button type="button" data-lb-next class="absolute right-3 top-1/2 z-10 grid h-12 w-12 -translate-y-1/2 place-items-center rounded-full bg-white/10 text-white transition hover:bg-white/20 sm:right-6" aria-label="Berikutnya">
        <x-heroicon-o-chevron-right class="h-7 w-7" />
    </button>

    <span id="zoom-counter" class="absolute bottom-5 left-1/2 -translate-x-1/2 rounded-full bg-white/10 px-3 py-1 text-sm font-bold text-white"></span>
</div>

<script>
    (function () {
        const links = Array.from(document.querySelectorAll('a.glightbox[href]'));
        if (! links.length) return;

        const images = links.map((a) => a.getAttribute('href'));
        const box = document.getElementById('zoom-lightbox');
        const img = document.getElementById('zoom-img');
        const counter = document.getElementById('zoom-counter');
        const levelEl = document.getElementById('zoom-level');
        let idx = 0, scale = 1, tx = 0, ty = 0, dragging = false, startX = 0, startY = 0;

        const MIN = 1, MAX = 5, STEP = 0.5;

        const apply = () => {
            img.style.transform = `translate(${tx}px, ${ty}px) scale(${scale})`;
            img.style.cursor = scale > 1 ? 'grab' : 'zoom-in';
            levelEl.textContent = Math.round(scale * 100) + '%';
        };
        const reset = () => { scale = 1; tx = 0; ty = 0; apply(); };
        const render = () => { img.src = images[idx]; counter.textContent = `${idx + 1} / ${images.length}`; reset(); };
        const open = (i) => { idx = i; render(); box.classList.remove('hidden'); box.classList.add('flex'); document.body.style.overflow = 'hidden'; };
        const close = () => { box.classList.add('hidden'); box.classList.remove('flex'); document.body.style.overflow = ''; };
        const next = () => { idx = (idx + 1) % images.length; render(); };
        const prev = () => { idx = (idx - 1 + images.length) % images.length; render(); };
        const zoomIn = () => { scale = Math.min(+(scale + STEP).toFixed(2), MAX); apply(); };
        const zoomOut = () => { scale = Math.max(+(scale - STEP).toFixed(2), MIN); if (scale === 1) { tx = 0; ty = 0; } apply(); };

        links.forEach((a, i) => a.addEventListener('click', (e) => { e.preventDefault(); open(i); }));
        box.querySelector('[data-zoom-in]').addEventListener('click', zoomIn);
        box.querySelector('[data-zoom-out]').addEventListener('click', zoomOut);
        box.querySelector('[data-zoom-reset]').addEventListener('click', reset);
        box.querySelector('[data-lb-close]').addEventListener('click', close);
        box.querySelector('[data-lb-next]').addEventListener('click', next);
        box.querySelector('[data-lb-prev]').addEventListener('click', prev);

        box.addEventListener('click', (e) => { if (e.target === box || e.target.id === 'zoom-stage') close(); });
        img.addEventListener('wheel', (e) => { e.preventDefault(); e.deltaY < 0 ? zoomIn() : zoomOut(); }, { passive: false });
        img.addEventListener('dblclick', () => { scale > 1 ? reset() : zoomIn(); });

        img.addEventListener('mousedown', (e) => { if (scale <= 1) return; dragging = true; startX = e.clientX - tx; startY = e.clientY - ty; img.style.cursor = 'grabbing'; e.preventDefault(); });
        window.addEventListener('mousemove', (e) => { if (! dragging) return; tx = e.clientX - startX; ty = e.clientY - startY; apply(); });
        window.addEventListener('mouseup', () => { dragging = false; if (scale > 1) img.style.cursor = 'grab'; });

        document.addEventListener('keydown', (e) => {
            if (box.classList.contains('hidden')) return;
            if (e.key === 'Escape') close();
            else if (e.key === 'ArrowRight') next();
            else if (e.key === 'ArrowLeft') prev();
            else if (e.key === '+' || e.key === '=') zoomIn();
            else if (e.key === '-' || e.key === '_') zoomOut();
        });
    })();
</script>
@endsection
