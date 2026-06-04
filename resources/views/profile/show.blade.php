@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#F1F8E9]">
    <x-navbar />

    <div class="pt-24 pb-12 container mx-auto px-6">
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-[#1B5E20] mb-2">Profil User</h1>
            <p class="text-[#2E7D32]/80">Kelola identitas akun yang tampil di MATCHGO.</p>
        </div>

        <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="bg-white rounded-3xl shadow-lg p-8">
                @csrf
                @method('PUT')

                <div class="mb-8 flex flex-col gap-5 md:flex-row md:items-center">
                    <div class="h-28 w-28 overflow-hidden rounded-full bg-[#81C784]/20 border border-[#81C784]/30">
                        @if($user->profile_photo)
                            <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center text-4xl font-bold text-[#2E7D32]">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-[#1B5E20]">{{ $user->name }}</h2>
                        <p class="text-[#2E7D32]/80">{{ $user->email }}</p>
                        <label class="mt-4 inline-flex cursor-pointer rounded-2xl border border-[#81C784]/40 px-5 py-3 font-semibold text-[#2E7D32] hover:bg-[#81C784]/10 transition">
                            Ganti Foto
                            <input type="file" name="profile_photo" accept="image/png,image/jpeg,image/webp" class="hidden">
                        </label>
                    </div>
                </div>

                <div class="grid gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-[#2E7D32] mb-2">Nama</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-2xl border border-[#81C784]/40 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-[#2E7D32] mb-2">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-2xl border border-[#81C784]/40 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-[#2E7D32] mb-2">WhatsApp</label>
                        <input type="text" name="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}" placeholder="08xxxxxxxxxx" class="w-full rounded-2xl border border-[#81C784]/40 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50">
                    </div>

                    <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                        <button type="submit" class="rounded-2xl bg-gradient-to-r from-[#2E7D32] to-[#4CAF50] px-6 py-3 text-white font-semibold shadow-lg hover:shadow-xl transition">
                            Simpan Profil
                        </button>
                        <a href="{{ route('dashboard') }}" class="rounded-2xl border border-[#81C784]/40 px-6 py-3 text-center font-semibold text-[#2E7D32] hover:bg-[#81C784]/10 transition">
                            Kembali
                        </a>
                    </div>
                </div>
            </form>

            <aside class="bg-white rounded-3xl shadow-lg p-6">
                <h2 class="text-2xl font-bold text-[#1B5E20] mb-4">Saran Profil</h2>
                <div class="space-y-4 text-[#2E7D32]/80">
                    <div class="rounded-2xl bg-[#F1F8E9] p-4">
                        <p class="font-semibold text-[#1B5E20]">Foto profil</p>
                        <p class="text-sm">Pakai foto yang jelas supaya captain tim lain mudah mengenali akun kamu.</p>
                    </div>
                    <div class="rounded-2xl bg-[#F1F8E9] p-4">
                        <p class="font-semibold text-[#1B5E20]">Nomor WhatsApp</p>
                        <p class="text-sm">Isi nomor aktif untuk koordinasi jadwal, pembayaran, dan konfirmasi match.</p>
                    </div>
                    <div class="rounded-2xl bg-[#F1F8E9] p-4">
                        <p class="font-semibold text-[#1B5E20]">Data tim</p>
                        <p class="text-sm">Pastikan lokasi, level, dan jumlah pemain tim sudah benar agar matchmaking lebih akurat.</p>
                    </div>
                </div>

                @if($user->team)
                    <div class="mt-6 rounded-2xl border border-[#81C784]/30 p-4">
                        <p class="text-sm text-[#2E7D32]/70">Tim Kamu</p>
                        <p class="text-xl font-bold text-[#1B5E20]">{{ $user->team->name }}</p>
                        <p class="text-sm text-[#2E7D32]/80">Status: {{ ucfirst($user->team->verification_status) }}</p>
                    </div>
                @endif
            </aside>
        </div>
    </div>
</div>
@endsection
