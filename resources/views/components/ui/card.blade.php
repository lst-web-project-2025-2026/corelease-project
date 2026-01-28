@props([
    'padding' => 'md',
])

@php
    $paddingClass = "p-{$padding}";
@endphp

<div {{ $attributes->merge(['class' => "card $paddingClass"]) }}>
    {{ $slot }}
</div>
