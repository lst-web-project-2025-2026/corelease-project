@props(['variant' => 'primary'])

@php
    $classes = "ui-badge badge-{$variant}";
    if ($attributes->has('class')) {
        $classes .= ' ' . $attributes->get('class');
    }
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
