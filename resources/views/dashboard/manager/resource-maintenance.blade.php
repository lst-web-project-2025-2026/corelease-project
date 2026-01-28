@extends('layouts.dashboard')

@section('dashboard-title')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-2xl);">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: var(--space-xs);">Schedule Maintenance</h1>
            <p style="color: var(--text-secondary);">Register a downtime window for <strong>{{ $resource->name }}</strong>.</p>
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
            max-width: 650px; 
            margin: 0 auto; 
            background: var(--bg-secondary); 
            border-radius: 1.5rem; 
            border: 1px solid var(--border-color);
            padding: var(--space-xl);
        }
        .form-input, .form-select, .form-textarea {
            padding: 0.625rem 1rem;
            min-height: 44px;
        }
    </style>

    <div class="form-card">
        <form action="{{ route('dashboard.manager.inventory.maintenance.store', $resource) }}" method="POST">
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

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-lg); margin-bottom: var(--space-lg);">
                <x-ui.datepicker 
                    label="Start Date" 
                    name="start_date" 
                    required 
                    value="{{ old('start_date', now()->format('Y-m-d')) }}"
                />

                <x-ui.datepicker 
                    label="End Date" 
                    name="end_date" 
                    required 
                    value="{{ old('end_date', now()->addDay()->format('Y-m-d')) }}"
                />
            </div>

            <div style="margin-bottom: var(--space-xl);">
                <div class="form-group">
                    <label class="form-label" style="display: flex; justify-content: space-between;">
                        Work Description <span class="text-muted" style="font-weight: 400; font-size: 0.75rem;">Minimum 10 characters</span>
                    </label>
                    <textarea 
                        name="description" 
                        class="form-textarea @error('description') is-invalid @enderror" 
                        placeholder="Explain the technical work being performed (e.g., Firmware upgrade, hardware replacement)..." 
                        required 
                        style="min-height: 120px; width: 100%; border-radius: 0.75rem;"
                    >{{ old('description') }}</textarea>
                </div>
                
                <div style="background: rgba(var(--accent-primary-rgb), 0.05); border: 1px solid var(--accent-glow); padding: var(--space-md); border-radius: 0.75rem; margin-top: var(--space-sm); display: flex; gap: var(--space-sm); align-items: flex-start;">
                    <i class="fas fa-info-circle" style="color: var(--accent-primary); margin-top: 2px;"></i>
                    <p style="font-size: 0.75rem; color: var(--text-secondary); margin: 0; line-height: 1.4;">
                        Provide a detailed technical justification. This description will be visible in the system audit logs and resource history.
                    </p>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: var(--space-sm); border-top: 1px solid var(--border-color); padding-top: var(--space-xl);">
                <a href="{{ route('dashboard.manager.inventory') }}" class="btn btn-secondary">Cancel</a>
                <x-ui.button type="submit" variant="primary">Schedule Downtime</x-ui.button>
            </div>
        </form>
    </div>
@endsection
