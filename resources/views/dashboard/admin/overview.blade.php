@extends('layouts.dashboard')

@section('dashboard-title')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-2xl);">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: var(--space-xs);">Admin Overview</h1>
            <p style="color: var(--text-secondary);">Management console for Corelease system resources and users.</p>
        </div>
        <div>
            <a href="{{ route('dashboard.admin.vetting') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-user-check"></i> Pending Vettings
            </a>
        </div>
    </div>
@endsection

@section('dashboard-content')
    <!-- Admin Stats Overview -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">Total Users</span>
            <span class="stat-value">{{ $totalUsers }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Resources Available</span>
            <span class="stat-value">{{ $totalResources }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">Pending Applications</span>
            <span class="stat-value">{{ $pendingApplications }}</span>
        </div>
        <div class="stat-card">
            <span class="stat-label">System Logs</span>
            <span class="stat-value">{{ $totalLogs }}</span>
        </div>
    </div>

    <!-- Recent Applications Section -->
    <div style="margin-top: var(--space-2xl);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-md);">
            <h2 style="font-size: 1.25rem; font-weight: 600;">Recent Applications</h2>
            <a href="{{ route('dashboard.admin.vetting') }}" class="btn btn-secondary btn-sm" style="font-size: 0.8125rem;">
                View Vetting Board <i class="fas fa-arrow-right" style="margin-left: 0.5rem; font-size: 0.75rem;"></i>
            </a>
        </div>

        <x-ui.table>
            <x-slot name="thead">
                <th>Applicant</th>
                <th>Profession</th>
                <th>Application Date</th>
                <th class="text-center">Status</th>
            </x-slot>

            @forelse($recentApplications as $application)
                <tr>
                    <td>
                        <div style="display: flex; flex-direction: column;">
                            <strong>{{ $application->name }}</strong>
                            <span class="text-muted">{{ $application->email }}</span>
                        </div>
                    </td>
                    <td>{{ $application->profession }}</td>
                    <td>
                        <span class="text-muted">{{ $application->created_at->format('M d, Y') }}</span>
                    </td>
                    <td class="text-center">
                        @php
                            $statusClass = match($application->status) {
                                'Pending' => 'warning',
                                'Approved' => 'success',
                                'Rejected' => 'error',
                                default => 'secondary'
                            };
                        @endphp
                        <x-ui.badge variant="{{ $statusClass }}">{{ $application->status }}</x-ui.badge>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align: center; padding: var(--space-2xl);">
                        <p style="color: var(--text-muted);">No recent applications.</p>
                    </td>
                </tr>
            @endforelse
        </x-ui.table>
    </div>
@endsection
