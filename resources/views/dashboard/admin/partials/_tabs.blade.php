<div class="admin-tabs" style="display: flex; gap: var(--space-md); margin-bottom: var(--space-xl); border-bottom: 1px solid var(--border-color); padding-bottom: 1px;">
    <a href="{{ route('dashboard.admin.vetting') }}" 
       class="tab-link {{ request()->routeIs('dashboard.admin.vetting') ? 'active' : '' }}"
       style="padding: var(--space-sm) var(--space-md); text-decoration: none; font-weight: 600; font-size: 0.875rem; color: var(--text-secondary); border-bottom: 2px solid transparent; transition: all var(--transition-fast);">
       Pending Vettings
    </a>
    <a href="{{ route('dashboard.admin.vetting.all') }}" 
       class="tab-link {{ request()->routeIs('dashboard.admin.vetting.all') ? 'active' : '' }}"
       style="padding: var(--space-sm) var(--space-md); text-decoration: none; font-weight: 600; font-size: 0.875rem; color: var(--text-secondary); border-bottom: 2px solid transparent; transition: all var(--transition-fast);">
       All Applications
    </a>
    <a href="{{ route('dashboard.admin.users') }}" 
       class="tab-link {{ request()->routeIs('dashboard.admin.users') ? 'active' : '' }}"
       style="padding: var(--space-sm) var(--space-md); text-decoration: none; font-weight: 600; font-size: 0.875rem; color: var(--text-secondary); border-bottom: 2px solid transparent; transition: all var(--transition-fast);">
       User Management
    </a>
</div>

<style>
.tab-link:hover {
    color: var(--text-primary);
}
.tab-link.active {
    color: var(--accent-primary) !important;
    border-bottom: 2px solid var(--accent-primary) !important;
}
</style>
