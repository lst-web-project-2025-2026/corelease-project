@extends('layouts.app')

@section('title', 'Maintenance Schedule')

@section('styles')
    @vite(['resources/css/pages/maintenance.css'])
@endsection

@section('content')
<div class="maintenance-page container">
    <header class="maintenance-header animate-fade-in">
        <h1 class="page-title">Maintenance Schedule</h1>
        <p class="page-subtitle text-secondary">Stay informed about scheduled downtime, ongoing updates, and recently completed hardware maintenance.</p>
    </header>

    <!-- Ongoing Maintenance -->
    <section class="maintenance-section animate-fade-in">
        <h2 class="section-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"></path></svg>
            Live / Ongoing Maintenance
        </h2>
        <div class="maintenance-grid">
            @forelse($currentMaintenance as $maintenance)
                <x-ui.card class="maintenance-card">
                    <div class="card-header">
                        <x-ui.badge variant="warning">{{ $maintenance->status }}</x-ui.badge>
                        <x-ui.status status="warning" />
                    </div>
                    <div class="maintenance-info">
                        <h3 class="resource-name">{{ $maintenance->resource->name }}</h3>
                        <span class="category-name">{{ $maintenance->resource->category->name }}</span>
                    </div>
                    <div class="maintenance-details">
                        <div class="detail-item">
                            <span class="detail-label">Started:</span>
                            <span class="detail-value">{{ $maintenance->start_date->format('M d, Y') }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Est. End:</span>
                            <span class="detail-value">{{ $maintenance->end_date->format('M d, Y') }}</span>
                        </div>
                        <div class="maintenance-description">
                            {{ $maintenance->description }}
                        </div>
                    </div>
                </x-ui.card>
            @empty
                <div class="empty-state">
                    <p>No ongoing maintenance activities at this time.</p>
                </div>
            @endforelse
        </div>
    </section>

    <!-- Upcoming Maintenance -->
    <section class="maintenance-section animate-fade-in" style="animation-delay: 0.1s;">
        <h2 class="section-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            Upcoming Maintenance
        </h2>
        <div class="maintenance-grid">
            @forelse($futureMaintenance as $maintenance)
                <x-ui.card class="maintenance-card">
                    <div class="card-header">
                        <x-ui.badge variant="info">Scheduled</x-ui.badge>
                        <x-ui.status status="info" />
                    </div>
                    <div class="maintenance-info">
                        <h3 class="resource-name">{{ $maintenance->resource->name }}</h3>
                        <span class="category-name">{{ $maintenance->resource->category->name }}</span>
                    </div>
                    <div class="maintenance-details">
                        <div class="detail-item">
                            <span class="detail-label">Start Date:</span>
                            <span class="detail-value">{{ $maintenance->start_date->format('M d, Y') }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">End Date:</span>
                            <span class="detail-value">{{ $maintenance->end_date->format('M d, Y') }}</span>
                        </div>
                        <div class="maintenance-description">
                            {{ $maintenance->description }}
                        </div>
                    </div>
                </x-ui.card>
            @empty
                <div class="empty-state">
                    <p>No upcoming maintenance scheduled for the near future.</p>
                </div>
            @endforelse
        </div>
    </section>

    <!-- Recently Completed -->
    <section class="maintenance-section animate-fade-in" style="animation-delay: 0.2s;">
        <h2 class="section-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            Recently Completed
        </h2>
        <div class="maintenance-grid">
            @forelse($pastMaintenance as $maintenance)
                <x-ui.card class="maintenance-card" style="opacity: 0.8;">
                    <div class="card-header">
                        <x-ui.badge variant="success">Completed</x-ui.badge>
                        <x-ui.status status="online" />
                    </div>
                    <div class="maintenance-info">
                        <h3 class="resource-name">{{ $maintenance->resource->name }}</h3>
                        <span class="category-name">{{ $maintenance->resource->category->name }}</span>
                    </div>
                    <div class="maintenance-details">
                        <div class="detail-item">
                            <span class="detail-label">Finished:</span>
                            <span class="detail-value">{{ $maintenance->end_date->format('M d, Y') }}</span>
                        </div>
                        <div class="maintenance-description">
                            {{ $maintenance->description }}
                        </div>
                    </div>
                </x-ui.card>
            @empty
                <div class="empty-state">
                    <p>No recent maintenance history available.</p>
                </div>
            @endforelse
        </div>
    </section>
</div>
@endsection
