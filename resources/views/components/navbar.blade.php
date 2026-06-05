{{-- Navbar Component for Authenticated Users --}}
@php
    $navItems = [
        ['label' => 'Buat Pertandingan', 'href' => route('matches.create'), 'active' => request()->routeIs('matches.create')],
        ['label' => 'Cari Pertandingan', 'href' => route('matches.take'), 'active' => request()->routeIs('matches.take')],
        ['label' => 'AutoMatching', 'href' => route('matches.auto'), 'active' => request()->routeIs('matches.auto')],
    ];

    $accountItems = [
        ['label' => 'Daftar Pertandingan', 'href' => route('matches.index'), 'active' => request()->routeIs('matches.index') || request()->routeIs('matchmaking.index')],
        ['label' => 'Tim', 'href' => url('/teams'), 'active' => request()->is('teams*')],
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
                <x-tooltip text="Belum ada notifikasi baru">
                    <button type="button" class="relative grid h-11 w-11 place-items-center rounded-2xl bg-[#F8FCF4] text-[#2E7D32] ring-1 ring-[#DDEED8] transition hover:bg-[#F1F8E9] hover:text-[#0B5D1E]" aria-label="Notifikasi">
                        <x-heroicon-o-bell class="h-5 w-5" />
                        <span class="absolute right-2.5 top-2.5 h-2 w-2 rounded-full bg-[#43A047] ring-2 ring-white"></span>
                    </button>
                </x-tooltip>

                <div class="relative">
                    <button id="account-menu-button" type="button" class="inline-flex items-center gap-3 rounded-2xl bg-[#F8FCF4] px-3 py-2 text-left text-[#0B5D1E] ring-1 ring-[#DDEED8] transition hover:bg-[#F1F8E9]" onclick="toggleAccountMenu()" aria-haspopup="menu" aria-expanded="false">
                        <span class="h-8 w-8 overflow-hidden rounded-full bg-[#DDF1D8]">
                            @if(Auth::user()->profile_photo)
                                <img src="{{ asset('storage/' . Auth::user()->profile_photo) }}" alt="{{ Auth::user()->name }}" class="h-full w-full object-cover">
                            @else
                                <span class="flex h-full w-full items-center justify-center text-sm font-black">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                            @endif
                        </span>
                        <span class="min-w-0">
                            <span class="block max-w-32 truncate text-sm font-bold">{{ Auth::user()->name }}</span>
                            <span class="block max-w-32 truncate text-xs font-semibold text-[#2E7D32]/60">{{ Auth::user()->email }}</span>
                        </span>
                        <x-heroicon-o-chevron-down class="h-4 w-4 shrink-0 text-[#2E7D32]/70" />
                    </button>

                    <div id="account-menu" class="hidden absolute right-0 top-full mt-3 w-72 overflow-hidden rounded-2xl bg-white text-[#0B5D1E] shadow-xl ring-1 ring-[#DDEED8]" role="menu">
                        <div class="border-b border-[#DDEED8] px-4 py-3">
                            <p class="text-sm font-black">My Account</p>
                            <p class="mt-1 truncate text-xs font-semibold text-[#2E7D32]/60">{{ Auth::user()->email }}</p>
                        </div>

                        <div class="grid gap-1 p-2">
                            <a href="{{ route('profile.show') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold text-[#2E7D32] transition hover:bg-[#F1F8E9]" role="menuitem">
                                <x-heroicon-o-user class="h-5 w-5" />
                                <span>Profile</span>
                            </a>

                            @foreach($accountItems as $item)
                                <a href="{{ $item['href'] }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-bold transition {{ $item['active'] ? 'bg-[#F1F8E9] text-[#0B5D1E]' : 'text-[#2E7D32] hover:bg-[#F1F8E9]' }}" role="menuitem">
                                    <x-heroicon-o-chevron-right class="h-5 w-5" />
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @endforeach

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-bold text-[#B94A57] transition hover:bg-[#FFF1F2]" role="menuitem">
                                    <x-heroicon-o-arrow-right-on-rectangle class="h-5 w-5" />
                                    <span>Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
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
                <div class="rounded-2xl bg-[#F8FCF4] px-4 py-3 ring-1 ring-[#DDEED8]">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm font-bold text-[#2E7D32]">Notifikasi</span>
                        <x-heroicon-o-bell class="h-5 w-5 text-[#43A047]" />
                    </div>
                    <p class="mt-1 text-xs font-semibold text-[#2E7D32]/70">Belum ada notifikasi baru</p>
                </div>
                <a href="{{ route('profile.show') }}" class="rounded-2xl px-4 py-3 text-sm font-bold text-[#2E7D32] hover:bg-[#F1F8E9]">Profil</a>
                @foreach($accountItems as $item)
                    <a href="{{ $item['href'] }}" class="rounded-2xl px-4 py-3 text-sm font-bold transition {{ $item['active'] ? 'bg-[#F1F8E9] text-[#0B5D1E]' : 'text-[#2E7D32] hover:bg-[#F1F8E9]' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full rounded-2xl px-4 py-3 text-left text-sm font-bold text-[#2E7D32] hover:bg-[#F1F8E9]">Logout</button>
                </form>
            </div>
        </div>
    </div>
</nav>

<script>
    function toggleAccountMenu() {
        const menu = document.getElementById('account-menu');
        const button = document.getElementById('account-menu-button');
        const isHidden = menu.classList.toggle('hidden');

        button.setAttribute('aria-expanded', String(!isHidden));
    }

    document.addEventListener('click', function (event) {
        const menu = document.getElementById('account-menu');
        const button = document.getElementById('account-menu-button');

        if (!menu || !button || menu.classList.contains('hidden')) {
            return;
        }

        if (!menu.contains(event.target) && !button.contains(event.target)) {
            menu.classList.add('hidden');
            button.setAttribute('aria-expanded', 'false');
        }
    });
</script>
