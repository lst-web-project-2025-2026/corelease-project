@props(['label' => null, 'name' => null, 'placeholder' => '', 'required' => false, 'hideError' => false])

@php
    $error = $name ? $errors->first($name) : null;
    $finalValue = $name ? old($name, $slot) : $slot;
@endphp

<div class="form-group {{ $attributes->get('group-class') }}">
    @if($label)
        <label for="{{ $name }}" class="form-label">{{ $label }} @if($required)<span class="text-error">*</span>@endif</label>
    @endif
    
    <textarea 
        id="{{ $attributes->get('id', $name) }}" 
        name="{{ $name }}" 
        class="form-textarea @if($error) is-invalid @endif {{ $attributes->get('class') }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->except(['label', 'name', 'placeholder', 'required', 'group-class', 'class', 'id']) }}
    >{{ $finalValue }}</textarea>

    @if($error && !$hideError)
        <span class="text-error" style="font-size: 0.8125rem; margin-top: 0.25rem; display: block;">{{ $error }}</span>
    @endif
</div>
