@extends('layouts.dashboard')

@section('dashboard-title')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-2xl);">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: var(--space-xs);">Maintenance Schedule</h1>
            <p style="color: var(--text-secondary);">Track and manage downtime for your assigned resources.</p>
        </div>
        <div>
            <a href="{{ route('maintenance.schedule') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-calendar-alt" style="margin-right: 0.5rem;"></i> View Full Schedule
            </a>
        </div>
    </div>
@endsection

@section('dashboard-content')
    <style>
    .empty-state { text-align: center; }
    </style>

    <x-ui.table>
        <x-slot name="thead">
            <th>Resource</th>
            <th class="text-center">Type</th>
            <th>Period</th>
            <th class="text-center">Status</th>
        </x-slot>

        @forelse($maintenances as $maintenance)
            <tr>
                <td>
                    <strong>{{ $maintenance->resource->name }}</strong>
                </td>
                <td class="text-center">
                    <x-ui.badge variant="secondary">{{ $maintenance->resource->category->name }}</x-ui.badge>
                </td>
                <td>
                    <div style="display: flex; flex-direction: column; font-size: 0.875rem;">
                        <span>{{ $maintenance->start_date->format('M d, Y') }}</span>
                        <span class="text-muted">to {{ $maintenance->end_date->format('M d, Y') }}</span>
                    </div>
                </td>
                <td class="text-center">
                    @php
                        $now = now();
                        if ($maintenance->end_date < $now) {
                            $status = 'Completed';
                            $variant = 'success';
                        } elseif ($maintenance->start_date <= $now && $maintenance->end_date >= $now) {
                            $status = 'In Progress';
                            $variant = 'warning';
                        } else {
                            $status = 'Scheduled';
                            $variant = 'info';
                        }
                    @endphp
                    <x-ui.badge variant="{{ $variant }}">{{ $status }}</x-ui.badge>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" style="text-align: center; padding: var(--space-2xl);">
                    <div class="empty-state">
                        <i class="fas fa-tools" style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: var(--space-md); display: block;"></i>
                        <p style="color: var(--text-muted);">No scheduled maintenance found.</p>
                    </div>
                </td>
            </tr>
        @endforelse

        @if($maintenances->hasPages())
            <x-slot name="tfoot">
                <td colspan="4" style="padding: var(--space-lg) var(--space-xl);">
                    <div class="catalog-pagination">
                        {{ $maintenances->links('vendor.pagination.custom') }}
                    </div>
                </td>
            </x-slot>
        @endif
    </x-ui.table>
@endsection
