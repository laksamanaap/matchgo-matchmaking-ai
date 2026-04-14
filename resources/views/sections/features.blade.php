{{-- Features Section --}}
<section id="features" class="py-20 md:py-28 relative overflow-hidden">

    {{-- Decorative elements --}}
    <div class="absolute top-10 right-10 text-[#81C784]/20 animate-float-slow pointer-events-none" aria-hidden="true">
        <x-heroicon-o-sparkles class="h-16 w-16" />
    </div>
    <div class="absolute bottom-10 left-10 text-[#4CAF50]/15 animate-float pointer-events-none" aria-hidden="true">
        <x-heroicon-o-flag class="h-12 w-12" />
    </div>

    <div class="container mx-auto px-6 lg:px-10">

        {{-- Section Header --}}
        <div class="text-center max-w-2xl mx-auto mb-16">
            <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-[#81C784]/15 text-[#2E7D32] text-sm font-bold rounded-full mb-4">
                <x-heroicon-o-sparkles class="h-4 w-4 text-[#4CAF50]" />
                Fitur Unggulan
            </span>
            <h2 class="text-3xl md:text-4xl font-extrabold text-[#1B5E20] mb-3 font-heading">
                Semua yang Kamu
                <span class="relative inline-block">
                    <x-doodle-circle color="#2E7D32" delay="200" variant="tight">Butuhkan</x-doodle-circle>
                </span>
            </h2>
            <p class="text-[#2E7D32]/60 text-lg mt-4">
                MATCHGO punya semua fitur untuk bikin pengalaman futsal kamu jadi
                <x-doodle-highlight color="#81C784" delay="500">lebih seru</x-doodle-highlight> dan terorganisir.
            </p>
        </div>

        {{-- Feature Cards Grid --}}
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">

            {{-- Card 1: Smart Matchmaking --}}
            <div class="relative">
                {{-- Doodle circle highlight on this card --}}
                <svg class="absolute -top-3 -right-3 w-8 h-8 pointer-events-none z-10 overflow-visible" viewBox="0 0 40 40" fill="none">
                    <path d="M20 4L22 16L34 18L22 20L20 32L18 20L6 18L18 16Z" stroke="#4CAF50" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="0.5" class="doodle-draw" style="--dash-length: 120; --duration: 0.6s; --delay: 600ms;" />
                </svg>
                <x-card
                    title="Smart Matchmaking"
                    description="Sistem mencocokkan tim berdasarkan level permainan, lokasi, dan jadwal secara otomatis. Pertandingan jadi lebih seimbang dan kompetitif!"
                    gradient="from-[#2E7D32]/10 to-[#81C784]/10"
                    borderColor="border-[#2E7D32]/20"
                >
                    <x-slot:icon>
                        <x-heroicon-o-user-group class="h-8 w-8 text-[#2E7D32]" />
                    </x-slot:icon>
                </x-card>
            </div>

            {{-- Card 2: Auto Venue --}}
            <x-card
                title="Auto Venue"
                description="Lapangan ditentukan otomatis berdasarkan titik tengah lokasi kedua tim. Adil, dekat, dan nyaman untuk semua pihak!"
                gradient="from-[#4CAF50]/10 to-[#81C784]/10"
                borderColor="border-[#4CAF50]/20"
            >
                <x-slot:icon>
                    <x-heroicon-o-map-pin class="h-8 w-8 text-[#2E7D32]" />
                </x-slot:icon>
            </x-card>

            {{-- Card 3: Smart Cost Split --}}
            <x-card
                title="Smart Cost Split"
                description="Biaya sewa lapangan dihitung dan dibagi otomatis per tim dan per pemain. Transparan, tidak ada ribut soal biaya lagi!"
                gradient="from-[#81C784]/10 to-[#4CAF50]/10"
                borderColor="border-[#81C784]/30"
            >
                <x-slot:icon>
                    <x-heroicon-o-calculator class="h-8 w-8 text-[#2E7D32]" />
                </x-slot:icon>
            </x-card>

            {{-- Card 4: Profil & Statistik Tim --}}
            <div class="relative">
                <svg class="absolute -bottom-2 -left-2 w-7 h-7 pointer-events-none z-10 overflow-visible" viewBox="0 0 24 24" fill="none">
                    <path d="M12 2L13.5 10L22 12L13.5 14L12 22L10.5 14L2 12L10.5 10Z" stroke="#81C784" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" stroke-opacity="0.5" class="doodle-draw" style="--dash-length: 80; --duration: 0.5s; --delay: 1200ms;" />
                </svg>
                <x-card
                    title="Profil & Statistik Tim"
                    description="Kelola profil tim, pantau statistik pertandingan — jumlah match, kemenangan, gol, dan masih banyak lagi!"
                    gradient="from-[#2E7D32]/10 to-[#4CAF50]/10"
                    borderColor="border-[#2E7D32]/15"
                >
                    <x-slot:icon>
                        <x-heroicon-o-chart-bar class="h-8 w-8 text-[#2E7D32]" />
                    </x-slot:icon>
                </x-card>
            </div>

        </div>
    </div>
</section>