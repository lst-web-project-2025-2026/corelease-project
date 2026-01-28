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
            <th>Submission Date</th>
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
                    <span class="text-muted">{{ $application->created_at->format('M d, Y H:i:s') }}</span>
                </td>
                <td class="text-center">
                    <x-ui.badge variant="warning">{{ $application->status }}</x-ui.badge>
                </td>
                <td class="text-center">
                    <div style="display: inline-flex; gap: var(--space-xs);">
                        <button type="button" 
                                class="btn btn-sm btn-secondary" 
                                title="Review Application"
                                onclick="openVettingModal({{ $application->id }}, '{{ addslashes($application->name) }}', '{{ addslashes($application->user_justification) }}')">
                            <i class="fas fa-file-alt"></i>
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: var(--space-2xl);">
                    <div class="empty-state">
                        <i class="fas fa-user-shield" style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: var(--space-md); display: block;"></i>
                        <p style="color: var(--text-muted);">No pending vettings at the moment.</p>
                    </div>
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

    <!-- Vetting Review Modal -->
    <x-ui.modal id="vetting-modal" title="Review Access Application">
        <form id="vetting-form" action="" method="POST">
            @csrf
            
            <div style="margin-bottom: var(--space-xl); padding: var(--space-md); background: var(--bg-tertiary); border-radius: 0.75rem; border: 1px solid var(--border-color);">
                <p style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); margin-bottom: 4px;">User Justification</p>
                <p id="vetting-user-justification" style="font-size: 0.9375rem; color: var(--text-primary); line-height: 1.5; white-space: pre-wrap;"></p>
            </div>

            <div style="margin-bottom: var(--space-lg);">
                <p style="font-size: 0.8125rem; font-weight: 600; color: var(--text-secondary); margin-bottom: var(--space-xs); text-transform: uppercase; letter-spacing: 0.05em;">Decision</p>
                <div style="display: flex; gap: var(--space-md);">
                    <label style="flex: 1; cursor: pointer;">
                        <input type="radio" name="status" value="Approved" checked style="display: none;">
                        <div class="btn btn-outline-success" style="width: 100%; text-align: center; display: block;" id="btn-approve" onclick="selectEdict('Approved')">
                            <i class="fas fa-check-circle"></i> Approve
                        </div>
                    </label>
                    <label style="flex: 1; cursor: pointer;">
                        <input type="radio" name="status" value="Rejected" style="display: none;">
                        <div class="btn btn-outline-error" style="width: 100%; text-align: center; display: block;" id="btn-reject" onclick="selectEdict('Rejected')">
                            <i class="fas fa-times-circle"></i> Reject
                        </div>
                    </label>
                </div>
            </div>

            <div style="margin-bottom: var(--space-md);">
                <x-ui.textarea 
                    label="Admin Justification / Feedback" 
                    name="admin_justification" 
                    placeholder="Provide a reason for your decision. This will be visible to the user." 
                    required 
                    rows="4"
                    value="{{ old('admin_justification') }}"
                    hide-error="true"
                />
                @error('admin_justification')
                    <div class="alert alert-error" style="margin-bottom: var(--space-md); font-size: 0.875rem; padding: var(--space-sm) var(--space-md);">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $message }}
                    </div>
                @enderror
            </div>
            
            <div style="display: flex; justify-content: flex-end; gap: var(--space-sm); margin-top: var(--space-xl);">
                <x-ui.button type="button" variant="secondary" onclick="toggleModal('vetting-modal', false)">Close</x-ui.button>
                <x-ui.button type="submit" id="submit-btn" variant="primary">Process Application</x-ui.button>
            </div>
        </form>
    </x-ui.modal>

    <script>
        function openVettingModal(applicationId, applicantName, userJustification) {
            const form = document.getElementById('vetting-form');
            const justificationEl = document.getElementById('vetting-user-justification');
            const modal = document.getElementById('vetting-modal');
            
            form.action = `/dashboard/admin/vetting/${applicationId}/process`;
            justificationEl.textContent = userJustification;
            
            // Set title with applicant name
            modal.querySelector('h3').textContent = `Reviewing: ${applicantName}`;
            
            // Store for validation recovery
            sessionStorage.setItem('last_vetting_id', applicationId);
            sessionStorage.setItem('last_vetting_name', applicantName);
            sessionStorage.setItem('last_vetting_justification', userJustification);

            toggleModal('vetting-modal', true);
            selectEdict('Approved'); // Default
        }

        function selectEdict(status) {
            const approveBtn = document.getElementById('btn-approve');
            const rejectBtn = document.getElementById('btn-reject');
            const submitBtn = document.getElementById('submit-btn');
            const radios = document.getElementsByName('status');
            
            radios.forEach(r => r.checked = (r.value === status));
            
            if (status === 'Approved') {
                // Set Approve button to filled
                approveBtn.classList.remove('btn-outline-success');
                approveBtn.classList.add('btn-success');
                
                // Set Reject button to outlined
                rejectBtn.classList.remove('btn-error');
                rejectBtn.classList.add('btn-outline-error');
                
                submitBtn.className = 'btn btn-primary';
                submitBtn.innerHTML = '<i class="fas fa-check"></i> Complete Approval';
            } else {
                // Set Approve button to outlined
                approveBtn.classList.remove('btn-success');
                approveBtn.classList.add('btn-outline-success');
                
                // Set Reject button to filled
                rejectBtn.classList.remove('btn-outline-error');
                rejectBtn.classList.add('btn-error');
                
                submitBtn.className = 'btn btn-error';
                submitBtn.innerHTML = '<i class="fas fa-times"></i> Confirm Rejection';
            }
        }

        // Handle validation errors - reopen modal
        @if($errors->has('admin_justification'))
            document.addEventListener('DOMContentLoaded', () => {
                const lastId = sessionStorage.getItem('last_vetting_id');
                const lastName = sessionStorage.getItem('last_vetting_name');
                const lastJust = sessionStorage.getItem('last_vetting_justification');
                if (lastId && lastName && lastJust) {
                    openVettingModal(lastId, lastName, lastJust);
                }
            });
        @endif
    </script>


    <style>
        .btn-outline-success { border: 1px solid #28a745; color: #28a745; background: transparent; }
        .btn-success { background: #28a745; color: white; border: 1px solid #28a745; }
        .btn-outline-error { border: 1px solid #dc3545; color: #dc3545; background: transparent; }
        .btn-error { background: #dc3545; color: white; border: 1px solid #dc3545; }
    </style>
@endsection

