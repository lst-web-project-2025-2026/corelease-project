@extends('layouts.app')

@section('title', 'Infrastructure for Research')

@section('styles')
    @vite(['resources/css/pages/home.css'])
@endsection

@section('content')
<div class="landing-page">
    <div class="hero-bg-glow"></div>
    
    <!-- Hero Section -->
    <header class="hero">
        <div class="container hero-content animate-fade-in">
            @if(session('message'))
                <div class="alert alert-success animate-fade-in" style="margin-bottom: 2rem; padding: 1rem; border-radius: 0.5rem; background: rgba(40, 167, 69, 0.1); color: #28a745; border: 1px solid rgba(40, 167, 69, 0.2); backdrop-filter: blur(10px);">
                    {{ session('message') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error animate-fade-in" style="margin-bottom: 2rem; padding: 1rem; border-radius: 0.5rem; background: rgba(220, 53, 69, 0.1); color: #dc3545; border: 1px solid rgba(220, 53, 69, 0.2); backdrop-filter: blur(10px);">
                    {{ session('error') }}
                </div>
            @endif

            @auth
                <h1 class="hero-title">Welcome Back, {{ Auth::user()->name }}</h1>
                <p class="hero-subtitle text-secondary">
                    You are logged in as a <strong>{{ Auth::user()->role }}</strong>. 
                    Manage your resources and reservations from your dashboard.
                </p>
                <div class="hero-actions" style="display: flex; flex-direction: column; align-items: center; gap: var(--space-md);">
                    <x-ui.button href="/dashboard" class="btn-lg">Go to Dashboard</x-ui.button>
                    
                    @if(auth()->user()->role === 'Admin' && $systemStatus === 'Maintenance')
                        <a href="{{ route('dashboard.admin.settings') }}" class="btn btn-outline-error btn-sm" style="border-color: #ef4444; color: #ef4444; background: rgba(239, 68, 68, 0.05);">
                            <i class="fas fa-exclamation-triangle"></i> Administrative Emergency: Manage Lockdown
                        </a>
                    @endif
                </div>
            @else
                <h1 class="hero-title">Reliable Computing for Internal Research</h1>
                <p class="hero-subtitle text-secondary">Corelease provides transparent access to data center nodes, virtual machines, and specialized hardware for authorized research groups and personnel.</p>

                <div class="hero-actions">
                    <x-ui.button href="/apply" class="btn-lg">Request Resource Access</x-ui.button>
                    <x-ui.button href="/catalog" variant="secondary" class="btn-lg">View Available Nodes</x-ui.button>
                </div>
            @endauth
        </div>
    </header>

    <!-- Live Status Component -->
    <section class="live-status container">
        <div class="section-header">
            <h2 class="section-title">Global Resource Status</h2>
            <p class="text-secondary">A live overview of our current computational allocation and facility health.</p>
        </div>

        <div class="status-grid animate-fade-in">
            <x-ui.card class="status-card">
                <div class="status-icon"><i class="icon-server"></i></div>
                <div class="status-value">{{ $totalResources }}</div>
                <div class="status-label">Total Nodes Managed</div>
            </x-ui.card>

            <x-ui.card class="status-card">
                <div class="status-icon"><i class="icon-check"></i></div>
                <div class="status-value">{{ $availableNow }}</div>
                <div class="status-label">Nodes Ready for Lease</div>
            </x-ui.card>

            <x-ui.card class="status-card">
                <div class="status-icon"><i class="icon-users"></i></div>
                <div class="status-value">{{ $activeUsers }}</div>
                <div class="status-label">Authorized Operators</div>
            </x-ui.card>

            <x-ui.card class="status-card {{ $systemStatus === 'Operational' ? 'status-green' : 'status-red' }}">
                <div class="status-icon"><i class="icon-activity"></i></div>
                <div class="status-value">{{ $systemStatus }}</div>
                <div class="status-label">Facility Health Status</div>
            </x-ui.card>
        </div>
    </section>

    <!-- Visual Divider -->
    <div class="divider container"></div>

    <!-- Application Status Checker -->
    @guest
    @if($systemStatus === 'Operational')
    <section id="status-checker" class="status-checker container animate-fade-in">
        <div class="section-header">
            <h2 class="section-title">Check Application Status</h2>
            <p class="text-secondary">Already applied? Enter your credentials to see your application progress.</p>
        </div>

        <div class="status-form">
            <form action="{{ route('status.check') }}" method="POST">
                @csrf
                <x-ui.input 
                    label="Email Address" 
                    name="email" 
                    type="email" 
                    placeholder="your@email.com" 
                    required 
                    value="{{ old('email') }}"
                />
                
                <x-ui.input 
                    label="Password" 
                    name="password" 
                    type="password" 
                    placeholder="••••••••" 
                    required 
                />

                @error('status_email')
                    <p class="text-error" style="margin-top: -0.5rem; margin-bottom: 1rem; font-size: 0.875rem;">{{ $message }}</p>
                @enderror

                <x-ui.button type="submit" style="width: 100%;">Check Status</x-ui.button>
            </form>

            @if(session('status_result'))
                <div class="status-result animate-slide-up">
                    <div class="result-header">
                        <span class="text-secondary">Applied on {{ session('status_result')['created_at'] }}</span>
                        <span class="result-status status-badge-{{ strtolower(session('status_result')['status']) }}">
                            {{ session('status_result')['status'] }}
                        </span>
                    </div>

                    @if(session('status_result')['status'] === 'Pending')
                        <p>Your application is currently being reviewed by our administrators. Please check back later.</p>
                    @elseif(session('status_result')['status'] === 'Approved')
                        <p class="text-success">Congratulations! Your application has been approved. You can now <a href="/login">login</a> to the system.</p>
                    @elseif(session('status_result')['status'] === 'Rejected')
                        <p class="text-error">Unfortunately, your application was not approved at this time.</p>
                    @endif

                    @if(session('status_result')['admin_justification'])
                        <div class="justification-box">
                            <strong>Admin Feedback:</strong>
                            <p>{{ session('status_result')['admin_justification'] }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </section>

    <div class="divider container"></div>
    @else
    <section class="container animate-fade-in">
        <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); padding: var(--space-2xl); border-radius: 1rem; text-align: center;">
            <i class="fas fa-lock" style="font-size: 2rem; color: var(--accent-primary); margin-bottom: var(--space-md);"></i>
            <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: var(--space-xs);">Public Services Suspended</h2>
            <p class="text-secondary">The application status gateway is temporarily closed for maintenance.</p>
        </div>
    </section>
    <div class="divider container"></div>
    @endif
    @endguest


    <!-- Features Section / How it Works -->
    <section class="features container">
         <div class="features-grid">
             <div class="feature-item">
                 <h3>Open Inventory</h3>
                 <p class="text-secondary">Browse our technical specifications for servers, storage clusters, and network nodes without requiring an initial account.</p>
                 <a href="/catalog" class="feature-link">Inventory Specs &rarr;</a>
             </div>
             @auth
             <div class="feature-item">
                 <h3>Active Dashboard</h3>
                 <p class="text-secondary">Access your personalized operations center to manage your resources, monitor your active nodes, and view your history.</p>
                 <a href="/dashboard" class="feature-link">Go to Dashboard &rarr;</a>
             </div>
             <div class="feature-item">
                 <h3>Communications Hub</h3>
                 <p class="text-secondary">View system-wide broadcast alerts, notification history, and updates regarding your resource allocations and approvals.</p>
                 <a href="/notifications" class="feature-link">View Notifications &rarr;</a>
             </div>
             @else
             <div class="feature-item">
                 <h3>Justified Allocation</h3>
                 <p class="text-secondary">Submit reservation requests with detailed project justifications. Our managers prioritize high-impact research needs.</p>
                 <a href="/login" class="feature-link">Reserve Capacity &rarr;</a>
             </div>
             <div class="feature-item">
                 <h3>Status Transparency</h3>
                 <p class="text-secondary">Track your application status and resource availability in real-time through our centralized moderation board.</p>
                 <a href="#status-checker" class="feature-link">Check Progress &rarr;</a>
             </div>
             @endauth
         </div>
    </section>
</div>
@endsection
