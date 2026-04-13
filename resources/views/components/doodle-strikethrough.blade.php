{{-- Doodle Cross-out / Strikethrough --}}
{{-- Draws a hand-drawn wavy strikethrough (useful for "old" text) --}}
{{-- Usage: <x-doodle-strikethrough color="#ef4444">wrong text</x-doodle-strikethrough> --}}

@props([
    'color' => '#ef4444',
    'opacity' => '0.6',
    'delay' => '0',
])

@php
    $delayMs = intval($delay);
@endphp

<span {{ $attributes->merge(['class' => 'relative inline-block']) }}>
    <span class="relative z-10">{{ $slot }}</span>
    <svg
        class="absolute pointer-events-none overflow-visible"
        viewBox="0 0 200 20"
        preserveAspectRatio="none"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        style="top: 30%; left: -4%; width: 108%; height: 40%;"
    >
        <path
            d="M4 12C30 6 60 16 100 10C140 4 170 14 196 8"
            stroke="{{ $color }}"
            stroke-width="3"
            stroke-linecap="round"
            stroke-opacity="{{ $opacity }}"
            class="doodle-draw"
            style="--dash-length: 280; --duration: 0.5s; --delay: {{ $delayMs }}ms;"
        />
    </svg>
</span>