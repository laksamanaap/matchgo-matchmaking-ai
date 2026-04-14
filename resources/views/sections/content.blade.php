{{-- Content Section (Two-Column Layout) --}}
<section id="about" class="py-20 md:py-28 relative field-pattern overflow-hidden">

    {{-- Floating decorations --}}
    <div class="absolute top-20 left-8 text-[#81C784]/20 animate-float-slow rotate-12 pointer-events-none" aria-hidden="true">
        <x-heroicon-o-sparkles class="h-14 w-14" />
    </div>
    <div class="absolute bottom-20 right-8 text-[#4CAF50]/15 animate-float -rotate-6 pointer-events-none" aria-hidden="true">
        <x-heroicon-o-user-group class="h-12 w-12" />
    </div>

    <div class="container mx-auto px-6 lg:px-10">

        {{-- Row 1: Text Left, Visual Right --}}
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center mb-20 md:mb-28">

            {{-- Text --}}
            <div class="space-y-6">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-[#81C784]/15 text-[#2E7D32] text-sm font-bold rounded-full">
                    <x-heroicon-o-bolt class="h-4 w-4 text-[#4CAF50] shrink-0" />
                    Matchmaking Cerdas
                </span>
                <h2 class="text-3xl md:text-4xl font-extrabold text-[#1B5E20] font-heading">
                    Pertandingan yang
                    <x-doodle-circle color="#4CAF50" delay="200">Seimbang</x-doodle-circle>
                </h2>
                <p class="text-[#2E7D32]/65 text-lg leading-relaxed">
                    Tidak seru kalau lawannya
                    <x-doodle-strikethrough color="#ef4444" delay="500">terlalu kuat</x-doodle-strikethrough> atau
                    <x-doodle-strikethrough color="#ef4444" delay="700">terlalu lemah</x-doodle-strikethrough>.
                    MATCHGO memastikan tim kamu
                    bertanding dengan lawan yang
                    <x-doodle-highlight color="#81C784" delay="1000"><strong class="text-[#2E7D32]">sepadan</strong></x-doodle-highlight>
                    — berdasarkan rating, lokasi, dan ketersediaan waktu.
                </p>
                <ul class="space-y-3">
                    <li class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl bg-[#4CAF50]/10 flex items-center justify-center text-[#2E7D32]" aria-hidden="true">
                            <x-heroicon-o-check class="w-5 h-5" />
                        </span>
                        <span class="text-[#1B5E20] font-medium">Matching berdasarkan level: Casual, Semi-Pro, Competitive</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl bg-[#4CAF50]/10 flex items-center justify-center text-[#2E7D32]" aria-hidden="true">
                            <x-heroicon-o-check class="w-5 h-5" />
                        </span>
                        <span class="text-[#1B5E20] font-medium">Proximity matching — lawan dekat dengan lokasi kamu</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl bg-[#4CAF50]/10 flex items-center justify-center text-[#2E7D32]" aria-hidden="true">
                            <x-heroicon-o-check class="w-5 h-5" />
                        </span>
                        <span class="text-[#1B5E20] font-medium">Schedule sync — jadwal cocok, langsung main!</span>
                    </li>
                </ul>
            </div>

            {{-- Visual: Matchmaking Illustration --}}
            <div class="relative flex items-center justify-center">
                <div class="relative w-full max-w-md aspect-square rounded-3xl bg-gradient-to-br from-[#2E7D32] to-[#66BB6A] shadow-2xl overflow-hidden rotate-1 hover:rotate-0 transition-transform duration-500">
                    <div class="absolute inset-6 rounded-2xl bg-white/10 backdrop-blur-sm border border-white/20 p-6 flex flex-col items-center justify-center gap-6">
                        {{-- Team A --}}
                        <div class="flex items-center gap-3 bg-white/20 rounded-2xl px-5 py-3 w-full">
                            <div class="w-12 h-12 rounded-xl bg-white/30 flex items-center justify-center text-white" aria-hidden="true">
                                <x-heroicon-o-user-group class="w-7 h-7" />
                            </div>
                            <div>
                                <p class="text-white font-bold text-sm">Manchester United</p>
                                <p class="text-white/60 text-xs">Profesional • Manchester</p>
                            </div>
                        </div>

                        {{-- VS --}}
                        <div class="w-14 h-14 rounded-full bg-white shadow-lg flex items-center justify-center">
                            <span class="text-[#2E7D32] font-black text-lg font-heading">VS</span>
                        </div>

                        {{-- Team B --}}
                        <div class="flex items-center gap-3 bg-white/20 rounded-2xl px-5 py-3 w-full">
                            <div class="w-12 h-12 rounded-xl bg-white/30 flex items-center justify-center text-white" aria-hidden="true">
                                <x-heroicon-o-user-group class="w-7 h-7" />
                            </div>
                            <div>
                                <p class="text-white font-bold text-sm">Real Madrid</p>
                                <p class="text-white/60 text-xs">Profesional • Madrid</p>
                            </div>
                        </div>

                        {{-- Match Info --}}
                        <div class="bg-white/15 rounded-xl px-4 py-2 text-center w-full">
                            <p class="text-white/80 text-xs font-medium inline-flex flex-wrap items-center justify-center gap-x-2 gap-y-1">
                                <span class="inline-flex items-center gap-1"><x-heroicon-o-map-pin class="h-3.5 w-3.5 shrink-0 opacity-90" />GOR Cipinang</span>
                                <span class="text-white/40" aria-hidden="true">·</span>
                                <span class="inline-flex items-center gap-1"><x-heroicon-o-calendar-days class="h-3.5 w-3.5 shrink-0 opacity-90" />Sabtu, 19:00</span>
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Floating badge --}}
                <div class="absolute -top-3 -right-3 bg-white rounded-2xl shadow-lg px-4 py-2 animate-float -rotate-3">
                    <p class="text-sm font-bold text-[#2E7D32]"><x-doodle-bracket color="#4CAF50" delay="1200" variant="stars">98% Match!</x-doodle-bracket></p>
                </div>
            </div>
        </div>

        {{-- Row 2: Visual Left, Text Right --}}
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">

            {{-- Visual: Cost Split --}}
            <div class="relative flex items-center justify-center order-2 lg:order-1">
                <div class="relative w-full max-w-md bg-white rounded-3xl shadow-2xl p-8 -rotate-1 hover:rotate-0 transition-transform duration-500 border border-[#81C784]/20">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#2E7D32] to-[#4CAF50] flex items-center justify-center text-white">
                            <x-heroicon-o-banknotes class="h-5 w-5" />
                        </div>
                        <div>
                            <p class="text-[#1B5E20] font-bold text-sm font-heading">Smart Cost Split</p>
                            <p class="text-[#2E7D32]/50 text-xs">Rincian biaya otomatis</p>
                        </div>
                    </div>

                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between items-center bg-[#F1F8E9] rounded-xl px-4 py-3">
                            <span class="text-sm text-[#2E7D32]">Sewa Lapangan (2 jam)</span>
                            <span class="text-sm font-bold text-[#1B5E20]">Rp 400.000</span>
                        </div>
                        <div class="flex justify-between items-center bg-[#F1F8E9] rounded-xl px-4 py-3">
                            <span class="text-sm text-[#2E7D32]">Per Tim (2 tim)</span>
                            <span class="text-sm font-bold text-[#1B5E20]">Rp 200.000</span>
                        </div>
                        <div class="flex justify-between items-center bg-gradient-to-r from-[#2E7D32] to-[#4CAF50] rounded-xl px-4 py-3">
                            <span class="text-sm text-white font-medium">Per Pemain (10 orang)</span>
                            <span class="text-sm font-bold text-white"><x-doodle-box color="#ffffff" opacity="0.35" delay="800">Rp 40.000</x-doodle-box></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-center gap-2 text-sm text-[#2E7D32]/50">
                        <x-heroicon-o-banknotes class="h-4 w-4 shrink-0 text-[#4CAF50]/70" />
                        <span>Transparan, tidak ada biaya tersembunyi</span>
                    </div>
                </div>

                {{-- Decorative --}}
                <div class="absolute -bottom-3 -left-3 bg-[#4CAF50] text-white rounded-2xl shadow-lg px-4 py-2 animate-float-slow rotate-2">
                    <p class="text-sm font-bold inline-flex items-center gap-2">
                        <x-heroicon-o-check-badge class="h-4 w-4 shrink-0 opacity-90" />
                        Fair Split!
                    </p>
                </div>
            </div>

            {{-- Text --}}
            <div class="space-y-6 order-1 lg:order-2">
                <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-[#81C784]/15 text-[#2E7D32] text-sm font-bold rounded-full">
                    <x-heroicon-o-banknotes class="h-4 w-4 text-[#4CAF50] shrink-0" />
                    Biaya Transparan
                </span>
                <h2 class="text-3xl md:text-4xl font-extrabold text-[#1B5E20] font-heading">
                    Bagi Biaya
                    <span class="relative inline-block">
                        <x-doodle-highlight color="#81C784" delay="200" opacity="0.2">Tanpa Ribet</x-doodle-highlight>
                        <span class="absolute -bottom-2 left-0 w-full">
                            <x-doodle-underline :width="200" color="#4CAF50" class="w-full" delay="400" variant="thick" />
                        </span>
                    </span>
                </h2>
                <p class="text-[#2E7D32]/65 text-lg leading-relaxed">
                    Smart Cost Split menghitung biaya sewa lapangan secara otomatis. Dibagi rata per tim,
                    bisa juga per pemain. Semua bisa lihat rinciannya, jadi
                   tidak ada lagi drama
                    soal uang!
                </p>
                <ul class="space-y-3">
                    <li class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl bg-[#4CAF50]/10 flex items-center justify-center text-[#2E7D32]" aria-hidden="true">
                            <x-heroicon-o-building-storefront class="w-5 h-5" />
                        </span>
                        <span class="text-[#1B5E20] font-medium">Hitung otomatis berdasarkan durasi & harga lapangan</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl bg-[#4CAF50]/10 flex items-center justify-center text-[#2E7D32]" aria-hidden="true">
                            <x-heroicon-o-chart-bar class="w-5 h-5" />
                        </span>
                        <span class="text-[#1B5E20] font-medium">Rincian biaya per tim dan per pemain</span>
                    </li>
                    <li class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl bg-[#4CAF50]/10 flex items-center justify-center text-[#2E7D32]" aria-hidden="true">
                            <x-heroicon-o-eye class="w-5 h-5" />
                        </span>
                        <span class="text-[#1B5E20] font-medium">100% transparan, semua pemain bisa lihat</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</section>