@props(['status' => 'default'])

@php
    $classes = "ui-status status-{$status}";
    if ($attributes->has('class')) {
        $classes .= ' ' . $attributes->get('class');
    }
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}></span>
