<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'MATCHGO — Find Your Futsal Rival' }}</title>
    <meta name="description" content="Platform matchmaking futsal — temukan lawan bermain, tentukan lapangan, dan bagi biaya secara transparan.">

    <link rel="icon" type="image/svg+xml" href="{{ asset('matchgo-logo.svg') }}">
    <link rel="shortcut icon" href="{{ asset('matchgo-logo.svg') }}">
    <meta name="theme-color" content="#6F945D">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300;400;500;600;700&family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    {{-- Tailwind CSS CDN (for prototyping — replace with compiled CSS in production) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'heading': ['Fredoka', 'sans-serif'],
                        'body': ['Nunito', 'sans-serif'],
                    },
                    container: {
                        center: true,
                        padding: {
                            DEFAULT: '1rem',
                            sm: '2rem',
                            lg: '4rem',
                            xl: '5rem',
                            '2xl': '6rem',
                        },
                    },
                }
            }
        }
    </script>

    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Nunito', sans-serif; }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
        }
        @keyframes float-slow {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-8px) rotate(3deg); }
        }
        @keyframes pulse-soft {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }
        @keyframes dash {
            to { stroke-dashoffset: 0; }
        }

        /* SVG Doodle Draw Animation */
        .doodle-draw {
            stroke-dasharray: var(--dash-length, 500);
            stroke-dashoffset: var(--dash-length, 500);
            animation: dash var(--duration, 1s) cubic-bezier(0.65, 0, 0.35, 1) forwards;
            animation-delay: var(--delay, 0ms);
        }

        @keyframes bounce-gentle {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }

        .animate-float { animation: float 4s ease-in-out infinite; }
        .animate-float-slow { animation: float-slow 6s ease-in-out infinite; }
        .animate-pulse-soft { animation: pulse-soft 3s ease-in-out infinite; }
        .animate-bounce-gentle { animation: bounce-gentle 2s ease-in-out infinite; }

        .field-pattern {
            background-image:
                linear-gradient(rgba(76, 175, 80, 0.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(76, 175, 80, 0.05) 1px, transparent 1px);
            background-size: 40px 40px;
        }
        .grass-gradient {
            background: linear-gradient(135deg, #2E7D32 0%, #4CAF50 50%, #66BB6A 100%);
        }
        .hero-glow {
            background: radial-gradient(ellipse at 50% 0%, rgba(129, 199, 132, 0.3) 0%, transparent 60%);
        }
    </style>

    @stack('styles')
</head>
<body class="h-full antialiased">

{{-- Navbar — hidden on pages that include their own (e.g. landing page) --}}
@unless(View::hasSection('hide-layout-nav'))
<nav class="fixed top-0 inset-x-0 z-50 bg-white/90 backdrop-blur-lg border-b border-green-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

            {{-- Brand --}}
            <a href="{{ auth()->check() ? '/app' : '/' }}" class="flex items-center gap-2 group">
                <span class="text-xl font-bold text-[#1B5E20] font-heading">
                    MATCH<span class="text-[#4CAF50]">GO.</span>
                </span>
            </a>

            {{-- Desktop Nav --}}
            <div class="hidden md:flex items-center gap-1">
                @auth
                <a href="{{ route('match.index') }}"
                   class="px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200
                   {{ request()->routeIs('match.*') ? 'bg-green-50 text-[#2E7D32] font-semibold' : 'text-[#2E7D32]/70 hover:bg-green-50 hover:text-[#2E7D32]' }}">
                    Pertandingan
                </a>
                <a href="{{ route('automatch.index') }}"
                   class="px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200
                   {{ request()->routeIs('automatch.*') ? 'bg-green-50 text-[#2E7D32] font-semibold' : 'text-[#2E7D32]/70 hover:bg-green-50 hover:text-[#2E7D32]' }}">
                    Automatching
                </a>
                <a href="{{ route('discover.index') }}"
                   class="px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200
                   {{ request()->routeIs('discover.*') ? 'bg-green-50 text-[#2E7D32] font-semibold' : 'text-[#2E7D32]/70 hover:bg-green-50 hover:text-[#2E7D32]' }}">
                    Cari Lawan
                </a>
                <a href="{{ route('venue.index') }}"
                   class="px-3 py-2 rounded-lg text-sm font-medium transition-colors duration-200
                   {{ request()->routeIs('venue.*') ? 'bg-green-50 text-[#2E7D32] font-semibold' : 'text-[#2E7D32]/70 hover:bg-green-50 hover:text-[#2E7D32]' }}">
                    Lapangan
                </a>
                @endauth
            </div>

            {{-- Right side --}}
            <div class="flex items-center gap-3">
                @auth
                <a href="{{ route('match.create') }}"
                   class="hidden sm:inline-flex items-center gap-1.5 px-4 py-2 bg-gradient-to-r from-[#2E7D32] to-[#4CAF50] hover:from-[#1B5E20] hover:to-[#2E7D32] text-white text-sm font-semibold rounded-xl shadow-sm transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Buat Pertandingan
                </a>

                {{-- User dropdown --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="flex items-center gap-2 px-3 py-2 rounded-xl hover:bg-green-50 transition-colors duration-200">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#2E7D32] to-[#4CAF50] flex items-center justify-center text-xs font-bold text-white shadow-sm">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <span class="hidden sm:block text-sm font-medium text-[#1B5E20]">{{ auth()->user()->name }}</span>
                        <svg class="w-4 h-4 text-[#2E7D32]/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="open" @click.away="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-green-100 py-1 z-50"
                         style="display: none;">
                        <div class="px-4 py-3 border-b border-green-50">
                            <p class="text-sm font-semibold text-[#1B5E20]">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-[#2E7D32]/60 truncate">{{ auth()->user()->email }}</p>
                        </div>
                        <a href="/app"
                           class="flex items-center gap-2 px-4 py-2.5 text-sm text-[#2E7D32] hover:bg-green-50 transition-colors duration-150">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Kelola Tim
                        </a>
                        <div class="border-t border-green-50 mt-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 transition-colors duration-150">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Keluar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @else
                <a href="{{ route('login') }}"
                   class="px-4 py-2 text-sm font-semibold text-[#2E7D32] hover:bg-green-50 rounded-xl transition-colors duration-200">
                    Masuk
                </a>
                <a href="{{ route('register') }}"
                   class="px-4 py-2 bg-gradient-to-r from-[#2E7D32] to-[#4CAF50] text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all duration-200">
                    Daftar Gratis
                </a>
                @endauth

                {{-- Mobile menu toggle --}}
                <button class="md:hidden p-2 rounded-xl hover:bg-green-50 transition-colors" x-data @click="$dispatch('toggle-mobile-menu')">
                    <svg class="w-5 h-5 text-[#2E7D32]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div x-data="{ open: false }" @toggle-mobile-menu.window="open = !open">
        <div x-show="open" class="md:hidden border-t border-green-100 bg-white px-4 py-3 space-y-1">
            @auth
            <a href="{{ route('match.index') }}" class="block px-3 py-2 rounded-lg text-sm text-[#2E7D32] hover:bg-green-50 transition-colors">Pertandingan</a>
            <a href="{{ route('automatch.index') }}" class="block px-3 py-2 rounded-lg text-sm text-[#2E7D32] hover:bg-green-50 transition-colors">Automatching</a>
            <a href="{{ route('discover.index') }}" class="block px-3 py-2 rounded-lg text-sm text-[#2E7D32] hover:bg-green-50 transition-colors">Cari Lawan</a>
            <a href="{{ route('venue.index') }}" class="block px-3 py-2 rounded-lg text-sm text-[#2E7D32] hover:bg-green-50 transition-colors">Lapangan</a>
            <a href="{{ route('match.create') }}" class="block px-3 py-2 rounded-lg text-sm text-[#4CAF50] font-semibold hover:bg-green-50 transition-colors">+ Buat Pertandingan</a>
            @else
            <a href="{{ route('login') }}" class="block px-3 py-2 rounded-lg text-sm text-[#2E7D32] hover:bg-green-50 transition-colors">Masuk</a>
            <a href="{{ route('register') }}" class="block px-3 py-2 rounded-lg text-sm text-[#4CAF50] font-semibold hover:bg-green-50 transition-colors">Daftar Gratis</a>
            @endauth
        </div>
    </div>
</nav>
@endunless

{{-- Flash messages --}}
@if(session('success'))
    <div class="fixed top-20 right-4 z-50 max-w-sm"
         x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 translate-x-4">
        <div class="flex items-center gap-3 p-4 bg-green-600 text-white rounded-xl shadow-xl">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <p class="text-sm font-medium flex-1">{{ session('success') }}</p>
            <button @click="show = false" class="ml-2 text-white/70 hover:text-white transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="fixed top-20 right-4 z-50 max-w-sm"
         x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100 translate-x-0"
         x-transition:leave-end="opacity-0 translate-x-4">
        <div class="flex items-center gap-3 p-4 bg-red-500 text-white rounded-xl shadow-xl">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <p class="text-sm font-medium flex-1">{{ session('error') }}</p>
            <button @click="show = false" class="ml-2 text-white/70 hover:text-white transition shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>
@endif

{{-- Main content --}}
<main class="pt-16 min-h-screen">
    @yield('content')
</main>

{{-- Footer --}}
<footer class="bg-white border-t border-green-100 mt-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <span class="text-lg font-bold text-[#1B5E20]">MATCH<span class="text-[#4CAF50]">GO.</span></span>
            <p class="text-[#2E7D32]/50 text-sm">© {{ date('Y') }} MatchGo. Platform Matchmaking Futsal.</p>
        </div>
    </div>
</footer>

{{-- Alpine.js is bundled with Livewire 4 — no separate CDN needed --}}
@livewireScripts
@stack('scripts')

<<<<<<< HEAD
=======
    <x-toaster />

    @stack('scripts')
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
>>>>>>> feature/milestone-2
</body>
</html>
