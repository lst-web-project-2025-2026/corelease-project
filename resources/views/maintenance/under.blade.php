@extends('layouts.app')

@section('title', 'System Under Maintenance')

@section('content')
<div class="container" style="min-height: 75vh; display: flex; align-items: center; justify-content: center; padding: var(--space-xl);">
    <div style="max-width: 540px; width: 100%; text-align: center; background: var(--bg-secondary); padding: var(--space-2xl); border-radius: 1.5rem; border: 1px solid var(--border-color); box-shadow: var(--glass-shadow);">
        <div style="width: 64px; height: 64px; background: rgba(var(--accent-h), var(--accent-s), var(--accent-l), 0.1); color: var(--accent-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto var(--space-lg);">
            <i class="fas fa-tools"></i>
        </div>
        
        <h1 style="font-size: 1.875rem; font-weight: 700; margin-bottom: var(--space-sm); color: var(--text-primary);">System Maintenance</h1>
        
        <p style="font-size: 1rem; color: var(--text-secondary); line-height: 1.6; margin-bottom: var(--space-2xl);">
            The facility is currently undergoing essential maintenance. Public access is temporarily suspended to ensure hardware integrity and data consistency.
        </p>

        <a href="/" class="btn btn-secondary" style="width: 100%;">
            Return to Homepage
        </a>
    </div>
</div>
@endsection
