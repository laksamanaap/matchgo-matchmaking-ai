@props([
    'mapId' => 'location-map',
    'latId',
    'lngId',
    'domicileId' => null,
    'defaultLat' => -6.2,
    'defaultLng' => 106.8,
])

@once
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
@endonce

<div>
    <label class="mb-2 block text-sm font-semibold text-[#2E7D32]">Pilih Lokasi di Peta</label>

    {{-- Pencarian tempat (kota, provinsi, alamat) via Nominatim --}}
    <div class="relative z-[1100] mb-2">
        <div class="flex items-center gap-2 rounded-2xl border border-[#81C784]/40 bg-white px-3 py-2.5">
            <svg class="h-4 w-4 shrink-0 text-[#4B8B43]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
            </svg>
            <input type="text" id="{{ $mapId }}-search" autocomplete="off" placeholder="Cari kota, provinsi, atau tempat..."
                class="w-full bg-transparent text-sm text-[#0B5D1E] outline-none placeholder:text-[#9CC298]" />
        </div>
        <ul id="{{ $mapId }}-results" class="absolute left-0 right-0 top-full z-[1100] mt-1 hidden max-h-60 overflow-y-auto rounded-2xl border border-[#C8E6C9] bg-white p-1.5 shadow-xl"></ul>
    </div>

    <div class="relative">
        <div id="{{ $mapId }}" class="z-0 h-64 w-full overflow-hidden rounded-2xl border border-[#81C784]/40"></div>
        <button type="button" id="{{ $mapId }}-geo"
            class="absolute right-3 top-3 z-[1000] inline-flex items-center gap-1.5 rounded-xl bg-white/95 px-3 py-2 text-xs font-bold text-[#2E7D32] shadow-md ring-1 ring-[#81C784]/40 backdrop-blur hover:bg-[#F1F8E9]">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8a4 4 0 100 8 4 4 0 000-8zM12 2v3M12 19v3M2 12h3M19 12h3" />
            </svg>
            Gunakan Lokasi Saat Ini
        </button>
    </div>

    <div class="mt-3 flex items-start gap-3 rounded-2xl border border-[#C8E6C9] bg-[#F1F8E9] px-4 py-3">
        <svg class="mt-0.5 h-5 w-5 shrink-0 text-[#2E7D32]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <div>
            <p class="text-sm font-bold text-[#1B5E20]">Lokasi Terpilih</p>
            <p id="{{ $mapId }}-coords" class="text-sm text-[#2E7D32]/80">Klik peta, geser pin, atau cari lokasi.</p>
        </div>
    </div>
</div>

