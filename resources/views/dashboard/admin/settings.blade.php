@extends('layouts.dashboard')

@section('dashboard-title')
    <div style="margin-bottom: var(--space-2xl);">
        <h1 style="font-size: 2rem; margin-bottom: var(--space-xs);">System Settings</h1>
        <p style="color: var(--text-secondary);">Manage global platform configuration and safety protocols.</p>
    </div>
@endsection

@section('dashboard-content')
    <div style="max-width: 800px;">
        <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 1rem; overflow: hidden; box-shadow: var(--shadow-sm);">
            <div style="padding: var(--space-xl); border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: var(--space-md);">
                <div style="width: 40px; height: 40px; background: rgba(239, 68, 68, 0.1); color: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-power-off"></i>
                </div>
                <div>
                    <h3 style="font-size: 1.125rem; font-weight: 600;">Global Kill Switch</h3>
                    <p style="font-size: 0.875rem; color: var(--text-secondary); margin: 0;">Danger Zone: Controls access to the entire facility.</p>
                </div>
            </div>

            <div style="padding: var(--space-xl);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: var(--space-2xl);">
                    <div style="flex: 1;">
                        <h4 style="font-size: 1rem; font-weight: 600; margin-bottom: var(--space-xs);">Facility Maintenance Mode</h4>
                        <p style="font-size: 0.875rem; color: var(--text-secondary); line-height: 1.5;">
                            When enabled, only the homepage remain accessible. All other platform features (Catalog, Reservations, Auth) will be locked and redirected to a maintenance message. 
                            <strong>Admins will maintain dashboard access.</strong>
                        </p>
                    </div>

                    <form action="{{ route('dashboard.admin.settings.toggle-maintenance') }}" method="POST" id="maintenance-form">
                        @csrf
                        <input type="hidden" name="maintenance_mode" value="{{ $isMaintenanceEnabled ? '0' : '1' }}">
                        
                        <button type="submit" 
                                class="btn {{ $isMaintenanceEnabled ? 'btn-primary' : 'btn-outline-error' }}" 
                                style="min-width: 160px; {{ $isMaintenanceEnabled ? '' : 'border-color: #ef4444; color: #ef4444;' }}">
                            <i class="fas fa-{{ $isMaintenanceEnabled ? 'play-circle' : 'stop-circle' }}"></i>
                            {{ $isMaintenanceEnabled ? 'Disable Mode' : 'Enable Mode' }}
                        </button>
                    </form>
                </div>

                @if($isMaintenanceEnabled)
                    <div style="margin-top: var(--space-xl); padding: var(--space-lg); background: rgba(239, 68, 68, 0.05); border-radius: 0.75rem; border: 1px solid rgba(239, 68, 68, 0.2); display: flex; align-items: center; gap: var(--space-md); color: #ef4444;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span style="font-size: 0.875rem; font-weight: 500;">Warning: The platform is currently locked to all non-admin users.</span>
                    </div>
                @endif
            </div>
        </div>

        <div style="margin-top: var(--space-2xl); color: var(--text-muted); font-size: 0.8125rem; text-align: center;">
            <p><i class="fas fa-info-circle"></i> Changes to system settings are logged in the audit trail.</p>
        </div>
    </div>
@endsection
