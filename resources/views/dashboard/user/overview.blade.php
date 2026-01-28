@extends('layouts.dashboard')

@section('dashboard-title')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-2xl);">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: var(--space-xs);">Overview</h1>
            <p style="color: var(--text-secondary);">Welcome back, {{ $user->name }}. Here is a summary of your activity.</p>
        </div>
        <div>
            <a href="{{ route('catalog.index') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> New Reservation
            </a>
        </div>
    </div>
@endsection

@section('dashboard-content')
    <!-- Stats Overview -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">Active Reservations</span>
            <span class="stat-value">{{ $activeReservationsCount }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Pending Requests</span>
            <span class="stat-value">{{ $pendingReservationsCount }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Account Role</span>
            <span class="stat-value">{{ $user->role }}</span>
        </div>
    </div>


    <!-- Recent Activity Section -->
    <div style="margin-top: var(--space-2xl);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-md);">
            <h2 style="font-size: 1.25rem; font-weight: 600;">Recent Reservations</h2>
            <a href="{{ route('dashboard.reservations') }}" class="btn btn-secondary btn-sm" style="font-size: 0.8125rem;">
                View All Activity <i class="fas fa-arrow-right" style="margin-left: 0.5rem; font-size: 0.75rem;"></i>
            </a>
        </div>

        <x-ui.table>
            <x-slot name="thead">
                <th>Resource</th>
                <th class="text-center">Category</th>
                <th>Request Date</th>
                <th class="text-center">Status</th>
            </x-slot>

            @forelse($recentReservations as $reservation)
                <tr>
                    <td>
                        <strong>{{ $reservation->resource->name }}</strong>
                    </td>
                    <td class="text-center">
                        <x-ui.badge variant="secondary">{{ $reservation->resource->category->name }}</x-ui.badge>
                    </td>
                    <td>
                        <span class="text-muted">{{ $reservation->created_at->format('M d, Y') }}</span>
                    </td>
                    <td class="text-center">
                        @php
                            $statusClass = match($reservation->status) {
                                'Pending' => 'warning',
                                'Approved' => 'success',
                                'Rejected' => 'error',
                                'Active' => 'primary',
                                'Completed' => 'info',
                                'Expired' => 'secondary',
                                'Cancelled' => 'error',
                                default => 'secondary'
                            };
                        @endphp
                        <x-ui.badge variant="{{ $statusClass }}">{{ $reservation->status }}</x-ui.badge>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: var(--space-2xl);">
                        <p style="color: var(--text-muted);">No recent activity.</p>
                        <a href="{{ route('catalog.index') }}" style="color: var(--accent-primary); font-size: 0.875rem;">Browse the catalog &rarr;</a>
                    </td>
                </tr>
            @endforelse
        </x-ui.table>
    </div>
@endsection
