@extends('layouts.dashboard')

@section('dashboard-title')
    <h1>{{ $title ?? 'Page Placeholder' }}</h1>
@endsection

@section('dashboard-content')
    <div class="card">
        <div class="card-body" style="padding: var(--space-2xl); text-align: center;">
            <i class="fas fa-hammer" style="font-size: 3rem; color: var(--text-muted); margin-bottom: var(--space-md);"></i>
            <h3>Under Construction</h3>
            <p style="color: var(--text-secondary);">The <strong>{{ $title ?? 'this page' }}</strong> functionality is currently being developed.</p>
            <div style="margin-top: var(--space-xl);">
                <a href="{{ route('dashboard') }}" class="btn btn-primary">Return to Overview</a>
            </div>
        </div>
    </div>
@endsection
