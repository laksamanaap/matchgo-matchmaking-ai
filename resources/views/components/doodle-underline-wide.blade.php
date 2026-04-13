{{-- Wide Doodle Underline Component --}}
{{-- Usage: <x-doodle-underline-wide color="#81C784" /> --}}

@props([
    'color' => '#81C784',
    'class' => '',
])

<svg
    width="100%"
    height="12"
    viewBox="0 0 400 12"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    {{ $attributes->merge(['class' => 'block ' . $class]) }}
    preserveAspectRatio="none"
>
    <path
        d="M2 7C30 2 60 11 90 5C120 -1 150 11 180 5C210 -1 240 11 270 5C300 -1 330 11 360 5C380 2 390 4 398 3"
        stroke="{{ $color }}"
        stroke-width="2.5"
        stroke-linecap="round"
        stroke-linejoin="round"
        style="stroke-dasharray: 500; stroke-dashoffset: 500; animation: dash 1.5s ease-out forwards;"
    />
</svg>