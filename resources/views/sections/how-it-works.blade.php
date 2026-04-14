{{-- How It Works Section --}}
<section id="how-it-works" class="py-20 md:py-28 bg-white relative overflow-hidden">

    {{-- Decorative center line --}}
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-px h-full bg-[#81C784]/10"></div>

    <div class="container mx-auto px-6 lg:px-10 relative z-10">

        {{-- Section Header --}}
        <div class="text-center max-w-2xl mx-auto mb-16">
            <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-[#81C784]/15 text-[#2E7D32] text-sm font-bold rounded-full mb-4">
                <x-heroicon-o-paper-airplane class="h-4 w-4 text-[#4CAF50] shrink-0" />
                Cara Kerja
            </span>
            <h2 class="text-3xl md:text-4xl font-extrabold text-[#1B5E20] mb-3 font-heading">
                <x-doodle-highlight color="#81C784" delay="100" opacity="0.2">Mudah Banget</x-doodle-highlight>,
                <x-doodle-circle color="#2E7D32" delay="400" variant="tight">4 Langkah!</x-doodle-circle>
            </h2>
            <p class="text-[#2E7D32]/60 text-lg mt-4">
                Dari daftar sampai main, semuanya
                <span class="relative inline-block font-semibold text-[#2E7D32]">cepat dan gampang</span>.
            </p>
        </div>

        {{-- Steps Grid --}}
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">

            @php
                $steps = [
                    ['number' => '01', 'icon' => 'clipboard-document-list', 'title' => 'Buat Profil Tim', 'description' => 'Daftarkan tim futsal kamu, masukkan lokasi, dan pilih level permainan: Casual, Semi-Pro, atau Competitive.'],
                    ['number' => '02', 'icon' => 'calendar-days', 'title' => 'Atur Jadwal', 'description' => 'Masukkan jadwal ketersediaan bermain — sistem akan mencocokkan dengan tim lain yang punya jadwal sama.'],
                    ['number' => '03', 'icon' => 'magnifying-glass', 'title' => 'Cari Lawan', 'description' => "Tekan tombol 'Cari Lawan' dan biarkan MATCHGO menemukan lawan terbaik berdasarkan level, lokasi, dan jadwal."],
                    ['number' => '04', 'icon' => 'play-circle', 'title' => 'Main!', 'description' => 'Datang ke lapangan yang sudah ditentukan. Biaya sudah dibagi rata. Tinggal fokus main dan menang!'],
                ];
            @endphp

            @foreach($steps as $index => $step)
                <div class="relative group">
                    <div class="bg-gradient-to-br from-[#F1F8E9] to-white rounded-2xl p-8 border border-[#81C784]/20 shadow-sm hover:shadow-lg hover:-translate-y-2 transform transition-all duration-300 h-full">
                        <div class="text-5xl font-black text-[#81C784]/20 mb-2 font-heading">
                            {{ $step['number'] }}
                        </div>
                        <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-[#4CAF50]/10 text-[#2E7D32] group-hover:scale-110 transition-transform duration-300" aria-hidden="true">
                            <x-dynamic-component :component="'heroicon-o-'.$step['icon']" class="h-8 w-8" />
                        </div>
                        <h3 class="text-lg font-bold text-[#1B5E20] mb-2 font-heading">
                            {{ $step['title'] }}
                        </h3>
                        <p class="text-sm text-[#2E7D32]/60 leading-relaxed">
                            {{ $step['description'] }}
                        </p>
                    </div>
                    @if($index < 3)
                        <div class="hidden lg:block absolute top-1/2 -right-4 z-10 text-[#81C784]" aria-hidden="true">
                            <x-heroicon-o-arrow-right class="h-6 w-6" />
                        </div>
                    @endif
                </div>
            @endforeach

        </div>
    </div>
</section>