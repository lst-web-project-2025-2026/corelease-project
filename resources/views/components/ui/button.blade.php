@props([
    'variant' => 'primary',
    'type' => 'button',
    'href' => null,
])

@php
    $classes = [
        'btn',
        'btn-' . $variant
    ];
@endphp

@if($href)
    <a {{ $attributes->class($classes)->merge(['href' => $href]) }}>
        {{ $slot }}
    </a>
@else
    <button {{ $attributes->class($classes)->merge(['type' => $type]) }}>
        {{ $slot }}
    </button>
@endif
