{{-- Doodle Highlight (Marker-style background swipe) --}}
{{-- Highlights text with a marker-pen swoosh behind it --}}
{{-- Usage: <x-doodle-highlight color="#81C784">key phrase</x-doodle-highlight> --}}

@props([
    'color' => '#81C784',
    'opacity' => '0.25',
    'delay' => '0',
])

@php
    $delayMs = intval($delay);
@endphp

<span {{ $attributes->merge(['class' => 'relative inline-block']) }}>
    <span class="relative z-10">{{ $slot }}</span>
    <svg
        class="doodle-highlight absolute pointer-events-none overflow-visible"
        viewBox="0 0 200 30"
        preserveAspectRatio="none"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        style="top: 20%; left: -4%; width: 108%; height: 80%;"
    >
        {{-- Thick marker swipe --}}
        <path
            d="M4 16C30 8 60 20 100 12C140 4 170 18 196 14"
            stroke="{{ $color }}"
            stroke-width="22"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-opacity="{{ $opacity }}"
            class="doodle-draw"
            style="--dash-length: 300; --duration: 0.6s; --delay: {{ $delayMs }}ms;"
        />
    </svg>
</span>