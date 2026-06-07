@once
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
@endonce

<div>
    <label class="mb-2 block text-sm font-medium leading-6 text-gray-950 dark:text-white">
        Pilih Lokasi di Peta
    </label>

    <div wire:ignore>
        <div
            x-data="{
                map: null,
                marker: null,
                query: '',
                results: [],
                timer: null,
                init() {
                    const load = () => {
                        if (typeof L === 'undefined') { setTimeout(load, 100); return; }

                        const lat = parseFloat($wire.get('data.latitude')) || -6.2;
                        const lng = parseFloat($wire.get('data.longitude')) || 106.8;

                        this.map = L.map(this.$refs.map).setView([lat, lng], 13);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap contributors',
                        }).addTo(this.map);

                        const icon = L.icon({
                            iconUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon.png',
                            iconRetinaUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-icon-2x.png',
                            shadowUrl: 'https://unpkg.com/leaflet@1.9.4/dist/images/marker-shadow.png',
                            iconSize: [25, 41], iconAnchor: [12, 41], popupAnchor: [1, -34], shadowSize: [41, 41],
                        });
                        this.marker = L.marker([lat, lng], { draggable: true, icon }).addTo(this.map);

                        this.map.on('click', (e) => { this.marker.setLatLng(e.latlng); this.apply(e.latlng.lat, e.latlng.lng); });
                        this.marker.on('dragend', () => { const p = this.marker.getLatLng(); this.apply(p.lat, p.lng); });

                        setTimeout(() => this.map.invalidateSize(), 250);
                    };
                    load();
                },
                apply(la, ln) {
                    $wire.set('data.latitude', Number(la).toFixed(8));
                    $wire.set('data.longitude', Number(ln).toFixed(8));
                },
                search() {
                    clearTimeout(this.timer);
                    const q = this.query.trim();
                    if (q.length < 3) { this.results = []; return; }
                    this.timer = setTimeout(async () => {
                        try {
                            const res = await fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&q=${encodeURIComponent(q)}&limit=6&addressdetails=1&countrycodes=id`, { headers: { Accept: 'application/json' } });
                            this.results = await res.json();
                        } catch (e) { this.results = []; }
                    }, 400);
                },
                pick(item) {
                    const lat = parseFloat(item.lat), lng = parseFloat(item.lon);
                    if (this.map) { this.marker.setLatLng([lat, lng]); this.map.setView([lat, lng], 14); }
                    this.apply(lat, lng);
                    const a = item.address || {};
                    const city = a.city || a.town || a.village || a.county || a.state || '';
                    if (city) $wire.set('data.city', city);
                    this.query = item.display_name.split(',').slice(0, 2).join(',').trim();
                    this.results = [];
                },
            }"
        >
            {{-- Pencarian tempat --}}
            <div class="mb-2" style="position:relative;z-index:1100;">
                <div class="fi-input-wrp flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-3 py-2.5 dark:border-white/10 dark:bg-white/5" style="display:flex;align-items:center;gap:0.5rem;">
                    <svg style="width:1.1rem;height:1.1rem;flex:none;color:#9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M11 19a8 8 0 100-16 8 8 0 000 16z" />
                    </svg>
                    <input type="text" x-model="query" @input="search()" autocomplete="off"
                        placeholder="Cari kota, provinsi, atau alamat..."
                        style="width:100%;border:0;background:transparent;padding:0;outline:none;font-size:0.875rem;"
                        class="text-gray-950 dark:text-white" />
                </div>
                <ul x-show="results.length" x-cloak @click.outside="results = []"
                    style="position:absolute;left:0;right:0;top:100%;z-index:1100;margin-top:0.25rem;max-height:15rem;overflow-y:auto;border:1px solid #e5e7eb;border-radius:0.5rem;background:#fff;padding:0.25rem;box-shadow:0 10px 25px rgba(0,0,0,0.15);"
                    class="dark:border-white/10 dark:bg-gray-800">
                    <template x-for="item in results" :key="item.place_id">
                        <li>
                            <button type="button" @click="pick(item)"
                                style="display:flex;align-items:flex-start;gap:0.5rem;width:100%;text-align:left;padding:0.5rem 0.75rem;border-radius:0.375rem;font-size:0.8125rem;"
                                class="text-gray-700 transition hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-white/5">
                                <svg style="width:1rem;height:1rem;flex:none;margin-top:2px;color:#9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span x-text="item.display_name"></span>
                            </button>
                        </li>
                    </template>
                </ul>
            </div>

            <div x-ref="map" style="height: 320px;" class="w-full overflow-hidden rounded-xl border border-gray-300 dark:border-white/10"></div>
        </div>
    </div>

    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
        Cari lokasi, klik peta, atau geser pin untuk mengisi Latitude &amp; Longitude otomatis.
    </p>
</div>
