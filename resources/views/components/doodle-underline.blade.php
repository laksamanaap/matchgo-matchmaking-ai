{{-- Doodle Underline Component --}}
{{-- Usage: <x-doodle-underline :width="200" color="#81C784" /> --}}
{{--        <x-doodle-underline :width="200" color="#81C784" delay="300" variant="thick" /> --}}

@props([
    'width' => 200,
    'color' => '#81C784',
    'class' => '',
    'delay' => '0',
    'variant' => 'default',
])

@php
    $delayMs = intval($delay);
@endphp

<svg
    width="{{ $width }}"
    height="14"
    viewBox="0 0 200 14"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    {{ $attributes->merge(['class' => 'block ' . $class]) }}
    preserveAspectRatio="none"
>
    @if($variant === 'thick')
        {{-- Single thick wobbly underline --}}
        <path
            d="M2 7C20 3 40 11 60 5C80 -1 100 11 120 5C140 -1 160 11 180 5C190 2 195 5 198 4"
            stroke="{{ $color }}"
            stroke-width="4.5"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-opacity="0.7"
            class="doodle-draw"
            style="--dash-length: 300; --duration: 1s; --delay: {{ $delayMs }}ms;"
        />
    @elseif($variant === 'zigzag')
        {{-- Zigzag style --}}
        <path
            d="M2 4L15 12L30 4L45 12L60 4L75 12L90 4L105 12L120 4L135 12L150 4L165 12L180 4L195 10"
            stroke="{{ $color }}"
            stroke-width="2.5"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-opacity="0.65"
            class="doodle-draw"
            style="--dash-length: 400; --duration: 1.2s; --delay: {{ $delayMs }}ms;"
        />
    @else
        {{-- Default double-line wavy underline --}}
        <path
            d="M2 8C20 2 40 12 60 6C80 0 100 12 120 6C140 0 160 12 180 6C190 3 195 5 198 4"
            stroke="{{ $color }}"
            stroke-width="3"
            stroke-linecap="round"
            stroke-linejoin="round"
            class="doodle-draw"
            style="--dash-length: 300; --duration: 1s; --delay: {{ $delayMs }}ms;"
        />
        <path
            d="M5 11C25 6 45 13 65 9C85 5 105 13 125 8C145 3 165 11 185 8"
            stroke="{{ $color }}"
            stroke-width="2"
            stroke-linecap="round"
            stroke-opacity="0.4"
            class="doodle-draw"
            style="--dash-length: 250; --duration: 1.2s; --delay: {{ $delayMs + 250 }}ms;"
        />
    @endif
</svg>