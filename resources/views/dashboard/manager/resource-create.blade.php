@extends('layouts.dashboard')

@section('dashboard-title')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-2xl);">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: var(--space-xs);">Add New Resource</h1>
            <p style="color: var(--text-secondary);">Onboard a new technical resource into the data center inventory.</p>
        </div>
        <div>
            <a href="{{ route('dashboard.manager.inventory') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left" style="margin-right: 0.5rem;"></i> Back to Inventory
            </a>
        </div>
    </div>
@endsection

@section('dashboard-content')
    <style>
        .form-card { 
            max-width: 850px; 
            margin: 0 auto; 
            background: var(--bg-secondary); 
            border-radius: 1.25rem; 
            border: 1px solid var(--border-color);
            padding: var(--space-xl);
        }
        .spec-grid { 
            display: grid; 
            grid-template-columns: 1fr 1fr; 
            gap: var(--space-md) var(--space-lg); 
        }
        .special-spec {
            background: rgba(var(--accent-primary-rgb), 0.05);
            border: 1px dashed var(--accent-primary);
            padding: var(--space-md);
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: space-between;
            grid-column: span 2;
            margin-bottom: var(--space-md);
        }
        /* Override form heights to be more compact as per user request */
        .form-input, .form-select {
            padding: 0.5rem 0.875rem;
            min-height: 40px;
        }
    </style>

    <div class="form-card">
        <form action="{{ route('dashboard.manager.inventory.store') }}" method="POST" id="resource-form">
            @csrf
            
            @if($errors->any())
                <div class="alert alert-error" style="margin-bottom: var(--space-xl); border-radius: 1rem; padding: var(--space-md) var(--space-lg);">
                    <div style="display: flex; gap: var(--space-sm); align-items: center; margin-bottom: var(--space-xs);">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <ul style="margin-left: 1.5rem; font-size: 0.875rem; list-style-type: none; padding: 0;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                <style>.text-error, .form-error { display: none !important; }</style>
            @endif
                <x-ui.input 
                    label="Resource Name" 
                    name="name" 
                    placeholder="e.g. Dell PowerEdge R750" 
                    required 
                    value="{{ old('name') }}"
                />

                <x-ui.select 
                    label="Category" 
                    name="category_id" 
                    id="category_id" 
                    required
                >
                    <option value="">Select a category...</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }} data-specs='@json($category->specs)'>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </x-ui.select>
            </div>

            <div id="specs-container" style="border-top: 1px solid var(--border-color); padding-top: var(--space-xl); display: none;">
                <h3 style="font-size: 1rem; font-weight: 700; margin-bottom: var(--space-lg); color: var(--text-primary); text-transform: uppercase; letter-spacing: 0.05em;">
                    Technical Specifications
                </h3>

                <div id="specs-fields" class="spec-grid">
                    <!-- Dynamically filled via JS -->
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: var(--space-sm); margin-top: var(--space-2xl); border-top: 1px solid var(--border-color); padding-top: var(--space-xl);">
                <a href="{{ route('dashboard.manager.inventory') }}" class="btn btn-secondary">Cancel</a>
                <x-ui.button type="submit" variant="primary">Create Resource</x-ui.button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const categorySelect = document.getElementById('category_id');
            const specsContainer = document.getElementById('specs-container');
            const specsFields = document.getElementById('specs-fields');

            // List of specs that should be handled as "Features" (Boolean/Fixed)
            const featureSpecs = ['allow_os'];

            function updateSpecs() {
                const selectedOption = categorySelect.options[categorySelect.selectedIndex];
                const specsJson = selectedOption.getAttribute('data-specs');
                
                specsFields.innerHTML = '';
                
                if (specsJson) {
                    const specs = JSON.parse(specsJson);
                    specsContainer.style.display = 'block';
                    
                    specs.forEach(spec => {
                        if (featureSpecs.includes(spec)) {
                            // Special handling for features like allow_os
                            const div = document.createElement('div');
                            div.className = 'special-spec';
                            
                            let labelText = spec.replace(/_/g, ' ').toUpperCase();
                            if (spec === 'allow_os') labelText = 'Enable Operating System Selection';

                            div.innerHTML = `
                                <div>
                                    <span style="font-weight: 600; font-size: 0.875rem; color: var(--text-primary);">${labelText}</span>
                                    <p style="font-size: 0.75rem; color: var(--text-muted); margin: 0;">This feature is mandatory for this category.</p>
                                </div>
                                <div style="color: var(--success); font-weight: 700; font-size: 0.875rem;">
                                    <i class="fas fa-check-circle"></i> ACTIVE
                                </div>
                                <input type="hidden" name="specs[${spec}]" value="true">
                            `;
                            specsFields.appendChild(div);
                        } else {
                            // Standard text input
                            const div = document.createElement('div');
                            div.className = 'form-group';
                            
                            const label = document.createElement('label');
                            label.className = 'form-label';
                            label.textContent = spec;
                            
                            const input = document.createElement('input');
                            input.type = 'text';
                            input.name = `specs[${spec}]`;
                            input.className = 'form-input';
                            input.required = true;
                            input.placeholder = `Enter ${spec}`;
                            
                            div.appendChild(label);
                            div.appendChild(input);
                            specsFields.appendChild(div);
                        }
                    });
                } else {
                    specsContainer.style.display = 'none';
                }
            }

            categorySelect.addEventListener('change', updateSpecs);

            // If old input exists, trigger update
            if (categorySelect.value) {
                updateSpecs();
                
                // Restore values if validation failed
                @if(old('specs'))
                    const oldSpecs = @json(old('specs'));
                    Object.entries(oldSpecs).forEach(([key, value]) => {
                        const input = document.querySelector(`input[name="specs[${key}]"]:not([type="hidden"])`);
                        if (input) input.value = value;
                    });
                @endif
            }
        });
    </script>
@endsection
