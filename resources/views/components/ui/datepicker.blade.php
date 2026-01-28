@props([
    'name',
    'id' => null,
    'value' => null,
    'label' => null,
    'required' => false,
    'min' => null,
    'max' => null,
    'disabled' => false,
    'containerClass' => '',
])

@php
    $id = $id ?? $name;
@endphp

<div class="form-group {{ $containerClass }}">
    @if($label)
        <label for="{{ $id }}" class="form-label">
            {{ $label }}
            @if($required) <span class="text-error">*</span> @endif
        </label>
    @endif
    
    <div class="datepicker-wrapper">
        <input 
            type="date" 
            name="{{ $name }}" 
            id="{{ $id }}"
            value="{{ old($name, $value) }}"
            class="form-input @error($name) is-invalid @enderror"
            @if($required) required @endif
            @if($min) min="{{ $min }}" @endif
            @if($max) max="{{ $max }}" @endif
            @if($disabled) disabled @endif
            {{ $attributes }}
        >
        <div class="datepicker-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
        </div>
    </div>

    @error($name)
        <span class="form-error">{{ $message }}</span>
    @enderror
</div>
