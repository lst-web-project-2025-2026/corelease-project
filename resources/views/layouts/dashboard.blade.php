@extends('layouts.app')

@section('title', 'Dashboard')

@section('styles')
    @vite(['resources/css/dashboard.css'])
@endsection

@section('content')
<div class="dashboard-container">
    <aside class="dashboard-sidebar">
        <nav class="sidebar-nav">
            <!-- Global Section -->
            <div class="sidebar-section">
                <p class="sidebar-title">Menu</p>
                <a href="{{ auth()->user()->role === 'Admin' ? route('dashboard.admin.index') : route('dashboard') }}" 
                   class="sidebar-link {{ request()->routeIs('dashboard') || request()->routeIs('dashboard.admin.index') ? 'active' : '' }}">
                    <i class="fas fa-th-large"></i>
                    <span>Overview</span>
                </a>

                <a href="{{ route('catalog.index') }}" class="sidebar-link {{ request()->routeIs('catalog.index') ? 'active' : '' }}">
                    <i class="fas fa-book"></i>
                    <span>Catalog</span>
                </a>
            </div>

            <!-- User Section -->
            <div class="sidebar-section">
                <p class="sidebar-title">Personal</p>
                <a href="{{ route('dashboard.reservations') }}" class="sidebar-link {{ request()->routeIs('dashboard.reservations') ? 'active' : '' }}">
                    <i class="fas fa-calendar-alt"></i>
                    <span>My Reservations</span>
                </a>
            </div>

            <!-- Manager Section -->
            @if(auth()->user()->role === 'Manager')
            <div class="sidebar-section">
                <p class="sidebar-title">Management</p>
                <a href="{{ route('dashboard.manager.inventory') }}" class="sidebar-link {{ request()->routeIs('dashboard.manager.inventory*') ? 'active' : '' }}">
                    <i class="fas fa-boxes"></i>
                    <span>Inventory</span>
                </a>
                <a href="{{ route('dashboard.manager.approvals') }}" class="sidebar-link {{ request()->routeIs('dashboard.manager.approvals') ? 'active' : '' }}">
                    <i class="fas fa-check-circle"></i>
                    <span>Pending Reservations</span>
                </a>
                <a href="{{ route('dashboard.manager.incidents') }}" class="sidebar-link {{ request()->routeIs('dashboard.manager.incidents') ? 'active' : '' }}">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Incidents</span>
                </a>
                <a href="{{ route('dashboard.manager.maintenance') }}" class="sidebar-link {{ request()->routeIs('dashboard.manager.maintenance') ? 'active' : '' }}">
                    <i class="fas fa-tools"></i>
                    <span>Maintenances</span>
                </a>
            </div>
            @endif

            <!-- Admin Section -->
            @if(auth()->user()->role === 'Admin')
            <div class="sidebar-section">
                <p class="sidebar-title">Administration</p>
                <a href="{{ route('dashboard.admin.vetting') }}" class="sidebar-link {{ request()->routeIs('dashboard.admin.vetting') ? 'active' : '' }}">
                    <i class="fas fa-users-cog"></i>
                    <span>User Vetting</span>
                </a>
                <a href="{{ route('dashboard.admin.settings') }}" class="sidebar-link {{ request()->routeIs('dashboard.admin.settings') ? 'active' : '' }}">
                    <i class="fas fa-cogs"></i>
                    <span>System Settings</span>
                </a>
                <a href="{{ route('dashboard.admin.audit') }}" class="sidebar-link {{ request()->routeIs('dashboard.admin.audit') ? 'active' : '' }}">
                    <i class="fas fa-clipboard-list"></i>
                    <span>Audit Logs</span>
                </a>
            </div>
            @endif
        </nav>
    </aside>

    <main class="dashboard-main">
        <header class="dashboard-header">
            @yield('dashboard-title')
        </header>
        
        <div class="dashboard-content">
            @if(session('success') || session('message'))
                <div class="alert alert-success animate-fade-in" style="margin-bottom: var(--space-lg); padding: var(--space-md); border-radius: 0.5rem; background: rgba(40, 167, 69, 0.1); color: #28a745; border: 1px solid rgba(40, 167, 69, 0.2); backdrop-filter: blur(10px);">
                    {{ session('success') ?: session('message') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error animate-fade-in" style="margin-bottom: var(--space-lg); padding: var(--space-md); border-radius: 0.5rem; background: rgba(220, 53, 69, 0.1); color: #dc3545; border: 1px solid rgba(220, 53, 69, 0.2); backdrop-filter: blur(10px);">
                    {{ session('error') }}
                </div>
            @endif

            @yield('dashboard-content')
        </div>
    </main>
</div>
@endsection
