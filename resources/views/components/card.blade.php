{{-- Feature Card Component --}}
{{-- Usage: <x-card title="Smart Matchmaking" description="..." gradient="from-[#2E7D32]/10 to-[#81C784]/10"> <x-slot:icon>...</x-slot:icon> </x-card> --}}

@props([
    'title' => '',
    'description' => '',
    'gradient' => 'from-[#2E7D32]/10 to-[#81C784]/10',
    'borderColor' => 'border-[#2E7D32]/20',
])

<div {{ $attributes->merge(['class' => "group relative bg-gradient-to-br {$gradient} bg-white rounded-2xl p-8 border {$borderColor} shadow-md hover:shadow-xl hover:scale-105 hover:-translate-y-1 transform transition-all duration-300 cursor-pointer"]) }}>
    {{-- Icon --}}
    @if(isset($icon))
        <div class="w-14 h-14 rounded-2xl bg-white shadow-sm flex items-center justify-center mb-5 group-hover:rotate-6 transition-transform duration-300">
            {{ $icon }}
        </div>
    @endif

    {{-- Title --}}
    <h3 class="text-lg font-bold text-[#1B5E20] mb-2 font-heading">
        {{ $title }}
    </h3>

    {{-- Description --}}
    <p class="text-sm text-[#2E7D32]/65 leading-relaxed">
        {{ $description }}
    </p>

    {{-- Learn More Arrow --}}
    <div class="mt-4 flex items-center gap-1 text-[#4CAF50] font-semibold text-sm opacity-0 group-hover:opacity-100 transition-opacity duration-300">
        Pelajari
        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
        </svg>
    </div>
</div>