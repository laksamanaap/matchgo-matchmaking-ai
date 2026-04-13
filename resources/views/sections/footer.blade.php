{{-- Footer Section --}}
<footer class="bg-[#1B5E20] text-white py-12 md:py-16">
    <div class="container mx-auto px-6 lg:px-10">
        <div class="grid md:grid-cols-4 gap-10 mb-10">

            {{-- Brand --}}
            <div class="md:col-span-1 space-y-4">
                <div class="flex items-center gap-2">
                    <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                            <circle cx="12" cy="12" r="9" stroke="white" stroke-width="2" />
                            <circle cx="12" cy="12" r="3" stroke="white" stroke-width="1.5" />
                            <path d="M12 3V7M12 17V21M3 12H7M17 12H21" stroke="white" stroke-width="1.5" stroke-linecap="round" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold font-heading">
                        MATCH<span class="text-[#81C784]">GO</span>
                    </span>
                </div>
                <p class="text-white/60 text-sm leading-relaxed">
                    Platform matchmaking futsal #1 di Indonesia. Temukan lawan, main futsal, jadi juara! ⚽
                </p>
            </div>

            {{-- Platform Links --}}
            <div class="space-y-4">
                <h4 class="font-bold text-[#81C784] font-heading">Platform</h4>
                <ul class="space-y-2">
                    <li><a href="#features" class="text-white/60 hover:text-white text-sm transition-colors">Fitur</a></li>
                    <li><a href="#how-it-works" class="text-white/60 hover:text-white text-sm transition-colors">Cara Kerja</a></li>
                    <li><a href="#" class="text-white/60 hover:text-white text-sm transition-colors">Harga</a></li>
                    <li><a href="#" class="text-white/60 hover:text-white text-sm transition-colors">FAQ</a></li>
                </ul>
            </div>

            {{-- Company Links --}}
            <div class="space-y-4">
                <h4 class="font-bold text-[#81C784] font-heading">Perusahaan</h4>
                <ul class="space-y-2">
                    <li><a href="#about" class="text-white/60 hover:text-white text-sm transition-colors">Tentang Kami</a></li>
                    <li><a href="#" class="text-white/60 hover:text-white text-sm transition-colors">Blog</a></li>
                    <li><a href="#" class="text-white/60 hover:text-white text-sm transition-colors">Karir</a></li>
                    <li><a href="#" class="text-white/60 hover:text-white text-sm transition-colors">Kontak</a></li>
                </ul>
            </div>

            {{-- Legal Links --}}
            <div class="space-y-4">
                <h4 class="font-bold text-[#81C784] font-heading">Legal</h4>
                <ul class="space-y-2">
                    <li><a href="#" class="text-white/60 hover:text-white text-sm transition-colors">Kebijakan Privasi</a></li>
                    <li><a href="#" class="text-white/60 hover:text-white text-sm transition-colors">Syarat & Ketentuan</a></li>
                    <li><a href="#" class="text-white/60 hover:text-white text-sm transition-colors">Cookies</a></li>
                </ul>
            </div>
        </div>

        {{-- Divider & Bottom --}}
        <div class="border-t border-white/10 pt-8 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-white/40 text-sm">
                © {{ date('Y') }} MATCHGO. All rights reserved. Made with 💚 for futsal lovers.
            </p>
            <div class="flex items-center gap-4">
                {{-- Instagram --}}
                <a href="#" class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center hover:bg-white/20 transition-colors">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="white"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                </a>
                {{-- Twitter/X --}}
                <a href="#" class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center hover:bg-white/20 transition-colors">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="white"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                </a>
                {{-- YouTube --}}
                <a href="#" class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center hover:bg-white/20 transition-colors">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="white"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                </a>
            </div>
        </div>
    </div>
</footer>