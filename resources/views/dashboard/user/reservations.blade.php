@extends('layouts.dashboard')

@section('dashboard-title')
    <div style="margin-bottom: var(--space-2xl);">
        <h1 style="font-size: 2rem; margin-bottom: var(--space-xs);">My Reservations</h1>
        <p style="color: var(--text-secondary);">Track your resource usage and status updates.</p>
    </div>
@endsection

@section('dashboard-content')
    <x-ui.table>
        <x-slot name="thead">
            <th>Resource</th>
            <th class="text-center">Category</th>
            <th>Booking Period</th>
            <th class="text-center">Status</th>
            <th class="text-center">Actions</th>
        </x-slot>

        @forelse($reservations as $reservation)
            <tr>
                <td>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <strong>{{ $reservation->resource->name }}</strong>
                        <span class="text-muted">ID: #{{ $reservation->id }}</span>
                    </div>
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
                <td class="text-center">
                    <div style="display: inline-flex; gap: var(--space-xs);">
                        <!-- Cancellation Button -->
                        <form action="{{ $reservation->status === 'Pending' ? route('reservations.cancel', $reservation) : '#' }}" 
                              method="{{ $reservation->status === 'Pending' ? 'POST' : 'GET' }}"
                              @if($reservation->status === 'Pending') onsubmit="return confirm('Are you sure you want to cancel this reservation request?')" @endif>
                            @csrf
                            <button type="submit" 
                                    class="btn btn-sm btn-secondary" 
                                    title="Cancel Reservation"
                                    {{ $reservation->status !== 'Pending' ? 'disabled' : '' }}
                                    style="{{ $reservation->status !== 'Pending' ? 'opacity: 0.5; cursor: not-allowed;' : '' }}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </form>

                        <!-- Report Button -->
                        <button type="button" 
                                class="btn btn-sm btn-secondary" 
                                title="Report Issue"
                                {{ $reservation->status !== 'Active' ? 'disabled' : '' }}
                                @if($reservation->status === 'Active') onclick="openReportModal({{ $reservation->id }}, '{{ $reservation->resource->name }}')" @endif
                                style="{{ $reservation->status !== 'Active' ? 'opacity: 0.5; cursor: not-allowed;' : '' }}">
                                <i class="fas fa-exclamation-triangle"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: var(--space-2xl);">
                    <div class="empty-state">
                        <i class="fas fa-calendar-times" style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: var(--space-md); display: block;"></i>
                        <p style="color: var(--text-muted); margin-bottom: var(--space-md);">You don't have any reservations yet.</p>
                        <a href="{{ route('catalog.index') }}" class="btn btn-primary btn-sm">Browse Catalog</a>
                    </div>
                </td>
            </tr>
        @endforelse

        @if($reservations->hasPages())
            <x-slot name="tfoot">
                <td colspan="5" style="padding: var(--space-lg) var(--space-xl);">
                    <div class="catalog-pagination">
                        {{ $reservations->links('vendor.pagination.custom') }}
                    </div>
                </td>
            </x-slot>
        @endif
    </x-ui.table>

    <!-- Incident Report Modal -->
    <x-ui.modal id="report-modal" title="Report Resource Issue">
        <form id="report-form" action="" method="POST">
            @csrf
            <div style="margin-bottom: var(--space-md);">
                <p id="report-resource-name" style="font-weight: 600; color: var(--text-secondary); margin-bottom: var(--space-sm);"></p>
                <x-ui.textarea 
                    label="Description of the Issue" 
                    name="description" 
                    placeholder="Please provide details about the problem you are experiencing..." 
                    required 
                    rows="5"
                    value="{{ old('description') }}"
                    hide-error="true"
                />
                @error('description')
                    <div class="alert alert-error" style="margin-bottom: var(--space-md); font-size: 0.875rem; padding: var(--space-sm) var(--space-md);">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: var(--space-sm); margin-top: var(--space-xl);">
                <x-ui.button type="button" variant="secondary" onclick="toggleModal('report-modal', false)">Cancel</x-ui.button>
                <x-ui.button type="submit" variant="primary">Submit Report</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    <script>
        function openReportModal(reservationId, resourceName) {
            const form = document.getElementById('report-form');
            const nameEl = document.getElementById('report-resource-name');
            const modal = document.getElementById('report-modal');
            
            // Construct the dynamic action URL
            form.action = `/dashboard/reservations/${reservationId}/report`;
            nameEl.textContent = `Reporting issue for: ${resourceName}`;
            
            // Store for validation recovery
            sessionStorage.setItem('last_report_id', reservationId);
            sessionStorage.setItem('last_report_name', resourceName);

            toggleModal('report-modal', true);
        }

        // Handle validation errors - reopen modal
        @if($errors->has('description'))
            document.addEventListener('DOMContentLoaded', () => {
                const lastId = sessionStorage.getItem('last_report_id');
                const lastName = sessionStorage.getItem('last_report_name');
                if (lastId && lastName) {
                    openReportModal(lastId, lastName);
                }
            });
        @endif
    </script>
@endsection


