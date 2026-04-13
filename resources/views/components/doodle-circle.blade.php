{{-- Doodle Circle Highlight Component --}}
{{-- Wraps text in a hand-drawn scribble circle, like a marker highlighting --}}
{{-- Usage: <x-doodle-circle color="#4CAF50">important text</x-doodle-circle> --}}

@props([
    'color' => '#4CAF50',
    'opacity' => '0.7',
    'delay' => '0',
    'variant' => 'default',
])

@php
    $delayMs = intval($delay);
@endphp

<span {{ $attributes->merge(['class' => 'relative inline-block']) }}>
    <span class="relative z-10">{{ $slot }}</span>
    <svg
        class="doodle-circle absolute inset-0 w-full h-full pointer-events-none overflow-visible"
        viewBox="0 0 200 80"
        preserveAspectRatio="none"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        style="top: -25%; left: -8%; width: 116%; height: 150%;"
    >
        @if($variant === 'tight')
            {{-- Tight single-loop circle --}}
            <path
                d="M100 6C140 2 185 12 190 36C195 60 160 74 110 76C60 78 10 65 6 42C2 19 50 5 100 6Z"
                stroke="{{ $color }}"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-opacity="{{ $opacity }}"
                class="doodle-draw"
                style="--dash-length: 600; --duration: 0.8s; --delay: {{ $delayMs }}ms;"
            />
        @elseif($variant === 'messy')
            {{-- Messy double-loop scribble --}}
            <path
                d="M95 8C145 1 192 15 194 40C196 65 148 76 100 77C52 78 8 68 5 44C2 20 45 6 95 8Z"
                stroke="{{ $color }}"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-opacity="{{ $opacity }}"
                class="doodle-draw"
                style="--dash-length: 600; --duration: 0.8s; --delay: {{ $delayMs }}ms;"
            />
            <path
                d="M105 4C150 -2 198 14 196 42C194 70 140 80 95 79C50 78 4 64 6 38C8 12 55 8 105 4Z"
                stroke="{{ $color }}"
                stroke-width="1.5"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-opacity="{{ floatval($opacity) * 0.5 }}"
                class="doodle-draw"
                style="--dash-length: 620; --duration: 0.9s; --delay: {{ $delayMs + 300 }}ms;"
            />
        @else
            {{-- Default double-stroke scribble circle --}}
            <path
                d="M98 6C138 0 188 14 192 40C196 66 152 78 102 76C52 74 6 62 8 38C10 14 58 12 98 6Z"
                stroke="{{ $color }}"
                stroke-width="2.5"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-opacity="{{ $opacity }}"
                class="doodle-draw"
                style="--dash-length: 600; --duration: 0.9s; --delay: {{ $delayMs }}ms;"
            />
            <path
                d="M102 10C142 5 182 18 186 38C190 58 155 72 108 74C61 76 16 66 12 44C8 22 52 13 102 10Z"
                stroke="{{ $color }}"
                stroke-width="1.5"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-opacity="{{ floatval($opacity) * 0.45 }}"
                class="doodle-draw"
                style="--dash-length: 580; --duration: 1s; --delay: {{ $delayMs + 250 }}ms;"
            />
        @endif
    </svg>
</span>