@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#F1F8E9] text-[#0B5D1E]">
    <main class="mx-auto grid min-h-screen max-w-7xl items-center gap-8 px-4 py-8 sm:px-6 lg:grid-cols-[0.95fr_1.05fr] lg:px-8">
        <section class="hidden overflow-hidden rounded-3xl border border-[#DDEED8] bg-white shadow-xl shadow-[#1B5E20]/10 lg:block">
            <div class="relative min-h-[680px] p-8">
                <div class="absolute inset-0 field-pattern opacity-60"></div>
                <div class="relative flex h-full min-h-[616px] flex-col justify-between">
                    <a href="{{ route('home') }}" class="inline-flex w-fit items-center gap-3 rounded-2xl bg-[#F8FCF4] px-4 py-3 ring-1 ring-[#DDEED8]">
                        <img src="{{ asset('matchgo-logo.svg') }}" alt="MATCHGO" class="h-10 w-10">
                        <span class="text-2xl font-black tracking-tight font-heading">MATCH<span class="text-[#43A047]">GO.</span></span>
                    </a>

                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.18em] text-[#4B8B43]">Matchmaking futsal</p>
                        <h1 class="mt-4 max-w-xl text-5xl font-black leading-tight">
                            Kelola tim, cari lawan, dan booking lapangan lebih cepat.
                        </h1>
                        <p class="mt-4 max-w-lg text-base font-semibold leading-7 text-[#4B8B43]">
                            Masuk untuk melihat jadwal, pembayaran, AutoMatching, dan aktivitas tim kamu dalam satu dashboard.
                        </p>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl bg-[#F1F8E9] p-4">
                            <x-heroicon-o-users class="h-7 w-7 text-[#43A047]" />
                            <p class="mt-3 text-sm font-black">Tim</p>
                            <p class="mt-1 text-xs font-semibold text-[#4B8B43]">Roster dan verifikasi</p>
                        </div>
                        <div class="rounded-2xl bg-[#F1F8E9] p-4">
                            <x-heroicon-o-calendar-days class="h-7 w-7 text-[#43A047]" />
                            <p class="mt-3 text-sm font-black">Jadwal</p>
                            <p class="mt-1 text-xs font-semibold text-[#4B8B43]">Booking pertandingan</p>
                        </div>
                        <div class="rounded-2xl bg-[#F1F8E9] p-4">
                            <x-heroicon-o-bolt class="h-7 w-7 text-[#43A047]" />
                            <p class="mt-3 text-sm font-black">Auto</p>
                            <p class="mt-1 text-xs font-semibold text-[#4B8B43]">Cari lawan instan</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="mx-auto w-full max-w-md">
            <div class="mb-8 flex items-center justify-between gap-4 lg:hidden">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                    <img src="{{ asset('matchgo-logo.svg') }}" alt="MATCHGO" class="h-11 w-11">
                    <span class="text-2xl font-black tracking-tight font-heading">MATCH<span class="text-[#43A047]">GO.</span></span>
                </a>
            </div>

            <div class="rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-xl shadow-[#1B5E20]/10 sm:p-8">
                <div class="mb-7">
                    <p class="text-sm font-black uppercase tracking-[0.16em] text-[#4B8B43]">Masuk Akun</p>
                    <h2 class="mt-2 text-4xl font-black">Selamat datang</h2>
                    <p class="mt-2 text-sm font-semibold text-[#4B8B43]">Gunakan akun captain untuk mengelola tim MATCHGO.</p>
                </div>

                @if($errors->any())
                    <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="grid gap-5">
                    @csrf

                    <div>
                        <label for="email" class="mb-2 block text-sm font-bold text-[#1B5E20]">Email</label>
                        <div class="relative">
                            <x-heroicon-o-envelope class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-[#43A047]" />
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" placeholder="captain@email.com" class="w-full rounded-2xl border border-[#C8E6C9] bg-[#F8FCF4] px-4 py-3 pl-12 text-sm font-semibold text-[#0B5D1E] outline-none transition placeholder:text-[#4B8B43]/50 focus:border-[#2E8B3C] focus:ring-2 focus:ring-[#4CAF50]/20" />
                        </div>
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-sm font-bold text-[#1B5E20]">Password</label>
                        <div class="relative">
                            <x-heroicon-o-lock-closed class="pointer-events-none absolute left-4 top-1/2 h-5 w-5 -translate-y-1/2 text-[#43A047]" />
                            <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="Password akun" class="w-full rounded-2xl border border-[#C8E6C9] bg-[#F8FCF4] px-4 py-3 pl-12 text-sm font-semibold text-[#0B5D1E] outline-none transition placeholder:text-[#4B8B43]/50 focus:border-[#2E8B3C] focus:ring-2 focus:ring-[#4CAF50]/20" />
                        </div>
                    </div>

                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-[#2E8B3C] px-5 py-3.5 text-sm font-black text-white shadow-md shadow-[#1B5E20]/15 transition hover:bg-[#23742F]">
                        Masuk
                        <x-heroicon-o-arrow-right class="h-5 w-5" />
                    </button>
                </form>

                <div class="mt-6 rounded-2xl bg-[#F1F8E9] px-4 py-3 text-center text-sm font-bold text-[#4B8B43]">
                    Belum punya akun?
                    <a href="{{ route('register.form') }}" class="text-[#0B5D1E] hover:text-[#43A047]">Daftar sekarang</a>
                </div>
            </div>
        </section>
    </main>
</div>
@endsection
