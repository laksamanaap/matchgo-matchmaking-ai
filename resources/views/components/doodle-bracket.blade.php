{{-- Doodle Bracket / Emphasis Marks --}}
{{-- Draws hand-drawn curly brackets or arrow around text --}}
{{-- Usage: <x-doodle-bracket color="#2E7D32">emphasized text</x-doodle-bracket> --}}

@props([
    'color' => '#2E7D32',
    'opacity' => '0.6',
    'delay' => '0',
    'variant' => 'arrow',
])

@php
    $delayMs = intval($delay);
@endphp

<span {{ $attributes->merge(['class' => 'relative inline-block']) }}>
    <span class="relative z-10">{{ $slot }}</span>

    @if($variant === 'arrow')
        {{-- Hand-drawn arrow pointing down --}}
        <svg
            class="absolute pointer-events-none overflow-visible"
            viewBox="0 0 60 40"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            style="top: -45%; right: -20%; width: 30px; height: 24px;"
        >
            <path
                d="M50 4C42 8 30 20 28 34"
                stroke="{{ $color }}"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-opacity="{{ $opacity }}"
                class="doodle-draw"
                style="--dash-length: 60; --duration: 0.5s; --delay: {{ $delayMs }}ms;"
            />
            <path
                d="M20 26L28 34L36 26"
                stroke="{{ $color }}"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-opacity="{{ $opacity }}"
                class="doodle-draw"
                style="--dash-length: 30; --duration: 0.3s; --delay: {{ $delayMs + 400 }}ms;"
            />
        </svg>
    @elseif($variant === 'stars')
        {{-- Hand-drawn sparkle stars --}}
        <svg
            class="absolute pointer-events-none overflow-visible"
            viewBox="0 0 40 40"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            style="top: -40%; right: -15%; width: 22px; height: 22px;"
        >
            <path
                d="M20 4L22 16L34 18L22 20L20 32L18 20L6 18L18 16Z"
                stroke="{{ $color }}"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-opacity="{{ $opacity }}"
                class="doodle-draw"
                style="--dash-length: 120; --duration: 0.6s; --delay: {{ $delayMs }}ms;"
            />
        </svg>
        <svg
            class="absolute pointer-events-none overflow-visible"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
            style="top: -55%; right: -30%; width: 14px; height: 14px;"
        >
            <path
                d="M12 2L13.5 10L22 12L13.5 14L12 22L10.5 14L2 12L10.5 10Z"
                stroke="{{ $color }}"
                stroke-width="1.5"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-opacity="{{ floatval($opacity) * 0.6 }}"
                class="doodle-draw"
                style="--dash-length: 80; --duration: 0.5s; --delay: {{ $delayMs + 300 }}ms;"
            />
        </svg>
    @endif
</span>