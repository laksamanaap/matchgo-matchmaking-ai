{{-- Button Component --}}
{{-- Usage: <x-button href="#" variant="primary">Daftar Tim</x-button> --}}

@props([
    'href' => '#',
    'variant' => 'primary',
    'size' => 'md',
    'tag' => 'a',
])

@php
    $baseClasses = 'inline-flex items-center justify-center gap-2 font-bold rounded-2xl transition-all duration-300 transform hover:scale-105';

    $variantClasses = match($variant) {
        'primary' => 'bg-gradient-to-r from-[#2E7D32] to-[#4CAF50] text-white shadow-lg hover:shadow-xl',
        'secondary' => 'bg-white text-[#2E7D32] shadow-md border-2 border-[#81C784]/30 hover:border-[#4CAF50] hover:shadow-lg',
        'outline' => 'bg-transparent text-white border-2 border-white/40 hover:bg-white/10 hover:border-white',
        'ghost' => 'text-[#2E7D32] hover:bg-[#81C784]/10',
        'white' => 'bg-white text-[#2E7D32] shadow-lg hover:shadow-xl',
        default => 'bg-gradient-to-r from-[#2E7D32] to-[#4CAF50] text-white shadow-lg hover:shadow-xl',
    };

    $sizeClasses = match($size) {
        'sm' => 'px-5 py-2.5 text-sm',
        'md' => 'px-6 py-3 text-base',
        'lg' => 'px-8 py-4 text-lg',
        'xl' => 'px-10 py-4 text-lg',
        default => 'px-6 py-3 text-base',
    };

    $classes = $baseClasses . ' ' . $variantClasses . ' ' . $sizeClasses;
@endphp

@if($tag === 'button')
    <button {{ $attributes->merge(['class' => $classes, 'type' => 'button']) }}>
        {{ $slot }}
    </button>
@else
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@endif