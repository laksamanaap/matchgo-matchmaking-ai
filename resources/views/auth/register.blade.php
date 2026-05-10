<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar — MatchGo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-900 text-white">

<div class="min-h-screen flex">
    {{-- Left panel --}}
    <div class="hidden lg:flex lg:w-1/2 relative items-center justify-center bg-gradient-to-br from-blue-700 via-blue-600 to-indigo-500 overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-10 right-10 w-64 h-64 rounded-full border-4 border-white"></div>
            <div class="absolute bottom-20 left-10 w-40 h-40 rounded-full border-4 border-white"></div>
            <div class="absolute top-1/2 right-1/3 w-80 h-80 rounded-full border-2 border-white"></div>
        </div>
        <div class="relative text-center px-12">
            <div class="text-7xl mb-6">🏆</div>
            <h1 class="text-4xl font-black tracking-tight mb-4">Gabung MatchGo</h1>
            <p class="text-xl text-blue-100 font-medium">Buat tim, tantang lawan, menangkan!</p>

            <div class="mt-10 space-y-4 text-left max-w-xs mx-auto">
                <div class="flex items-start gap-3">
                    <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="text-blue-100 text-sm">Buat tim futsal dan undang teman</p>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="text-blue-100 text-sm">Temukan lawan sepadan secara otomatis</p>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="text-blue-100 text-sm">Booking lapangan & split biaya otomatis</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Right panel --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center px-6 py-12">
        <div class="w-full max-w-md">
            <div class="lg:hidden text-center mb-10">
                <span class="text-4xl">⚽</span>
                <h1 class="text-3xl font-black text-green-400 mt-2">MatchGo</h1>
            </div>

            <h2 class="text-3xl font-bold text-white mb-2">Buat akun baru</h2>
            <p class="text-slate-400 mb-8">Daftar gratis dan mulai bermain</p>

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-300 mb-1.5">Nama Lengkap</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        value="{{ old('name') }}"
                        autocomplete="name"
                        class="w-full px-4 py-3 rounded-xl bg-slate-800 border @error('name') border-red-500 @else border-slate-700 @enderror text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                        placeholder="Ahmad Futsal"
                        required
                    >
                    @error('name')
                        <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

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
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-1.5">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        autocomplete="new-password"
                        class="w-full px-4 py-3 rounded-xl bg-slate-800 border @error('password') border-red-500 @else border-slate-700 @enderror text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                        placeholder="Min. 8 karakter"
                        required
                    >
                    @error('password')
                        <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-1.5">Konfirmasi Password</label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        autocomplete="new-password"
                        class="w-full px-4 py-3 rounded-xl bg-slate-800 border border-slate-700 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition"
                        placeholder="Ulangi password"
                        required
                    >
                </div>

                <button
                    type="submit"
                    class="w-full py-3 px-4 bg-green-600 hover:bg-green-500 text-white font-semibold rounded-xl transition focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 focus:ring-offset-slate-900"
                >
                    Buat Akun
                </button>

                <p class="text-center text-xs text-slate-500">
                    Dengan mendaftar, kamu menyetujui syarat dan ketentuan MatchGo.
                </p>
            </form>

            <p class="mt-6 text-center text-slate-400 text-sm">
                Sudah punya akun?
                <a href="{{ route('login') }}" class="text-green-400 font-semibold hover:text-green-300 transition">Masuk di sini</a>
            </p>
        </div>
    </div>
</div>

</body>
</html>
