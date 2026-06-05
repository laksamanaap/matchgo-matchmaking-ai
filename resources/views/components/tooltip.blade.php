@props([
    'text',
    'position' => 'bottom',
])

@php
    $positionClasses = match ($position) {
        'top' => 'bottom-full left-1/2 mb-2 -translate-x-1/2',
        'left' => 'right-full top-1/2 mr-2 -translate-y-1/2',
        'right' => 'left-full top-1/2 ml-2 -translate-y-1/2',
        default => 'left-1/2 top-full mt-2 -translate-x-1/2',
    };
@endphp

<span {{ $attributes->merge(['class' => 'group/tooltip relative inline-flex']) }}>
    {{ $slot }}

    <span role="tooltip" class="pointer-events-none absolute {{ $positionClasses }} z-50 whitespace-nowrap rounded-lg bg-[#0B5D1E] px-3 py-1.5 text-xs font-bold text-white opacity-0 shadow-lg transition duration-150 group-hover/tooltip:opacity-100 group-focus-within/tooltip:opacity-100">
        {{ $text }}
    </span>
</span>
