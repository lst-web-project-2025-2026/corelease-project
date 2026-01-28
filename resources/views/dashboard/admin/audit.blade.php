@extends('layouts.dashboard')

@section('dashboard-title')
    <div style="margin-bottom: var(--space-2xl);">
        <h1 style="font-size: 2rem; margin-bottom: var(--space-xs);">Audit Logs</h1>
        <p style="color: var(--text-secondary);">System-wide activity trail and security logs.</p>
    </div>
@endsection

@section('dashboard-content')
    <x-ui.table>
        <x-slot name="thead">
            <th>User</th>
            <th class="text-center">Event</th>
            <th>Resource</th>
            <th class="text-center">Date</th>
        </x-slot>

        @forelse($logs as $log)
            <tr>
                <td>
                    <div style="display: flex; flex-direction: column;">
                        <strong>{{ $log->user->name ?? 'System' }}</strong>
                        <span class="text-muted">{{ $log->user->role ?? 'N/A' }}</span>
                    </div>
                </td>
                <td class="text-center">
                    <x-ui.badge variant="secondary">{{ $log->event }}</x-ui.badge>
                </td>

                <td>
                    <div style="display: flex; flex-direction: column; font-size: 0.8125rem;">
                        <span style="font-family: monospace;">{{ class_basename($log->auditable_type) }}</span>
                        <span class="text-muted">ID: #{{ $log->auditable_id }}</span>
                    </div>
                </td>
                <td class="text-center">
                    <span class="text-muted" style="font-size: 0.8125rem;">{{ $log->created_at->format('M d, Y H:i') }}</span>
                </td>
            </tr>

        @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: var(--space-2xl);">
                    <p style="color: var(--text-muted);">No logs found.</p>
                </td>
            </tr>
        @endforelse

        @if($logs->hasPages())
            <x-slot name="tfoot">
                <td colspan="5" style="padding: var(--space-lg) var(--space-xl);">
                    <div class="catalog-pagination">
                        {{ $logs->links('vendor.pagination.custom') }}
                    </div>
                </td>
            </x-slot>
        @endif
    </x-ui.table>
@endsection
