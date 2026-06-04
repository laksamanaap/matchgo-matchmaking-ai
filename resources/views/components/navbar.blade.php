{{-- Navbar Component for Authenticated Users --}}
@php
    $navItems = [
        ['label' => 'Tim', 'href' => url('/teams'), 'active' => request()->is('teams*')],
        ['label' => 'Daftar Pertandingan', 'href' => route('matches.index'), 'active' => request()->routeIs('matches.index') || request()->routeIs('matchmaking.index')],
        ['label' => 'Buat Pertandingan', 'href' => route('matches.create'), 'active' => request()->routeIs('matches.create')],
        ['label' => 'Cari Pertandingan', 'href' => route('matches.take'), 'active' => request()->routeIs('matches.take')],
        ['label' => 'AutoMatching', 'href' => route('matches.auto'), 'active' => request()->routeIs('matches.auto')],
        ['label' => 'History', 'href' => route('matches.history'), 'active' => request()->routeIs('matches.history')],
        ['label' => 'Pembayaran', 'href' => route('payments.index'), 'active' => request()->routeIs('payments.*')],
    ];
@endphp

<nav class="fixed left-0 right-0 top-0 z-50 border-b border-[#DDEED8] bg-white/90 shadow-sm backdrop-blur-xl">
    <div class="container mx-auto px-6 lg:px-10">
        <div class="flex h-16 items-center justify-between gap-4 md:h-20">
            <a href="{{ route('dashboard') }}" class="group inline-flex items-center">
                <span class="text-xl font-black tracking-tight text-[#0B5D1E] font-heading">
                    MATCH<span class="text-[#43A047]">GO.</span>
                </span>
            </a>

            <div class="hidden items-center gap-1 rounded-2xl bg-[#F8FCF4] p-1 ring-1 ring-[#DDEED8] md:flex">
                @foreach($navItems as $item)
                    <a href="{{ $item['href'] }}" class="rounded-xl px-3 py-2 text-xs font-bold transition xl:px-4 xl:text-sm {{ $item['active'] ? 'bg-white text-[#0B5D1E] shadow-sm ring-1 ring-[#DDEED8]' : 'text-[#2E7D32] hover:bg-white/70 hover:text-[#0B5D1E]' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>

            <div class="hidden items-center gap-3 md:flex">
                <a href="{{ route('profile.show') }}" class="inline-flex items-center gap-3 rounded-2xl bg-[#F8FCF4] px-3 py-2 text-[#0B5D1E] ring-1 ring-[#DDEED8] transition hover:bg-[#F1F8E9]">
                    <span class="h-8 w-8 overflow-hidden rounded-full bg-[#DDF1D8]">
                        @if(Auth::user()->profile_photo)
                            <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="{{ Auth::user()->name }}" class="h-full w-full object-cover">
                        @else
                            <span class="flex h-full w-full items-center justify-center text-sm font-black">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                        @endif
                    </span>
                    <span class="max-w-28 truncate text-sm font-bold">{{ Auth::user()->name }}</span>
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="rounded-2xl bg-[#E4F2DE] px-4 py-2 text-sm font-black text-[#2E7D32] transition hover:bg-[#D7EBCD]">
                        Logout
                    </button>
                </form>
            </div>

            <button type="button" class="grid h-10 w-10 place-items-center rounded-2xl bg-[#F1F8E9] text-[#2E7D32] ring-1 ring-[#C8E6C9] md:hidden" onclick="document.getElementById('mobile-menu').classList.toggle('hidden')">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
        </div>

        <div id="mobile-menu" class="hidden border-t border-[#DDEED8] pb-5 pt-4 md:hidden">
            <div class="grid gap-2">
                @foreach($navItems as $item)
                    <a href="{{ $item['href'] }}" class="rounded-2xl px-4 py-3 text-sm font-bold transition {{ $item['active'] ? 'bg-[#F1F8E9] text-[#0B5D1E]' : 'text-[#2E7D32] hover:bg-[#F1F8E9]' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
                <a href="{{ route('profile.show') }}" class="rounded-2xl px-4 py-3 text-sm font-bold text-[#2E7D32] hover:bg-[#F1F8E9]">Profil</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-2xl px-4 py-3 text-left text-sm font-bold text-[#2E7D32] hover:bg-[#F1F8E9]">Logout</button>
                </form>
            </div>
        </div>
    </div>
</nav>
