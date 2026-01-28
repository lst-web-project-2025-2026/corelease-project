@extends('layouts.app')

@section('title', 'Resource Catalog')

@section('styles')
    @vite(['resources/css/pages/catalog.css'])
@endsection

@section('content')
<div class="catalog-page container">
    <header class="catalog-header animate-fade-in">
        <h1 class="page-title">Technical Resource Catalog</h1>
        <p class="page-subtitle text-secondary">Browse our high-performance computing nodes, storage clusters, and specialized hardware specifications.</p>
    </header>

    @if(session('success'))
        <div class="alert alert-success animate-fade-in">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-error animate-fade-in">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('catalog.index') }}" method="GET" id="catalog-form">

        <div class="catalog-layout">
            <!-- Sidebar Filters -->
            <aside class="catalog-sidebar animate-fade-in">
                <div class="filter-section">
                    <h3 class="filter-title">Filter by Category</h3>
                    <div class="filter-options">
                        @foreach($categories as $category)
                            <label class="filter-checkbox">
                                <input type="checkbox" name="categories[]" value="{{ $category->name }}" 
                                    {{ (request('categories') && in_array($category->name, request('categories'))) || !request()->has('categories') ? 'checked' : '' }}>
                                <span>{{ $category->name }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="filter-section">
                    <h3 class="filter-title">Availability</h3>
                    <div class="filter-options">
                        @foreach(['Enabled' => 'Online & Available', 'Maintenance' => 'Under Maintenance', 'Disabled' => 'Manually Disabled'] as $val => $label)
                            <label class="filter-checkbox">
                                <input type="checkbox" name="statuses[]" value="{{ $val }}"
                                    {{ (request('statuses') && in_array($val, request('statuses'))) || !request()->has('statuses') ? 'checked' : '' }}>
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="filter-section">
                    <h3 class="filter-title">Sort Order</h3>
                    <select name="sort" class="form-select">
                        <option value="category_asc" {{ request('sort') == 'category_asc' ? 'selected' : '' }}>Category (A-Z)</option>
                        <option value="category_desc" {{ request('sort') == 'category_desc' ? 'selected' : '' }}>Category (Z-A)</option>
                        <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                        <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name (Z-A)</option>
                        <option value="status_asc" {{ request('sort') == 'status_asc' ? 'selected' : '' }}>Status</option>
                    </select>
                </div>

                <div class="sidebar-actions">
                    <x-ui.button type="submit" style="width: 100%;">Apply Filters</x-ui.button>
                    @if(request()->anyFilled(['categories', 'statuses', 'sort']))
                        <a href="{{ route('catalog.index') }}" class="btn-text text-muted" style="display: block; text-align: center; margin-top: 1rem; font-size: 0.875rem;">Clear All</a>
                    @endif
                </div>

                <div class="sidebar-info" style="margin-top: 2rem;">
                    <p class="text-muted"><i class="icon-info"></i> Justified access is required for all resources. <a href="/apply">Apply for access</a> if you are a new researcher.</p>
                </div>
            </aside>

            <!-- Resource Grid -->
            <main class="catalog-main">
                <div class="catalog-top-actions animate-fade-in">
                    <div class="results-count">
                        Showing {{ $resources->firstItem() ?? 0 }} - {{ $resources->lastItem() ?? 0 }} of {{ $resources->total() }} resources
                    </div>
                </div>

                <div class="catalog-pagination pagination-top animate-fade-in">
                    {{ $resources->links('vendor.pagination.custom') }}
                </div>

                <div class="catalog-grid animate-fade-in">
                    @forelse ($resources as $resource)
                        <x-ui.card class="resource-card {{ $resource->status === 'Disabled' ? 'resource-disabled' : '' }}">
                            <div class="card-header">
                                <x-ui.badge variant="primary">{{ $resource->category->name }}</x-ui.badge>
                                <x-ui.status status="{{ $resource->status === 'Enabled' ? 'online' : ($resource->status === 'Maintenance' ? 'warning' : 'offline') }}" />
                            </div>
                            
                            <h3 class="resource-name">{{ $resource->name }}</h3>
                            
                            <div class="resource-specs">
                                @if(is_array($resource->specs))
                                    <div class="specs-list">
                                        @foreach($resource->specs as $key => $value)
                                            <div class="spec-item">
                                                <span class="spec-key">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                                <span class="spec-value">{{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-muted">No specifications available.</p>
                                @endif
                            </div>

                            <div class="card-footer">
                                @if($resource->status === 'Disabled')
                                    <x-ui.button variant="secondary" style="width: 100%; cursor: not-allowed;" disabled>
                                        Node Offline
                                    </x-ui.button>
                                @else
                                    @auth
                                        <x-ui.button 
                                            type="button"
                                            variant="primary" 
                                            style="width: 100%;"
                                            class="reserve-trigger"
                                            data-id="{{ $resource->id }}"
                                            data-name="{{ $resource->name }}"
                                            data-category="{{ $resource->category->name }}"
                                            data-allow-os="{{ (isset($resource->specs['allow_os']) && $resource->specs['allow_os']) ? 'true' : 'false' }}"
                                        >
                                            Reserve Node
                                        </x-ui.button>
                                    @else
                                        <x-ui.button href="/login" variant="secondary" style="width: 100%;">
                                            Login to Reserve
                                        </x-ui.button>
                                    @endauth
                                @endif
                            </div>
                        </x-ui.card>
                    @empty
                        <div class="empty-state">
                            <p class="text-secondary">No resources match your current filters.</p>
                        </div>
                    @endforelse
                </div>

                <div class="catalog-pagination pagination-bottom">
                    {{ $resources->links('vendor.pagination.custom') }}
                </div>
            </main>
        </div>
    </form>
</div>

@auth
    <x-ui.modal id="reservation-modal" title="Reserve Resource">
        <form id="reservation-form" action="{{ route('reservations.store') }}" method="POST">
            @csrf
            
            @if($errors->any() && (old('resource_id') || $errors->has('reservation')))
                <div class="alert alert-error animate-fade-in" style="margin-bottom: var(--space-md);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <ul style="margin-left: 1.5rem; font-size: 0.875rem;">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <input type="hidden" name="resource_id" id="modal-resource-id" value="{{ old('resource_id') }}">
            
            <div class="form-group">
                <label class="form-label">Resource</label>
                <div id="modal-resource-name" class="form-input" style="background: var(--bg-tertiary); pointer-events: none; opacity: 0.8;"></div>
            </div>

            <div class="form-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-md);">
                <x-ui.datepicker 
                    name="start_date" 
                    label="Start Date" 
                    required 
                    min="{{ date('Y-m-d') }}" 
                />
                <x-ui.datepicker 
                    name="end_date" 
                    label="End Date" 
                    required 
                    min="{{ date('Y-m-d') }}" 
                />
            </div>

            <!-- Dynamic Configuration (VM OS selection) -->
            <div id="vm-config-section" style="display: none;">
                <div class="form-group">
                    <label class="form-label">Requested Operating System</label>
                    <select name="configuration[os]" class="form-select @error('configuration.os') is-invalid @enderror">
                        <option value="">-- Select an OS --</option>
                        <option value="ubuntu_22_04" {{ old('configuration.os') == 'ubuntu_22_04' ? 'selected' : '' }}>Ubuntu 22.04 LTS</option>
                        <option value="ubuntu_20_04" {{ old('configuration.os') == 'ubuntu_20_04' ? 'selected' : '' }}>Ubuntu 20.04 LTS</option>
                        <option value="debian_11" {{ old('configuration.os') == 'debian_11' ? 'selected' : '' }}>Debian 11</option>
                        <option value="centos_8" {{ old('configuration.os') == 'centos_8' ? 'selected' : '' }}>CentOS 8 Stream</option>
                        <option value="windows_server_2022" {{ old('configuration.os') == 'windows_server_2022' ? 'selected' : '' }}>Windows Server 2022</option>
                    </select>
                    @error('configuration.os')
                        <span class="form-error">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Usage Justification</label>
                <textarea name="user_justification" class="form-textarea" placeholder="Explain why you need this resource for your research/project..." required minlength="10"></textarea>
                <p class="text-muted" style="font-size: 0.75rem; margin-top: 0.5rem;">A manager will review this justification before approval.</p>
            </div>

            <x-slot name="footer">
                <x-ui.button variant="secondary" type="button" onclick="document.getElementById('reservation-modal').close()">Cancel</x-ui.button>
                <x-ui.button variant="primary" type="submit" id="reserve-submit-btn" form="reservation-form">Confirm Reservation</x-ui.button>
            </x-slot>
        </form>
    </x-ui.modal>
@endauth

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const triggers = document.querySelectorAll('.reserve-trigger');
            triggers.forEach(trigger => {
                trigger.addEventListener('click', function() {
                    const resourceId = this.getAttribute('data-id');
                    const resourceName = this.getAttribute('data-name');
                    const categoryName = this.getAttribute('data-category');
                    const allowOs = this.getAttribute('data-allow-os') === 'true';
                    
                    // Clear previous errors if we're opening for a NEW resource
                    const errorAlert = document.querySelector('#reservation-modal .alert-error');
                    if (errorAlert) {
                        errorAlert.remove();
                    }

                    openReservationModal({
                        id: resourceId,
                        name: resourceName,
                        category_name: categoryName,
                        allow_os: allowOs
                    });
                });
            });

            // Loading state for reservation form
            const reservationForm = document.getElementById('reservation-form');
            if (reservationForm) {
                reservationForm.addEventListener('submit', function() {
                    const btn = document.getElementById('reserve-submit-btn');
                    if (btn) {
                        btn.innerHTML = '<span class="loading-spinner"></span> Processing...';
                        // Use setTimeout to ensure the form submission starts before disabling
                        setTimeout(() => {
                            btn.disabled = true;
                        }, 0);
                    }
                });
            }
            
            // Auto-open modal if there are errors (meaning valid redirect back with input)
            @if($errors->any() && (old('resource_id') || $errors->has('reservation')))
                const oldResourceId = "{{ old('resource_id') }}";
                const oldResourceName = "{{ session('old_resource_name') }}";
                const oldCategory = "{{ session('old_category') }}";

                if (oldResourceId) {
                    openReservationModal({
                        id: oldResourceId,
                        name: oldResourceName || 'Selected Resource',
                        category_name: oldCategory,
                        allow_os: oldCategory === 'Virtual Machine' // Fallback for auto-open
                    });
                }
            @endif
        });

        function openReservationModal(resource) {
            const modal = document.getElementById('reservation-modal');
            if (!modal) return;

            document.getElementById('modal-resource-id').value = resource.id;
            document.getElementById('modal-resource-name').textContent = resource.name;
            
            // Check if it's a VM to show OS config
            const vmSection = document.getElementById('vm-config-section');
            if (resource.allow_os) {
                vmSection.style.display = 'block';
            } else {
                vmSection.style.display = 'none';
            }
            
            if (typeof window.toggleModal === 'function') {
                window.toggleModal('reservation-modal', true);
            } else {
                modal.showModal();
            }
        }

        console.log('Catalog page initialized');
    </script>
@endsection