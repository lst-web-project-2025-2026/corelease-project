@extends('layouts.dashboard')

@section('dashboard-title')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-2xl);">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: var(--space-xs);">Incidents Feed</h1>
            <p style="color: var(--text-secondary);">Monitor and resolve issues reported for your assigned resources.</p>
        </div>
    </div>
@endsection

@section('dashboard-content')
    <div class="admin-tabs" style="display: flex; gap: var(--space-md); margin-bottom: var(--space-xl); border-bottom: 1px solid var(--border-color); padding-bottom: 1px;">
        <a href="{{ route('dashboard.manager.incidents', ['tab' => 'open']) }}" 
           class="tab-link {{ $tab === 'open' ? 'active' : '' }}"
           style="padding: var(--space-sm) var(--space-md); text-decoration: none; font-weight: 600; font-size: 0.875rem; color: var(--text-secondary); border-bottom: 2px solid transparent; transition: all var(--transition-fast);">
           Open Issues
        </a>
        <a href="{{ route('dashboard.manager.incidents', ['tab' => 'all']) }}" 
           class="tab-link {{ $tab === 'all' ? 'active' : '' }}"
           style="padding: var(--space-sm) var(--space-md); text-decoration: none; font-weight: 600; font-size: 0.875rem; color: var(--text-secondary); border-bottom: 2px solid transparent; transition: all var(--transition-fast);">
           All Incidents
        </a>
    </div>

    <style>
    .tab-link:hover { color: var(--text-primary); }
    .tab-link.active { color: var(--accent-primary) !important; border-bottom: 2px solid var(--accent-primary) !important; }
    .empty-state { text-align: center; }
    .detail-row { display: flex; flex-direction: column; gap: var(--space-xs); margin-bottom: var(--space-md); }
    .detail-label { font-size: 0.75rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
    .detail-value { font-size: 0.9375rem; color: var(--text-primary); }
    </style>

    <x-ui.table>
        <x-slot name="thead">
            <th>Resource</th>
            <th>Reported By</th>
            <th>Date</th>
            <th class="text-center">Status</th>
            <th class="text-center">Action</th>
        </x-slot>

        @forelse($incidents as $incident)
            <tr>
                <td>
                    <strong>{{ $incident->resource->name }}</strong>
                </td>
                <td>
                    <div style="display: flex; flex-direction: column;">
                        <strong>{{ $incident->user->name }}</strong>
                        <span class="text-muted" style="font-size: 0.75rem;">{{ $incident->user->email }}</span>
                    </div>
                </td>
                <td>
                    <span class="text-muted">{{ $incident->created_at->format('M d, Y') }}</span>
                </td>
                <td class="text-center">
                    @php
                        $statusClass = $incident->status === 'Open' ? 'error' : 'success';
                    @endphp
                    <x-ui.badge variant="{{ $statusClass }}">{{ $incident->status }}</x-ui.badge>
                </td>
                <td class="text-center">
                    <div style="display: flex; gap: var(--space-xs); justify-content: center;">
                        <button type="button" class="btn btn-secondary btn-xs" 
                                onclick='openReviewModal(@json($incident->load(["user", "resource.category"])))'>
                            Review
                        </button>
                        @if($incident->status === 'Open')
                            <form action="{{ route('dashboard.manager.incidents.resolve', $incident) }}" method="POST" style="display: inline;">
                                @csrf
                                <x-ui.button type="submit" variant="primary" size="xs">Resolve</x-ui.button>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" style="text-align: center; padding: var(--space-2xl);">
                    <div class="empty-state">
                        <i class="fas fa-exclamation-circle" style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: var(--space-md); display: block;"></i>
                        <p style="color: var(--text-muted);">No incidents found.</p>
                    </div>
                </td>
            </tr>
        @endforelse

        @if($incidents->hasPages())
            <x-slot name="tfoot">
                <td colspan="5" style="padding: var(--space-lg) var(--space-xl);">
                    <div class="catalog-pagination">
                        {{ $incidents->appends(['tab' => $tab])->links('vendor.pagination.custom') }}
                    </div>
                </td>
            </x-slot>
        @endif
    </x-ui.table>

    <!-- Incident Review Modal -->
    <x-ui.modal id="review-modal" title="Review Incident Details">
        <div id="review-content">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-lg); margin-bottom: var(--space-xl);">
                <div class="detail-row">
                    <span class="detail-label">Resource</span>
                    <span id="modal-resource-name" class="detail-value" style="font-weight: 600;"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Category</span>
                    <span id="modal-resource-category" class="detail-value"></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Reported By</span>
                    <div class="detail-value">
                        <div id="modal-user-name" style="font-weight: 600;"></div>
                        <div id="modal-user-email" class="text-muted" style="font-size: 0.8125rem;"></div>
                    </div>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Date Reported</span>
                    <span id="modal-date" class="detail-value"></span>
                </div>
            </div>

            <div class="detail-row">
                <span class="detail-label">Issue Description</span>
                <div id="modal-description" style="background: var(--bg-primary); color: var(--text-primary); padding: var(--space-md); border-radius: var(--radius-md); border: 1px solid var(--border-color); font-size: 0.875rem; line-height: 1.6; white-space: pre-wrap;"></div>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: var(--space-sm); margin-top: var(--space-2xl);">
                <x-ui.button type="button" variant="secondary" onclick="toggleModal('review-modal', false)">Close</x-ui.button>
                <div id="modal-resolve-container">
                    <form id="modal-resolve-form" action="" method="POST" style="display: inline;">
                        @csrf
                        <x-ui.button type="submit" variant="primary">Mark as Resolved</x-ui.button>
                    </form>
                </div>
            </div>
        </div>
    </x-ui.modal>

    <script>
        function openReviewModal(incident) {
            document.getElementById('modal-resource-name').textContent = incident.resource.name;
            document.getElementById('modal-resource-category').textContent = incident.resource.category.name;
            document.getElementById('modal-user-name').textContent = incident.user.name;
            document.getElementById('modal-user-email').textContent = incident.user.email;
            
            const date = new Date(incident.created_at);
            document.getElementById('modal-date').textContent = date.toLocaleDateString('en-US', { 
                year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' 
            });
            
            document.getElementById('modal-description').textContent = incident.description;

            const resolveForm = document.getElementById('modal-resolve-form');
            const resolveContainer = document.getElementById('modal-resolve-container');
            
            if (incident.status === 'Open') {
                resolveForm.action = `/dashboard/manager/incidents/${incident.id}/resolve`;
                resolveContainer.style.display = 'block';
            } else {
                resolveContainer.style.display = 'none';
            }

            toggleModal('review-modal', true);
        }
    </script>
@endsection
