@props(['label' => null, 'name' => null, 'required' => false])

@php
    $error = $name ? $errors->first($name) : null;
@endphp

<div class="form-group {{ $attributes->get('group-class') }}">
    @if($label)
        <label for="{{ $attributes->get('id', $name) }}" class="form-label">{{ $label }} @if($required)<span class="text-error">*</span>@endif</label>
    @endif
    
    <select 
        id="{{ $attributes->get('id', $name) }}" 
        name="{{ $name }}" 
        class="form-select @if($error) is-invalid @endif {{ $attributes->get('class') }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->except(['label', 'name', 'required', 'group-class', 'class', 'id']) }}
    >
        {{ $slot }}
    </select>

    @if($error)
        <span class="text-error" style="font-size: 0.8125rem; margin-top: 0.25rem; display: block;">{{ $error }}</span>
    @endif
</div>
