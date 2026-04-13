{{-- Hero Section --}}
<section id="home" class="relative min-h-screen flex items-center pt-20 overflow-hidden field-pattern">

    {{-- Decorative floating elements --}}
    <div class="absolute top-32 left-10 w-16 h-16 rounded-full bg-[#81C784]/20 animate-float"></div>
    <div class="absolute top-48 right-16 w-10 h-10 rounded-full bg-[#4CAF50]/15 animate-float-slow"></div>
    <div class="absolute bottom-32 left-1/4 w-8 h-8 rounded-full bg-[#2E7D32]/10 animate-float"></div>
    <div class="absolute top-40 right-1/3 text-[#81C784]/30 animate-pulse-soft text-2xl">✦</div>
    <div class="absolute bottom-40 right-10 text-[#4CAF50]/20 animate-pulse-soft text-xl">✦</div>
    <div class="absolute top-60 left-1/3 text-[#81C784]/20 animate-float-slow text-3xl font-light">+</div>

    {{-- Glow background --}}
    <div class="absolute inset-0 hero-glow pointer-events-none"></div>

    <div class="container mx-auto px-6 lg:px-10">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">

            {{-- Left: Text Content --}}
            <div class="space-y-8">
                {{-- Badge --}}
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-white rounded-full shadow-md border border-[#81C784]/20">
                    <span class="w-2 h-2 rounded-full bg-[#4CAF50] animate-pulse"></span>
                    <span class="text-sm font-semibold text-[#2E7D32]">Platform Futsal #1 Indonesia</span>
                </div>

                {{-- Heading with Doodle Underline --}}
                <div class="space-y-2">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-[#1B5E20] leading-tight font-heading">
                        Temukan <x-doodle-circle color="#4CAF50" delay="400" variant="messy">Lawan</x-doodle-circle>,<br>
                        <span class="relative inline-block">
                            <span class="text-[#2E7D32]">Main Futsal!</span>
                            <span class="absolute -bottom-2 left-0 w-full">
                                <x-doodle-underline :width="300" color="#81C784" class="w-full" delay="200" variant="thick" />
                            </span>
                        </span>
                    </h1>
                </div>

                {{-- Subheading --}}
                <p class="text-lg md:text-xl text-[#2E7D32]/70 max-w-lg leading-relaxed">
                    MATCHGO mencocokkan tim futsal berdasarkan
                    <x-doodle-highlight color="#81C784" delay="600"><strong class="text-[#2E7D32]">level permainan</strong></x-doodle-highlight>,
                    <x-doodle-highlight color="#81C784" delay="800"><strong class="text-[#2E7D32]">lokasi</strong></x-doodle-highlight>, dan
                    <x-doodle-highlight color="#81C784" delay="1000"><strong class="text-[#2E7D32]">jadwal</strong></x-doodle-highlight>
                    — otomatis, adil, dan transparan! 🎯
                </p>

                {{-- CTA Buttons --}}
                <div class="flex flex-wrap gap-4">
                    <x-button href="#" variant="primary" size="lg">
                        <span>Cari Lawan Sekarang</span>
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </x-button>
                    <x-button href="#features" variant="secondary" size="lg">
                        Pelajari Fitur 📖
                    </x-button>
                </div>

                {{-- Stats --}}
                <div class="flex flex-wrap gap-8 pt-4">
                    <div class="text-center">
                        <p class="text-2xl font-extrabold text-[#2E7D32] font-heading"><x-doodle-box color="#4CAF50" delay="1200">500+</x-doodle-box></p>
                        <p class="text-sm text-[#2E7D32]/60 font-medium">Tim Terdaftar</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-extrabold text-[#2E7D32] font-heading"><x-doodle-box color="#4CAF50" delay="1400">1200+</x-doodle-box></p>
                        <p class="text-sm text-[#2E7D32]/60 font-medium">Match Selesai</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-extrabold text-[#2E7D32] font-heading"><x-doodle-box color="#4CAF50" delay="1600">50+</x-doodle-box></p>
                        <p class="text-sm text-[#2E7D32]/60 font-medium">Kota</p>
                    </div>
                </div>
            </div>

            {{-- Right: Illustration --}}
            <div class="relative flex items-center justify-center">
                <div class="absolute w-80 h-80 md:w-96 md:h-96 rounded-full bg-gradient-to-br from-[#81C784]/20 to-[#4CAF50]/10 blur-2xl"></div>
                <div class="relative w-72 h-72 md:w-96 md:h-96 rounded-3xl bg-gradient-to-br from-[#2E7D32] to-[#4CAF50] shadow-2xl flex items-center justify-center overflow-hidden animate-float-slow">
                    {{-- Field lines --}}
                    <div class="absolute inset-4 rounded-2xl border-2 border-white/20">
                        <div class="absolute top-1/2 left-0 right-0 h-px bg-white/20"></div>
                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-20 h-20 rounded-full border-2 border-white/20"></div>
                        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-3 h-3 rounded-full bg-white/40"></div>
                    </div>

                    {{-- Ball icon --}}
                    <div class="relative z-10 animate-bounce-gentle">
                        <svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="40" cy="40" r="35" fill="white" fill-opacity="0.95" />
                            <path d="M40 5C40 5 50 20 40 25C30 30 25 15 25 15" stroke="#2E7D32" stroke-width="2" stroke-linecap="round" />
                            <path d="M40 75C40 75 50 60 40 55C30 50 25 65 25 65" stroke="#2E7D32" stroke-width="2" stroke-linecap="round" />
                            <path d="M5 40C5 40 20 50 25 40C30 30 15 25 15 25" stroke="#2E7D32" stroke-width="2" stroke-linecap="round" />
                            <path d="M75 40C75 40 60 50 55 40C50 30 65 25 65 25" stroke="#2E7D32" stroke-width="2" stroke-linecap="round" />
                            <polygon points="40,22 50,32 46,44 34,44 30,32" fill="#2E7D32" fill-opacity="0.15" stroke="#2E7D32" stroke-width="1.5" />
                        </svg>
                    </div>

                    {{-- VS badge --}}
                    <div class="absolute bottom-8 right-8 w-14 h-14 rounded-full bg-white shadow-lg flex items-center justify-center">
                        <span class="text-[#2E7D32] font-black text-sm font-heading">VS</span>
                    </div>

                    {{-- Sparkles --}}
                    <div class="absolute top-6 right-6 text-white/50 text-lg animate-pulse-soft">✦</div>
                    <div class="absolute bottom-6 left-6 text-white/30 text-sm animate-pulse-soft">⚡</div>
                </div>

                {{-- Floating mini cards --}}
                <div class="absolute -top-4 -left-4 md:left-0 bg-white rounded-2xl shadow-lg px-4 py-3 flex items-center gap-2 animate-float rotate-2">
                    <span class="text-2xl">🏆</span>
                    <div>
                        <p class="text-xs font-bold text-[#1B5E20]">Match Found!</p>
                        <p class="text-[10px] text-[#2E7D32]/60">vs Thunder FC</p>
                    </div>
                </div>

                <div class="absolute -bottom-4 -right-4 md:right-0 bg-white rounded-2xl shadow-lg px-4 py-3 flex items-center gap-2 animate-float-slow -rotate-2">
                    <span class="text-2xl">📍</span>
                    <div>
                        <p class="text-xs font-bold text-[#1B5E20]">Auto Venue</p>
                        <p class="text-[10px] text-[#2E7D32]/60">GOR Sudirman</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>