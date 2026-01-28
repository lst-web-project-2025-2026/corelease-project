@extends('layouts.dashboard')

@section('dashboard-title')
    <div style="margin-bottom: var(--space-2xl);">
        <h1 style="font-size: 2rem; margin-bottom: var(--space-xs);">User Management</h1>
        <p style="color: var(--text-secondary);">Review and manage system users and applications.</p>
    </div>
@endsection

@section('dashboard-content')
    @include('dashboard.admin.partials._tabs')

    <x-ui.table>
        <x-slot name="thead">
            <th>Applicant</th>
            <th>Profession</th>
            <th>Modification Date</th>
            <th class="text-center">Status</th>
            <th class="text-center">Actions</th>
        </x-slot>

        @forelse($applications as $application)
            <tr>
                <td>
                    <div style="display: flex; flex-direction: column;">
                        <strong>{{ $application->name }}</strong>
                        <span class="text-muted">{{ $application->email }}</span>
                    </div>
                </td>
                <td>{{ $application->profession }}</td>
                <td>
                    <span class="text-muted">{{ $application->updated_at->format('M d, Y H:i:s') }}</span>
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
                <td class="text-center">
                    <div style="display: inline-flex; gap: var(--space-xs);">
                        <button type="button" 
                                class="btn btn-sm btn-secondary" 
                                title="Review History"
                                {{ $application->status === 'Pending' ? 'disabled' : '' }}
                                @if($application->status !== 'Pending') 
                                    onclick="openHistoryModal({
                                        id: {{ $application->id }},
                                        name: '{{ addslashes($application->name) }}',
                                        profession: '{{ addslashes($application->profession) }}',
                                        submitted: '{{ $application->created_at->format('M d, Y H:i:s') }}',
                                        decided: '{{ $application->updated_at->format('M d, Y H:i:s') }}',
                                        userJustification: '{{ addslashes($application->user_justification) }}',
                                        adminJustification: '{{ addslashes($application->admin_justification) }}',
                                        status: '{{ $application->status }}'
                                    })"
                                @endif
                                style="{{ $application->status === 'Pending' ? 'opacity: 0.5; cursor: not-allowed;' : '' }}">
                            <i class="fas fa-history"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: var(--space-2xl);">
                    <p style="color: var(--text-muted);">No applications found.</p>
                </td>
            </tr>
        @endforelse

        @if($applications->hasPages())
            <x-slot name="tfoot">
                <td colspan="5" style="padding: var(--space-lg) var(--space-xl);">
                    <div class="catalog-pagination">
                        {{ $applications->links('vendor.pagination.custom') }}
                    </div>
                </td>
            </x-slot>
        @endif
    </x-ui.table>

    <!-- History Review Modal -->
    <x-ui.modal id="history-modal" title="Vetting Details">
        <div style="display: flex; flex-direction: column; gap: var(--space-xl);">
            <!-- Status Ribbon -->
            <div id="history-status-ribbon" style="padding: var(--space-sm) var(--space-md); border-radius: 0.5rem; text-align: center; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.8125rem;">
            </div>

            <!-- Metadata Grid -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-lg);">
                <div style="padding: var(--space-md); background: var(--bg-tertiary); border-radius: 0.75rem; border: 1px solid var(--border-color);">
                    <p style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); margin-bottom: 4px;">Submitted On</p>
                    <p id="history-submitted-date" style="font-size: 0.9375rem; color: var(--text-primary);"></p>
                </div>
                <div style="padding: var(--space-md); background: var(--bg-tertiary); border-radius: 0.75rem; border: 1px solid var(--border-color);">
                    <p id="history-decision-label" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); margin-bottom: 4px;">Decision Date</p>
                    <p id="history-decision-date" style="font-size: 0.9375rem; color: var(--text-primary);"></p>
                </div>
            </div>

            <!-- Justifications -->
            <div style="display: flex; flex-direction: column; gap: var(--space-md);">
                <div>
                    <p style="font-size: 0.8125rem; font-weight: 600; color: var(--text-secondary); margin-bottom: var(--space-xs); text-transform: uppercase; letter-spacing: 0.05em;">User Justification</p>
                    <div style="padding: var(--space-md); background: var(--bg-tertiary); border-radius: 0.75rem; border: 1px solid var(--border-color); color: var(--text-primary); line-height: 1.5; white-space: pre-wrap;" id="history-user-justification"></div>
                </div>

                <div>
                    <p style="font-size: 0.8125rem; font-weight: 600; color: var(--text-secondary); margin-bottom: var(--space-xs); text-transform: uppercase; letter-spacing: 0.05em;">Admin Decision Feedback</p>
                    <div style="padding: var(--space-md); background: var(--bg-tertiary); border-radius: 0.75rem; border: 1px solid var(--border-color); color: var(--text-primary); line-height: 1.5; white-space: pre-wrap;" id="history-admin-justification"></div>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; margin-top: var(--space-lg);">
                <x-ui.button type="button" variant="secondary" onclick="toggleModal('history-modal', false)">Close</x-ui.button>
            </div>
        </div>
    </x-ui.modal>

    <script>
        function openHistoryModal(data) {
            document.getElementById('history-submitted-date').textContent = data.submitted;
            document.getElementById('history-decision-date').textContent = data.decided;
            document.getElementById('history-user-justification').textContent = data.userJustification;
            document.getElementById('history-admin-justification').textContent = data.adminJustification;
            
            const ribbon = document.getElementById('history-status-ribbon');
            ribbon.textContent = `Status: ${data.status}`;
            
            if (data.status === 'Approved') {
                ribbon.style.background = 'rgba(40, 167, 69, 0.1)';
                ribbon.style.color = '#28a745';
                ribbon.style.border = '1px solid rgba(40, 167, 69, 0.2)';
            } else if (data.status === 'Rejected') {
                ribbon.style.background = 'rgba(220, 53, 69, 0.1)';
                ribbon.style.color = '#dc3545';
                ribbon.style.border = '1px solid rgba(220, 53, 69, 0.2)';
            } else {
                ribbon.style.background = 'var(--bg-tertiary)';
                ribbon.style.color = 'var(--text-secondary)';
                ribbon.style.border = '1px solid var(--border-color)';
            }

            const modal = document.getElementById('history-modal');
            modal.querySelector('h3').textContent = `Reviewing: ${data.name}`;
            
            toggleModal('history-modal', true);
        }
    </script>
@endsection

