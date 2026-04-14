{{-- Call-to-Action Section --}}
<section class="py-20 md:py-28 relative overflow-hidden">
    <div class="container mx-auto px-6 lg:px-10">
        <div class="relative rounded-3xl grass-gradient p-10 md:p-16 text-center overflow-hidden shadow-2xl">

            {{-- Field decoration --}}
            <div class="absolute inset-6 rounded-2xl border border-white/10 pointer-events-none"></div>
            <div class="absolute top-1/2 left-6 right-6 h-px bg-white/10 pointer-events-none"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-24 h-24 rounded-full border border-white/10 pointer-events-none"></div>

            {{-- Floating elements --}}
            <div class="absolute top-8 left-8 text-white/20 animate-float pointer-events-none" aria-hidden="true">
                <x-heroicon-o-sparkles class="h-10 w-10" />
            </div>
            <div class="absolute bottom-8 right-8 text-white/15 animate-float-slow pointer-events-none" aria-hidden="true">
                <x-heroicon-o-trophy class="h-9 w-9" />
            </div>
            <div class="absolute top-12 right-16 text-white/15 animate-pulse-soft pointer-events-none" aria-hidden="true">
                <x-heroicon-o-sparkles class="h-5 w-5" />
            </div>
            <div class="absolute bottom-12 left-16 text-white/15 animate-pulse-soft pointer-events-none" aria-hidden="true">
                <x-heroicon-o-sparkles class="h-5 w-5" />
            </div>

            <div class="relative z-10 max-w-2xl mx-auto">
                <h2 class="text-3xl md:text-4xl lg:text-5xl font-extrabold text-white mb-4 font-heading inline-flex flex-wrap items-center justify-center gap-3">
                    <x-doodle-circle color="#ffffff" delay="200" opacity="0.5" variant="messy">Siap Tanding?</x-doodle-circle>
                </h2>
                <p class="text-white/80 text-lg md:text-xl mb-8 leading-relaxed">
                    Daftarkan tim kamu sekarang dan temukan lawan bermain futsal yang sepadan.
                    <x-doodle-highlight color="#ffffff" delay="600" opacity="0.15">Gratis, cepat, dan seru!</x-doodle-highlight>
                </p>
                <div class="flex flex-wrap justify-center gap-4">
                    <x-button href="#" variant="white" size="xl">
                        <span>Daftar Tim Gratis</span>
                        <x-heroicon-o-arrow-right class="w-5 h-5 group-hover:translate-x-1 transition-transform" />
                    </x-button>
                    <x-button href="#" variant="outline" size="xl" class="group">
                        <span>Lihat Demo</span>
                        <x-heroicon-o-eye class="w-5 h-5 opacity-90 group-hover:opacity-100 transition-opacity" />
                    </x-button>
                </div>
            </div>
        </div>
    </div>
</section>