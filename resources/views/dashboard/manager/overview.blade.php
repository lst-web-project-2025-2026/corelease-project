@extends('layouts.dashboard')

@section('dashboard-title')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-2xl);">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: var(--space-xs);">Manager Overview</h1>
            <p style="color: var(--text-secondary);">Operational hub for resource management and request moderation.</p>
        </div>
        <div>
            <a href="{{ route('dashboard.manager.approvals') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-check-circle"></i> Pending Requests
            </a>
        </div>
    </div>
@endsection

@section('dashboard-content')
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">Pending Requests</span>
            <span class="stat-value">{{ $pendingRequestsCount }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Total Resources</span>
            <span class="stat-value">{{ $totalResourcesCount }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Open Incidents</span>
            <span class="stat-value">{{ $openIncidentsCount }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Active Maintenances</span>
            <span class="stat-value">{{ $activeMaintenancesCount }}</span>
        </div>
    </div>

    <!-- Personal Reservations Section -->
    <div style="margin-top: var(--space-2xl);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-md);">
            <h2 style="font-size: 1.25rem; font-weight: 600;">My Recent Reservations</h2>
            <a href="{{ route('dashboard.reservations') }}" class="btn btn-secondary btn-sm" style="font-size: 0.8125rem;">
                View All <i class="fas fa-arrow-right" style="margin-left: 0.5rem; font-size: 0.75rem;"></i>
            </a>
        </div>

        <x-ui.table>
            <x-slot name="thead">
                <th>Resource</th>
                <th class="text-center">Category</th>
                <th>Booking Period</th>
                <th class="text-center">Status</th>
            </x-slot>

            @forelse($recentPersonalReservations as $reservation)
                <tr>
                    <td>
                        <strong>{{ $reservation->resource->name }}</strong>
                    </td>
                    <td class="text-center">
                        <x-ui.badge variant="secondary">{{ $reservation->resource->category->name }}</x-ui.badge>
                    </td>
                    <td>
                        <div style="display: flex; flex-direction: column; font-size: 0.875rem;">
                            <span>{{ $reservation->start_date->format('M d, Y') }}</span>
                            <span class="text-muted">to {{ $reservation->end_date->format('M d, Y') }}</span>
                        </div>
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
                        <p style="color: var(--text-muted);">You don't have any personal reservations yet.</p>
                    </td>
                </tr>
            @endforelse
        </x-ui.table>
    </div>
@endsection
