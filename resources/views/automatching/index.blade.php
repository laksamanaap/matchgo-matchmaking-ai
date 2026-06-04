@extends('layouts.app')

@section('title', 'Automatching')

@section('content')
<div class="bg-gradient-to-b from-green-50/50 to-white min-h-screen pb-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">

        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-gray-400 mb-2">
                <a href="{{ route('match.index') }}" class="hover:text-green-600 transition">Pertandingan</a>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span>Automatching</span>
            </div>
            <h1 class="text-3xl font-black text-[#1B5E20] mb-1">Automatching Realtime</h1>
            <p class="text-gray-500 text-sm">Sistem akan memasangkan tim kamu dengan lawan setara secara otomatis.</p>
        </div>

        {{-- Live matchmaking widget --}}
        <livewire:matchmaking-queue />

        {{-- How it works --}}
        <div class="mt-10 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center mb-3">
                    <span class="text-green-700 font-black">1</span>
                </div>
                <h3 class="font-bold text-gray-900 mb-1 text-sm">Masuk Antrian</h3>
                <p class="text-xs text-gray-500 leading-relaxed">Klik tombol mulai. Sistem mencatat lokasi dan level skill tim kamu.</p>
            </div>
            <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center mb-3">
                    <span class="text-green-700 font-black">2</span>
                </div>
                <h3 class="font-bold text-gray-900 mb-1 text-sm">Sistem Mencari</h3>
                <p class="text-xs text-gray-500 leading-relaxed">Algoritma membandingkan level, jarak, dan aktivitas. Toleransi melebar tiap 30 detik.</p>
            </div>
            <div class="bg-white border border-gray-100 rounded-2xl p-5 shadow-sm">
                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center mb-3">
                    <span class="text-green-700 font-black">3</span>
                </div>
                <h3 class="font-bold text-gray-900 mb-1 text-sm">Konfirmasi 30 Detik</h3>
                <p class="text-xs text-gray-500 leading-relaxed">Kedua kapten harus menerima dalam 30 detik. Setelah itu lanjut ke booking lapangan.</p>
            </div>
        </div>

    </div>
</div>
@endsection
