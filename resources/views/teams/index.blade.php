@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#F1F8E9]">
    <x-navbar />
    
    <div class="pt-24 pb-12 container mx-auto px-6">
        @if($team)
            {{-- Tim Sudah Ada --}}
            <div class="bg-white rounded-3xl shadow-lg p-8">
                <div class="flex flex-col gap-5 mb-8 md:flex-row md:items-center md:justify-between">
                    <div class="flex items-center gap-5">
                        <div class="h-24 w-24 overflow-hidden rounded-2xl border border-[#81C784]/30 bg-[#F1F8E9]">
                            @if($team->logo_url)
                                <img src="{{ asset('storage/' . $team->logo_url) }}" alt="{{ $team->name }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full w-full items-center justify-center text-3xl font-bold text-[#2E7D32]">
                                    {{ strtoupper(substr($team->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <h1 class="text-4xl font-bold text-[#1B5E20]">{{ $team->name }}</h1>
                            <p class="text-[#2E7D32]/80 mt-2">{{ $team->description }}</p>
                        </div>
                    </div>
                    <a href="{{ route('teams.index') }}" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-[#2E7D32] to-[#4CAF50] text-white font-semibold hover:shadow-lg transition">
                        Kelola Tim
                    </a>
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="rounded-2xl bg-[#F1F8E9] p-6">
                        <p class="text-sm text-[#2E7D32]/80 mb-1">Level Tim</p>
                        <p class="text-2xl font-bold text-[#1B5E20] capitalize">{{ $team->skill_level }}</p>
                    </div>
                    <div class="rounded-2xl bg-[#F1F8E9] p-6">
                        <p class="text-sm text-[#2E7D32]/80 mb-1">Domisili</p>
                        <p class="text-2xl font-bold text-[#1B5E20]">{{ $team->city }}</p>
                    </div>
                    <div class="rounded-2xl bg-[#F1F8E9] p-6">
                        <p class="text-sm text-[#2E7D32]/80 mb-1">Kontak</p>
                        <p class="text-2xl font-bold text-[#1B5E20]">{{ $team->contact_number }}</p>
                    </div>
                    <div class="rounded-2xl bg-[#F1F8E9] p-6">
                        <p class="text-sm text-[#2E7D32]/80 mb-1">Status</p>
                        <span class="inline-block px-4 py-2 rounded-xl text-white text-sm font-semibold {{ $team->verification_status === 'verified' ? 'bg-green-500' : 'bg-yellow-500' }}">
                            {{ ucfirst($team->verification_status) }}
                        </span>
                    </div>
                </div>
            </div>
        @else
            {{-- Form Buat Tim Baru --}}
            <div class="bg-white rounded-3xl shadow-lg p-8 max-w-2xl mx-auto">
                <h1 class="text-4xl font-bold text-[#1B5E20] mb-2">Buat Tim Futsal</h1>
                <p class="text-[#2E7D32]/80 mb-8">Isi data tim kamu untuk mulai bermain dan mencari lawan.</p>

                <form method="POST" action="{{ route('teams.store') }}" enctype="multipart/form-data" class="grid gap-6">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-[#2E7D32] mb-2">Logo Tim</label>
                        <input type="file" name="logo" accept="image/png,image/jpeg,image/webp" class="w-full rounded-2xl border border-[#81C784]/40 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50 @error('logo') border-red-500 @enderror" />
                        @error('logo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-semibold text-[#2E7D32] mb-2">Nama Tim</label>
                            <input type="text" name="team_name" value="{{ old('team_name') }}" required class="w-full rounded-2xl border border-[#81C784]/40 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50 @error('team_name') border-red-500 @enderror" />
                            @error('team_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-[#2E7D32] mb-2">Level Tim</label>
                            <select name="team_level" required class="w-full rounded-2xl border border-[#81C784]/40 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50 @error('team_level') border-red-500 @enderror">
                                <option value="">-- Pilih Level --</option>
                                <option value="casual" {{ old('team_level') === 'casual' ? 'selected' : '' }}>Casual (Santai)</option>
                                <option value="semi_pro" {{ old('team_level') === 'semi_pro' ? 'selected' : '' }}>Semi-Pro</option>
                                <option value="competitive" {{ old('team_level') === 'competitive' ? 'selected' : '' }}>Competitive (Profesional)</option>
                            </select>
                            @error('team_level') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-[#2E7D32] mb-2">Deskripsi Tim</label>
                        <textarea name="description" rows="4" class="w-full rounded-2xl border border-[#81C784]/40 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50 @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                        @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-semibold text-[#2E7D32] mb-2">Domisili</label>
                            <input type="text" name="domicile" value="{{ old('domicile') }}" required class="w-full rounded-2xl border border-[#81C784]/40 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50 @error('domicile') border-red-500 @enderror" />
                            @error('domicile') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#2E7D32] mb-2">Nomor Kontak</label>
                            <input type="text" name="contact_number" value="{{ old('contact_number') }}" required class="w-full rounded-2xl border border-[#81C784]/40 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50 @error('contact_number') border-red-500 @enderror" />
                            @error('contact_number') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="block text-sm font-semibold text-[#2E7D32] mb-2">Latitude</label>
                            <input type="number" step="0.000001" name="latitude" value="{{ old('latitude', '-6.2') }}" required class="w-full rounded-2xl border border-[#81C784]/40 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50 @error('latitude') border-red-500 @enderror" />
                            @error('latitude') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-[#2E7D32] mb-2">Longitude</label>
                            <input type="number" step="0.000001" name="longitude" value="{{ old('longitude', '106.8') }}" required class="w-full rounded-2xl border border-[#81C784]/40 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-[#4CAF50]/50 @error('longitude') border-red-500 @enderror" />
                            @error('longitude') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <button type="submit" class="w-full rounded-2xl bg-gradient-to-r from-[#2E7D32] to-[#4CAF50] px-4 py-3 text-white font-semibold shadow-lg hover:shadow-xl transition">
                        Buat Tim
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
