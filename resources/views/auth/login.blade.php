<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — MatchGo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-900 text-white">

<div class="min-h-screen flex">
    {{-- Left panel --}}
    <div class="hidden lg:flex lg:w-1/2 relative items-center justify-center bg-gradient-to-br from-green-700 via-green-600 to-emerald-500 overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-10 left-10 w-64 h-64 rounded-full border-4 border-white"></div>
            <div class="absolute bottom-20 right-10 w-40 h-40 rounded-full border-4 border-white"></div>
            <div class="absolute top-1/2 left-1/3 w-80 h-80 rounded-full border-2 border-white"></div>
        </div>
        <div class="relative text-center px-12">
            <div class="text-7xl mb-6">⚽</div>
            <h1 class="text-4xl font-black tracking-tight mb-4">MatchGo</h1>
            <p class="text-xl text-green-100 font-medium">Platform Matchmaking Futsal Terpintar</p>
            <p class="mt-4 text-green-200 text-sm leading-relaxed max-w-sm mx-auto">
                Temukan lawan sepadan, jadwalkan pertandingan, dan bayar lapangan — semuanya di satu tempat.
            </p>

            <div class="mt-10 grid grid-cols-3 gap-6 text-center">
                <div>
                    <div class="text-3xl font-black">500+</div>
                    <div class="text-green-200 text-xs mt-1">Tim Aktif</div>
                </div>
                <div>
                    <div class="text-3xl font-black">1.2K</div>
                    <div class="text-green-200 text-xs mt-1">Match Selesai</div>
                </div>
                <div>
                    <div class="text-3xl font-black">80+</div>
                    <div class="text-green-200 text-xs mt-1">Lapangan</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right panel --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-md">
            {{-- Logo mobile --}}
            <div class="lg:hidden text-center mb-10">
                <span class="text-4xl">⚽</span>
                <h1 class="text-3xl font-black text-green-400 mt-2">MatchGo</h1>
            </div>

            <h2 class="text-3xl font-bold text-white mb-2">Selamat datang kembali</h2>
            <p class="text-slate-400 mb-8">Masuk ke akun MatchGo kamu</p>

            @if(session('status'))
                <div class="mb-4 p-3 rounded-lg bg-green-500/20 border border-green-500/40 text-green-300 text-sm">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-slate-300 mb-1.5">Email</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        autocomplete="email"
                        class="w-full px-4 py-3 rounded-xl bg-slate-800 border @error('email') border-red-500 @else border-slate-700 @enderror text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                        placeholder="kamu@email.com"
                        required
                    >
                    @error('email')
                        <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-sm font-medium text-slate-300">Password</label>
                    </div>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="current-password"
                        class="w-full px-4 py-3 rounded-xl bg-slate-800 border @error('password') border-red-500 @else border-slate-700 @enderror text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                        placeholder="••••••••"
                        required
                    >
                    @error('password')
                        <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center">
                    <input type="checkbox" id="remember" name="remember" class="rounded border-slate-600 bg-slate-800 text-green-500 focus:ring-green-500">
                    <label for="remember" class="ml-2 text-sm text-slate-400">Ingat saya</label>
                </div>

                <button
                    type="submit"
                    class="w-full py-3 px-4 bg-green-600 hover:bg-green-500 text-white font-semibold rounded-xl transition focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:ring-offset-slate-900"
                >
                    Masuk
                </button>
            </form>

            <p class="mt-6 text-center text-slate-400 text-sm">
                Belum punya akun?
                <a href="{{ route('register') }}" class="text-green-400 font-semibold hover:text-green-300 transition">Daftar sekarang</a>
            </p>

            <div class="mt-8 pt-6 border-t border-slate-700/50 text-center">
                <p class="text-xs text-slate-600">Admin / Staff?
                    <a href="/admin/login" class="text-slate-500 hover:text-slate-400 transition">Login via panel admin</a>
                </p>
            </div>
        </div>
    </div>
</div>

</body>
</html>
