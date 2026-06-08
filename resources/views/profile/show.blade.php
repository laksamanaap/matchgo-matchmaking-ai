@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#F1F8E9]">
    <x-navbar />

    <main class="mx-auto max-w-6xl px-4 pb-12 pt-24 sm:px-6 lg:px-8">
        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-[#C8E6C9] bg-white px-5 py-4 text-sm font-bold text-[#1B5E20] shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <section class="mb-6 overflow-hidden rounded-3xl border border-[#DDEED8] bg-white shadow-xl shadow-[#1B5E20]/10">
            <div class="grid gap-6 p-6 sm:p-8 lg:grid-cols-[1fr_auto] lg:items-center">
                <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
                    <div class="h-24 w-24 shrink-0 overflow-hidden rounded-3xl border border-[#C8E6C9] bg-[#E4F2DE]">
                        @if($user->profile_photo)
                            <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center text-4xl font-black text-[#2E7D32]">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <div class="min-w-0">
                        <p class="text-sm font-black uppercase tracking-[0.18em] text-[#4B8B43]">Profil Akun</p>
                        <h1 class="mt-2 truncate text-4xl font-black text-[#0B5D1E]">{{ $user->name }}</h1>
                        <p class="mt-1 truncate text-[#4B8B43]">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="grid gap-3 sm:grid-cols-2 lg:min-w-72">
                    <div class="rounded-2xl bg-[#F1F8E9] px-4 py-3">
                        <p class="text-xs font-bold text-[#4B8B43]">Role</p>
                        <p class="mt-1 text-lg font-black capitalize text-[#0B5D1E]">{{ str_replace('_', ' ', $user->role) }}</p>
                    </div>
                    <div class="rounded-2xl bg-[#F1F8E9] px-4 py-3">
                        <p class="text-xs font-bold text-[#4B8B43]">Tim</p>
                        <p class="mt-1 truncate text-lg font-black text-[#0B5D1E]">{{ $user->team?->name ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-xl shadow-[#1B5E20]/10 sm:p-8">
                @csrf
                @method('PUT')

                <div class="mb-6 flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.16em] text-[#4B8B43]">Identitas</p>
                        <h2 class="mt-2 text-2xl font-black text-[#0B5D1E]">Edit profil</h2>
                    </div>
                    <x-heroicon-o-user-circle class="h-8 w-8 text-[#43A047]" />
                </div>

                <div class="grid gap-5">
                    <div>
                        <label class="mb-2 block text-sm font-bold text-[#1B5E20]">Foto profil</label>
                        <label class="flex cursor-pointer items-center justify-between gap-4 rounded-2xl border border-[#C8E6C9] bg-[#F8FCF4] px-4 py-3 text-sm font-bold text-[#2E7D32] transition hover:bg-[#F1F8E9]">
                            <span>Pilih foto baru</span>
                            <x-heroicon-o-camera class="h-5 w-5" />
                            <input type="file" name="profile_photo" accept="image/png,image/jpeg,image/webp" class="hidden">
                        </label>
                        <p class="mt-2 text-xs font-semibold text-[#4B8B43]">Format JPG, PNG, atau WebP maksimal 2 MB.</p>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-[#1B5E20]">Nama</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full rounded-2xl border border-[#C8E6C9] bg-[#F8FCF4] px-4 py-3 text-[#0B5D1E] outline-none transition focus:border-[#2E8B3C] focus:ring-2 focus:ring-[#4CAF50]/20">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-[#1B5E20]">Email</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full rounded-2xl border border-[#C8E6C9] bg-[#F8FCF4] px-4 py-3 text-[#0B5D1E] outline-none transition focus:border-[#2E8B3C] focus:ring-2 focus:ring-[#4CAF50]/20">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-[#1B5E20]">WhatsApp</label>
                        <input type="text" name="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}" placeholder="08xxxxxxxxxx" class="w-full rounded-2xl border border-[#C8E6C9] bg-[#F8FCF4] px-4 py-3 text-[#0B5D1E] outline-none transition focus:border-[#2E8B3C] focus:ring-2 focus:ring-[#4CAF50]/20">
                    </div>

                    <div class="flex flex-col gap-3 pt-2 sm:flex-row">
                        <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#2E8B3C] px-6 py-3 font-black text-white shadow-md shadow-[#1B5E20]/15 transition hover:bg-[#23742F]">
                            <x-heroicon-o-check class="h-5 w-5" />
                            Simpan Profil
                        </button>
                        <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-2xl border border-[#C8E6C9] bg-[#F8FCF4] px-6 py-3 font-black text-[#2E7D32] transition hover:bg-[#F1F8E9]">
                            Kembali
                        </a>
                    </div>
                </div>
            </form>

            <form method="POST" action="{{ route('profile.password.update') }}" class="rounded-3xl border border-[#DDEED8] bg-white p-6 shadow-xl shadow-[#1B5E20]/10 sm:p-8">
                @csrf
                @method('PUT')

                <div class="mb-6 flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.16em] text-[#4B8B43]">Keamanan</p>
                        <h2 class="mt-2 text-2xl font-black text-[#0B5D1E]">Ganti password</h2>
                    </div>
                    <x-heroicon-o-lock-closed class="h-8 w-8 text-[#43A047]" />
                </div>

                <div class="grid gap-5">
                    <div>
                        <label class="mb-2 block text-sm font-bold text-[#1B5E20]">Password lama</label>
                        <input type="password" name="current_password" required autocomplete="current-password" class="w-full rounded-2xl border border-[#C8E6C9] bg-[#F8FCF4] px-4 py-3 text-[#0B5D1E] outline-none transition focus:border-[#2E8B3C] focus:ring-2 focus:ring-[#4CAF50]/20">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-[#1B5E20]">Password baru</label>
                        <input type="password" name="password" required autocomplete="new-password" class="w-full rounded-2xl border border-[#C8E6C9] bg-[#F8FCF4] px-4 py-3 text-[#0B5D1E] outline-none transition focus:border-[#2E8B3C] focus:ring-2 focus:ring-[#4CAF50]/20">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-[#1B5E20]">Konfirmasi password baru</label>
                        <input type="password" name="password_confirmation" required autocomplete="new-password" class="w-full rounded-2xl border border-[#C8E6C9] bg-[#F8FCF4] px-4 py-3 text-[#0B5D1E] outline-none transition focus:border-[#2E8B3C] focus:ring-2 focus:ring-[#4CAF50]/20">
                    </div>

                    <div class="rounded-2xl bg-[#F1F8E9] px-4 py-3 text-sm font-semibold text-[#4B8B43]">
                        Password baru minimal 8 karakter dan akan aktif setelah disimpan.
                    </div>

                    <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-[#0B5D1E] px-6 py-3 font-black text-white shadow-md shadow-[#1B5E20]/15 transition hover:bg-[#084817]">
                        <x-heroicon-o-key class="h-5 w-5" />
                        Update Password
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
@endsection