<script>
    (function () {
        function init() {
            if (typeof L === 'undefined') { return window.setTimeout(init, 100); }

            const mapEl = document.getElementById(@json($mapId));
            if (!mapEl || mapEl.dataset.init) return;
            mapEl.dataset.init = '1';

            const latEl = document.getElementById(@json($latId));
            const lngEl = document.getElementById(@json($lngId));
            const domicileEl = @json($domicileId) ? document.getElementById(@json($domicileId)) : null;
            const coordsEl = document.getElementById(@json($mapId . '-coords'));
            const geoBtn = document.getElementById(@json($mapId . '-geo'));
            const searchEl = document.getElementById(@json($mapId . '-search'));
            const resultsEl = document.getElementById(@json($mapId . '-results'));

            const fmt = (n) => Number(n).toFixed(6);
            const startLat = parseFloat(latEl?.value) || {{ $defaultLat }};
            const startLng = parseFloat(lngEl?.value) || {{ $defaultLng }};

            const map = L.map(mapEl).setView([startLat, startLng], 13);
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
            const marker = L.marker([startLat, startLng], { draggable: true, icon }).addTo(map);

            const pickCity = (a = {}) => a.city || a.town || a.village || a.suburb || a.county || a.state || '';

            async function reverseGeocode(lat, lng) {
                if (!domicileEl) return;
                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`, { headers: { Accept: 'application/json' } });
                    const data = await res.json();
                    const city = pickCity(data.address || {});
                    if (city) domicileEl.value = city;
                } catch (e) { /* abaikan */ }
            }

            function apply(lat, lng, geocode = true) {
                if (latEl) latEl.value = fmt(lat);
                if (lngEl) lngEl.value = fmt(lng);
                if (coordsEl) coordsEl.textContent = `Latitude: ${fmt(lat)}, Longitude: ${fmt(lng)}`;
                if (geocode) reverseGeocode(fmt(lat), fmt(lng));
            }

            if (latEl?.value && lngEl?.value) {
                coordsEl.textContent = `Latitude: ${fmt(latEl.value)}, Longitude: ${fmt(lngEl.value)}`;
            }

            map.on('click', (e) => { marker.setLatLng(e.latlng); apply(e.latlng.lat, e.latlng.lng); });
            marker.on('dragend', () => { const p = marker.getLatLng(); apply(p.lat, p.lng); });

            [latEl, lngEl].forEach((el) => el?.addEventListener('change', () => {
                const lat = parseFloat(latEl.value), lng = parseFloat(lngEl.value);
                if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
                    marker.setLatLng([lat, lng]);
                    map.setView([lat, lng], map.getZoom());
                    coordsEl.textContent = `Latitude: ${fmt(lat)}, Longitude: ${fmt(lng)}`;
                }
            }));

            // ── Tombol "Gunakan Lokasi Saat Ini" ──
            geoBtn?.addEventListener('click', () => {
                if (!navigator.geolocation) return;
                const original = geoBtn.innerHTML;
                geoBtn.disabled = true;
                geoBtn.textContent = 'Mengambil lokasi...';
                navigator.geolocation.getCurrentPosition((pos) => {
                    const lat = pos.coords.latitude, lng = pos.coords.longitude;
                    marker.setLatLng([lat, lng]);
                    map.setView([lat, lng], 15);
                    apply(lat, lng);
                    geoBtn.disabled = false;
                    geoBtn.innerHTML = original;
                }, () => {
                    geoBtn.disabled = false;
                    geoBtn.innerHTML = original;
                }, { enableHighAccuracy: true, timeout: 10000, maximumAge: 60000 });
            });

            // ── Pencarian tempat (Nominatim) ──
            let searchTimer = null;
            const renderResults = (items) => {
                resultsEl.innerHTML = '';
                if (!items.length) { resultsEl.classList.add('hidden'); return; }
                items.forEach((it) => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'flex w-full items-start gap-2 rounded-xl px-3 py-2 text-left text-sm text-[#0B5D1E] transition hover:bg-[#F1F8E9]';
                    btn.innerHTML = `<svg class="mt-0.5 h-4 w-4 shrink-0 text-[#4B8B43]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg><span>${it.display_name}</span>`;
                    btn.addEventListener('click', () => {
                        const lat = parseFloat(it.lat), lng = parseFloat(it.lon);
                        marker.setLatLng([lat, lng]);
                        map.setView([lat, lng], 14);
                        apply(lat, lng, false);
                        if (domicileEl) domicileEl.value = pickCity(it.address || {}) || it.display_name.split(',')[0];
                        searchEl.value = it.display_name.split(',').slice(0, 2).join(',').trim();
                        resultsEl.classList.add('hidden');
                    });
                    resultsEl.appendChild(btn);
                });
                resultsEl.classList.remove('hidden');
            };

            const doSearch = async (q) => {
                try {
                    const res = await fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(q)}&limit=6&addressdetails=1&countrycodes=id`, { headers: { Accept: 'application/json' } });
                    renderResults(await res.json());
                } catch (e) { /* abaikan */ }
            };

            searchEl?.addEventListener('input', () => {
                window.clearTimeout(searchTimer);
                const q = searchEl.value.trim();
                if (q.length < 3) { resultsEl.classList.add('hidden'); resultsEl.innerHTML = ''; return; }
                searchTimer = window.setTimeout(() => doSearch(q), 400);
            });

            document.addEventListener('click', (e) => {
                if (resultsEl && !resultsEl.contains(e.target) && e.target !== searchEl) {
                    resultsEl.classList.add('hidden');
                }
            });

            // Perbaiki ukuran tile saat peta tampil setelah sempat tersembunyi (mis. dalam modal)
            if ('IntersectionObserver' in window) {
                new IntersectionObserver((entries) => {
                    entries.forEach((en) => { if (en.isIntersecting) map.invalidateSize(); });
                }).observe(mapEl);
            }
            window.setTimeout(() => map.invalidateSize(), 300);
        }

        document.addEventListener('DOMContentLoaded', init);
    })();
</script>
