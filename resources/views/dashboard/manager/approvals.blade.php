@extends('layouts.dashboard')

@section('dashboard-title')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-2xl);">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: var(--space-xs);">Pending Reservations</h1>
            <p style="color: var(--text-secondary);">Review and moderate reservation requests for your assigned resources.</p>
        </div>
    </div>
@endsection

@section('dashboard-content')
    <div class="admin-tabs" style="display: flex; gap: var(--space-md); margin-bottom: var(--space-xl); border-bottom: 1px solid var(--border-color); padding-bottom: 1px;">
        <a href="{{ route('dashboard.manager.approvals', ['tab' => 'pending']) }}" 
           class="tab-link {{ $tab === 'pending' ? 'active' : '' }}"
           style="padding: var(--space-sm) var(--space-md); text-decoration: none; font-weight: 600; font-size: 0.875rem; color: var(--text-secondary); border-bottom: 2px solid transparent; transition: all var(--transition-fast);">
           Pending Requests
        </a>
        <a href="{{ route('dashboard.manager.approvals', ['tab' => 'history']) }}" 
           class="tab-link {{ $tab === 'history' ? 'active' : '' }}"
           style="padding: var(--space-sm) var(--space-md); text-decoration: none; font-weight: 600; font-size: 0.875rem; color: var(--text-secondary); border-bottom: 2px solid transparent; transition: all var(--transition-fast);">
           Moderation History
        </a>
    </div>

    <style>
    .tab-link:hover { color: var(--text-primary); }
    .tab-link.active { color: var(--accent-primary) !important; border-bottom: 2px solid var(--accent-primary) !important; }
    .empty-state { text-align: center; }
    .detail-row { display: flex; flex-direction: column; gap: var(--space-xs); margin-bottom: var(--space-md); }
    .detail-label { font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
    .detail-value { font-size: 0.9375rem; color: var(--text-primary); }
    
    .btn-outline-success { border: 1px solid #28a745; color: #28a745; background: transparent; }
    .btn-success { background: #28a745; color: white !important; border: 1px solid #28a745; }
    .btn-outline-error { border: 1px solid #dc3545; color: #dc3545; background: transparent; }
    .btn-error { background: #dc3545; color: white !important; border: 1px solid #dc3545; }
    </style>

    <x-ui.table>
        <x-slot name="thead">
            <th>User</th>
            <th>Resource</th>
            <th>Booking Period</th>
            <th class="text-center">{{ $tab === 'history' ? 'Status' : 'Action' }}</th>
        </x-slot>

        @forelse($requests as $request)
            <tr>
                <td>
                    <div style="display: flex; flex-direction: column;">
                        <strong>{{ $request->user->name }}</strong>
                        <span class="text-muted" style="font-size: 0.75rem;">{{ $request->user->email }}</span>
                    </div>
                </td>
                <td>
                    <div style="display: flex; flex-direction: column;">
                        <strong>{{ $request->resource->name }}</strong>
                        <span class="text-muted" style="font-size: 0.75rem;">{{ $request->resource->category->name }}</span>
                    </div>
                </td>
                <td>
                    <div style="display: flex; flex-direction: column; font-size: 0.875rem;">
                        <span>{{ $request->start_date->format('M d, Y') }}</span>
                        <span class="text-muted">to {{ $request->end_date->format('M d, Y') }}</span>
                    </div>
                </td>
                <td class="text-center">
                    @if($tab === 'history')
                        @php
                            $variant = match($request->status) {
                                'Approved' => 'success',
                                'Rejected' => 'error',
                                default => 'secondary'
                            };
                        @endphp
                        <x-ui.badge variant="{{ $variant }}">{{ $request->status }}</x-ui.badge>
                    @else
                        <button type="button" class="btn btn-secondary btn-xs" 
                                onclick='openReviewModal(@json($request->load(["user", "resource.category"])))'>
                            Review
                        </button>
                    @endif
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" style="text-align: center; padding: var(--space-2xl);">
                    <div class="empty-state">
                        <i class="fas fa-calendar-times" style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: var(--space-md); display: block;"></i>
                        <p style="color: var(--text-muted);">No {{ $tab === 'history' ? 'processed' : 'pending' }} requests found.</p>
                    </div>
                </td>
            </tr>
        @endforelse

        @if($requests->hasPages())
            <x-slot name="tfoot">
                <td colspan="4" style="padding: var(--space-lg) var(--space-xl);">
                    <div class="catalog-pagination">
                        {{ $requests->links('vendor.pagination.custom') }}
                    </div>
                </td>
            </x-slot>
        @endif
    </x-ui.table>

    <!-- Reservation Moderation Modal -->
    <x-ui.modal id="review-modal" title="Moderate Reservation Request">
        <form id="moderate-form" action="" method="POST">
            @csrf
            <div id="review-content">
                <div style="margin-bottom: var(--space-xl); padding: var(--space-md); background: var(--bg-tertiary); border-radius: 0.75rem; border: 1px solid var(--border-color);">
                    <p style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); margin-bottom: 4px;">User Justification</p>
                    <p id="modal-justification" style="font-size: 0.9375rem; color: var(--text-primary); line-height: 1.5; white-space: pre-wrap;"></p>
                </div>

                <div id="historical-mod-data" style="display: none; margin-bottom: var(--space-lg);">
                    <div class="detail-row">
                        <span class="detail-label">Your Previous Justification</span>
                        <div id="modal-prev-justification" style="background: var(--bg-secondary); color: var(--text-secondary); padding: var(--space-md); border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 0.875rem; line-height: 1.6; white-space: pre-wrap;"></div>
                    </div>
                </div>

                <div id="moderation-controls">
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

                    <div class="detail-row">
                        <x-ui.textarea 
                            label="Manager Justification" 
                            name="justification" 
                            placeholder="Explain your decision (required)..." 
                            required 
                            rows="4"
                            hide-error="true"
                        />
                        @error('justification')
                            <div class="alert alert-error" style="margin-bottom: var(--space-md); font-size: 0.875rem; padding: var(--space-sm) var(--space-md);">
                                <i class="fas fa-exclamation-circle"></i>
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: var(--space-sm); margin-top: var(--space-xl);">
                    <x-ui.button type="button" variant="secondary" onclick="toggleModal('review-modal', false)">
                        <span id="close-btn-text">Cancel</span>
                    </x-ui.button>
                    <div id="submit-btn-container">
                        <x-ui.button type="submit" id="submit-btn" variant="primary">Confirm Moderation</x-ui.button>
                    </div>
                </div>
            </div>
        </form>
    </x-ui.modal>

    <script>
        function openReviewModal(request) {
            const form = document.getElementById('moderate-form');
            const modal = document.getElementById('review-modal');
            
            document.getElementById('modal-justification').textContent = request.user_justification || 'No justification provided.';

            const modControls = document.getElementById('moderation-controls');
            const submitContainer = document.getElementById('submit-btn-container');
            const historyData = document.getElementById('historical-mod-data');
            const closeBtnText = document.getElementById('close-btn-text');
            
            // Set title with requester name
            modal.querySelector('h3').textContent = `Reviewing: ${request.user.name}`;

            if (request.status === 'Pending') {
                form.action = `/dashboard/manager/approvals/${request.id}/moderate`;
                modControls.style.display = 'block';
                submitContainer.style.display = 'block';
                historyData.style.display = 'none';
                closeBtnText.textContent = 'Cancel';
                
                // Set default status if not already selected
                const checkedStatus = document.querySelector('input[name="status"]:checked');
                selectEdict(checkedStatus ? checkedStatus.value : 'Approved');
            } else {
                modControls.style.display = 'none';
                submitContainer.style.display = 'none';
                historyData.style.display = 'block';
                document.getElementById('modal-prev-justification').textContent = request.manager_justification || 'No justification recorded.';
                closeBtnText.textContent = 'Close';
            }

            // Store for validation recovery
            sessionStorage.setItem('last_mod_request', JSON.stringify(request));

            toggleModal('review-modal', true);
        }

        function selectEdict(status) {
            const approveBtn = document.getElementById('btn-approve');
            const rejectBtn = document.getElementById('btn-reject');
            const submitBtn = document.getElementById('submit-btn');
            const radios = document.getElementsByName('status');
            
            radios.forEach(r => r.checked = (r.value === status));
            
            if (status === 'Approved') {
                approveBtn.classList.remove('btn-outline-success');
                approveBtn.classList.add('btn-success');
                
                rejectBtn.classList.remove('btn-error');
                rejectBtn.classList.add('btn-outline-error');
                
                submitBtn.className = 'btn btn-primary';
                submitBtn.innerHTML = '<i class="fas fa-check"></i> Complete Approval';
            } else {
                approveBtn.classList.remove('btn-success');
                approveBtn.classList.add('btn-outline-success');
                
                rejectBtn.classList.remove('btn-outline-error');
                rejectBtn.classList.add('btn-error');
                
                submitBtn.className = 'btn btn-error';
                submitBtn.innerHTML = '<i class="fas fa-times"></i> Confirm Rejection';
            }
        }

        // Handle validation errors - reopen modal
        @if($errors->any())
            document.addEventListener('DOMContentLoaded', () => {
                const lastRequest = sessionStorage.getItem('last_mod_request');
                if (lastRequest) {
                    const request = JSON.parse(lastRequest);
                    openReviewModal(request);
                    
                    // Restore selected status
                    const oldStatus = "{{ old('status') }}";
                    if (oldStatus) {
                        selectEdict(oldStatus);
                    }
                }
            });
        @endif
    </script>
@endsection
