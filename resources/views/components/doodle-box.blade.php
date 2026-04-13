{{-- Doodle Box / Rectangle Highlight --}}
{{-- Draws a hand-drawn wobbly rectangle around text --}}
{{-- Usage: <x-doodle-box color="#2E7D32">boxed text</x-doodle-box> --}}

@props([
    'color' => '#2E7D32',
    'opacity' => '0.55',
    'delay' => '0',
])

@php
    $delayMs = intval($delay);
@endphp

<span {{ $attributes->merge(['class' => 'relative inline-block']) }}>
    <span class="relative z-10">{{ $slot }}</span>
    <svg
        class="absolute pointer-events-none overflow-visible"
        viewBox="0 0 200 60"
        preserveAspectRatio="none"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        style="top: -15%; left: -6%; width: 112%; height: 130%;"
    >
        {{-- Wobbly rectangle --}}
        <path
            d="M8 8C40 4 80 6 100 5C120 4 160 6 192 8C194 20 196 35 192 52C160 55 120 54 100 55C80 56 40 54 8 52C6 35 4 20 8 8Z"
            stroke="{{ $color }}"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-opacity="{{ $opacity }}"
            class="doodle-draw"
            style="--dash-length: 700; --duration: 1s; --delay: {{ $delayMs }}ms;"
        />
    </svg>
</span>