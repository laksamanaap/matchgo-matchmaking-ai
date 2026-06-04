@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-[#F1F8E9] px-4 py-10">
    <div class="w-full max-w-md bg-white rounded-3xl shadow-xl p-8">
        <h1 class="text-3xl font-bold text-[#1B5E20] mb-2">Masuk ke MATCHGO</h1>
        <p class="text-sm text-[#2E7D32]/80 mb-8">Masuk untuk mengelola tim, meminta lawan, dan booking lapangan.</p>

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-sm font-semibold text-[#2E7D32] mb-2">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required class="w-full rounded-2xl border border-[#81C784]/40 px-4 py-3 text-sm text-[#1B5E20] focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50" />
            </div>

            <div>
                <label for="password" class="block text-sm font-semibold text-[#2E7D32] mb-2">Password</label>
                <input id="password" name="password" type="password" required class="w-full rounded-2xl border border-[#81C784]/40 px-4 py-3 text-sm text-[#1B5E20] focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50" />
            </div>

            <button type="submit" class="w-full rounded-2xl bg-gradient-to-r from-[#2E7D32] to-[#4CAF50] px-4 py-3 text-white font-semibold shadow-lg hover:shadow-xl transition">Masuk</button>
        </form>

        <div class="mt-6 text-center text-sm text-[#2E7D32]/80">
            Belum punya akun? <a href="{{ route('register.form') }}" class="font-semibold text-[#1B5E20] hover:text-[#4CAF50]">Daftar Tim</a>
        </div>
    </div>
</div>
@endsection
