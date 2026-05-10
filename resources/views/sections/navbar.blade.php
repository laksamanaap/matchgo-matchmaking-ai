{{-- Navbar Section --}}
<nav class="fixed top-0 left-0 right-0 z-50 bg-white/80 backdrop-blur-lg border-b border-[#81C784]/20 shadow-sm">
    <div class="container mx-auto px-6 lg:px-10">
        <div class="flex items-center justify-between h-16 md:h-20">

            {{-- Logo --}}
            <a href="{{ url('/') }}" class="flex items-center gap-2 group">
                <!-- <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-[#2E7D32] to-[#4CAF50] flex items-center justify-center shadow-md group-hover:shadow-lg transition-shadow duration-300 group-hover:scale-105 transform">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="12" cy="12" r="9" stroke="white" stroke-width="2" />
                        <circle cx="12" cy="12" r="3" stroke="white" stroke-width="1.5" />
                        <path d="M12 3V7M12 17V21M3 12H7M17 12H21" stroke="white" stroke-width="1.5" stroke-linecap="round" />
                    </svg>
                </div> -->
                <span class="text-xl font-bold text-[#1B5E20] font-heading">
                    MATCH<span class="text-[#4CAF50]">GO.</span>
                </span>
            </a>

            {{-- Desktop Menu --}}
            <div class="hidden md:flex items-center gap-8">
                <a href="#home" class="text-[#1B5E20] font-semibold hover:text-[#4CAF50] transition-colors duration-300 relative group">
                    Beranda
                    <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-[#4CAF50] rounded-full group-hover:w-full transition-all duration-300"></span>
                </a>
                <a href="#features" class="text-[#2E7D32]/70 font-medium hover:text-[#4CAF50] transition-colors duration-300 relative group">
                    Fitur
                    <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-[#4CAF50] rounded-full group-hover:w-full transition-all duration-300"></span>
                </a>
                <a href="#how-it-works" class="text-[#2E7D32]/70 font-medium hover:text-[#4CAF50] transition-colors duration-300 relative group">
                    Cara Kerja
                    <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-[#4CAF50] rounded-full group-hover:w-full transition-all duration-300"></span>
                </a>
                <a href="#about" class="text-[#2E7D32]/70 font-medium hover:text-[#4CAF50] transition-colors duration-300 relative group">
                    Tentang
                    <span class="absolute -bottom-1 left-0 w-0 h-0.5 bg-[#4CAF50] rounded-full group-hover:w-full transition-all duration-300"></span>
                </a>
            </div>

            {{-- CTA Buttons --}}
            <div class="hidden md:flex items-center gap-3">
                @auth
                <a href="/app" class="px-5 py-2.5 text-[#2E7D32] font-semibold hover:bg-[#81C784]/10 rounded-xl transition-all duration-300">
                    Dashboard
                </a>
                @else
                <a href="{{ route('login') }}" class="px-5 py-2.5 text-[#2E7D32] font-semibold hover:bg-[#81C784]/10 rounded-xl transition-all duration-300">
                    Masuk
                </a>
                <x-button href="{{ route('register') }}" variant="primary" size="sm">
                    Daftar Tim
                </x-button>
                @endauth
            </div>

            {{-- Mobile Hamburger --}}
            <button
                class="md:hidden p-2 rounded-xl hover:bg-[#81C784]/10 transition-colors"
                onclick="document.getElementById('mobile-menu').classList.toggle('hidden')"
            >
                <x-heroicon-o-bars-3 class="h-6 w-6 text-[#2E7D32]" />
            </button>
        </div>

        {{-- Mobile Menu --}}
        <div id="mobile-menu" class="md:hidden hidden pb-6 border-t border-[#81C784]/20 pt-4 space-y-3">
            <a href="#home" class="block px-4 py-2 text-[#1B5E20] font-semibold rounded-xl hover:bg-[#81C784]/10 transition-colors">Beranda</a>
            <a href="#features" class="block px-4 py-2 text-[#2E7D32]/70 font-medium rounded-xl hover:bg-[#81C784]/10 transition-colors">Fitur</a>
            <a href="#how-it-works" class="block px-4 py-2 text-[#2E7D32]/70 font-medium rounded-xl hover:bg-[#81C784]/10 transition-colors">Cara Kerja</a>
            <a href="#about" class="block px-4 py-2 text-[#2E7D32]/70 font-medium rounded-xl hover:bg-[#81C784]/10 transition-colors">Tentang</a>
            <div class="pt-3 flex flex-col gap-2 px-4">
                @auth
                <a href="/app" class="py-2.5 text-center text-[#2E7D32] font-semibold rounded-xl border border-[#81C784]/30 hover:bg-[#81C784]/10 transition-colors">Dashboard</a>
                @else
                <a href="{{ route('login') }}" class="py-2.5 text-center text-[#2E7D32] font-semibold rounded-xl border border-[#81C784]/30 hover:bg-[#81C784]/10 transition-colors">Masuk</a>
                <a href="{{ route('register') }}" class="flex w-full py-2.5 items-center justify-center gap-2 bg-gradient-to-r from-[#2E7D32] to-[#4CAF50] text-white font-semibold rounded-xl shadow-md">
                    <span>Daftar Tim</span>
                    <x-heroicon-o-user-plus class="h-5 w-5 shrink-0 opacity-90" />
                </a>
                @endauth
            </div>
        </div>
    </div>
</nav>