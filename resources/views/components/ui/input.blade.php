@props(['label' => null, 'name' => null, 'type' => 'text', 'placeholder' => '', 'value' => '', 'required' => false])

@php
    $error = $name ? $errors->first($name) : null;
    $finalValue = $name ? old($name, $value) : $value;
@endphp

<div class="form-group {{ $attributes->get('group-class') }}">
    @if($label)
        <label for="{{ $name }}" class="form-label">{{ $label }} @if($required)<span class="text-error">*</span>@endif</label>
    @endif
    
    <input 
        type="{{ $type }}" 
        id="{{ $attributes->get('id', $name) }}" 
        name="{{ $name }}" 
        class="form-input @if($error) is-invalid @endif {{ $attributes->get('class') }}"
        placeholder="{{ $placeholder }}"
        value="{{ $finalValue }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->except(['label', 'name', 'type', 'placeholder', 'value', 'required', 'group-class', 'class', 'id']) }}
    >

    @if($error)
        <span class="text-error" style="font-size: 0.8125rem; margin-top: 0.25rem; display: block;">{{ $error }}</span>
    @php endif @endphp
</div>
