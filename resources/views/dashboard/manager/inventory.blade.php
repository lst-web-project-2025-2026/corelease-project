@extends('layouts.dashboard')

@section('dashboard-title')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-2xl);">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: var(--space-xs);">Inventory Management</h1>
            <p style="color: var(--text-secondary);">Summary of resources assigned to you.</p>
        </div>
        <div>
            <a href="{{ route('dashboard.manager.inventory.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus" style="margin-right: 0.5rem;"></i> New Resource
            </a>
        </div>
    </div>
@endsection

@section('dashboard-content')
    <x-ui.table>
        <x-slot name="thead">
            <th>Resource</th>
            <th class="text-center">Category</th>
            <th class="text-center">Status</th>
            <th class="text-center">Actions</th>
        </x-slot>

        @forelse($resources as $resource)
            <tr>
                <td>
                    <div style="display: flex; flex-direction: column;">
                        <strong>{{ $resource->name }}</strong>
                        <span class="text-muted" style="font-size: 0.75rem;">ID: #{{ str_pad($resource->id, 5, '0', STR_PAD_LEFT) }}</span>
                    </div>
                </td>
                <td class="text-center">
                    <x-ui.badge variant="secondary">{{ $resource->category->name }}</x-ui.badge>
                </td>
                <td class="text-center">
                    @php
                        $variant = match($resource->status) {
                            'Enabled' => 'success',
                            'Maintenance' => 'warning',
                            'Disabled' => 'error',
                            default => 'secondary'
                        };
                    @endphp
                    <x-ui.badge variant="{{ $variant }}">{{ $resource->status }}</x-ui.badge>
                </td>
                <td class="text-center">
                    <div style="display: flex; gap: var(--space-xs); justify-content: center;">
                        @php
                            $isMaintenance = $resource->status === 'Maintenance';
                            $title = $resource->status === 'Enabled' || $isMaintenance ? 'Disable Resource' : 'Enable Resource';
                            if ($isMaintenance) $title = 'Cannot change status during maintenance';
                        @endphp
                        <form action="{{ route('dashboard.manager.inventory.toggle', $resource) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" 
                                class="btn btn-secondary btn-xs" 
                                title="{{ $title }}"
                                {{ $isMaintenance ? 'disabled' : '' }}
                                style="{{ $isMaintenance ? 'opacity: 0.5; cursor: not-allowed;' : '' }}">
                                <i class="fas fa-power-off" style="color: {{ $resource->status === 'Disabled' ? 'var(--success)' : 'var(--error)' }};"></i>
                            </button>
                        </form>
                        
                        <a href="{{ route('dashboard.manager.inventory.maintenance', $resource) }}" class="btn btn-secondary btn-xs" title="Schedule Maintenance">
                            <i class="fas fa-tools"></i>
                        </a>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" style="text-align: center; padding: var(--space-2xl);">
                    <div class="empty-state">
                        <i class="fas fa-boxes" style="font-size: 2.5rem; color: var(--text-muted); margin-bottom: var(--space-md); display: block;"></i>
                        <p style="color: var(--text-muted);">No resources found in your inventory.</p>
                        <a href="{{ route('dashboard.manager.inventory.create') }}" class="btn btn-primary btn-sm" style="margin-top: var(--space-md);">Add Your First Resource</a>
                    </div>
                </td>
            </tr>
        @endforelse

        @if($resources->hasPages())
            <x-slot name="tfoot">
                <td colspan="4" style="padding: var(--space-lg) var(--space-xl);">
                    <div class="catalog-pagination">
                        {{ $resources->links('vendor.pagination.custom') }}
                    </div>
                </td>
            </x-slot>
        @endif
    </x-ui.table>
@endsection
