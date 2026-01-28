<nav class="navbar">
    <div class="container-navbar">
        <div class="nav-main">
            <a href="/" class="brand">
                <x-ui.logo size="32px" />
                <span class="brand-name">Corelease</span>
            </a>

            <div class="nav-links">
                <a href="/catalog" class="nav-link">Resource Catalog</a>
                <a href="/maintenance" class="nav-link">Maintenance Schedule</a>
                @auth
                    <a href="/dashboard" class="nav-link">Dashboard</a>
                @else
                    <a href="/#status-checker" class="nav-link">Check Application Status</a>
                @endauth
            </div>
        </div>

        <div class="nav-actions">
            <!-- Theme Toggle -->
            <button onclick="toggleDarkMode()" class="theme-toggle" title="Toggle Theme">
                <span class="dark-icon">🌙</span>
                <span class="light-icon">☀️</span>
            </button>

            <!-- Accent Picker -->
            <div class="accent-toggle" title="Change Accent">
                <div class="accent-dots">
                    <div class="accent-dot" style="background: #3b82f6;" onclick="setAccent(217, 91, 60)"></div>
                    <div class="accent-dot" style="background: #10b981;" onclick="setAccent(160, 84, 39)"></div>
                    <div class="accent-dot" style="background: #f59e0b;" onclick="setAccent(38, 92, 50)"></div>
                    <div class="accent-dot" style="background: #ef4444;" onclick="setAccent(0, 84, 60)"></div>
                </div>
            </div>

            @auth
                <span class="nav-link user-name">{{ Auth::user()->name }}</span>
                <a href="{{ route('notifications.index') }}" class="theme-toggle" title="Notifications" style="margin: 0 var(--space-sm); width: 2.5rem; height: 2.5rem; display: flex; align-items: center; justify-content: center; position: relative; padding: 0;">
                    <i class="fas fa-bell" style="font-size: 1.1rem;"></i>
                    @php
                        $unreadCount = \App\Models\Notification::where('user_id', Auth::id())->where('status', 'unread')->count();
                    @endphp
                    @if($unreadCount > 0)
                        <span style="position: absolute; top: 0.4rem; right: 0.4rem; width: 0.6rem; height: 0.6rem; background: var(--error); border-radius: 50%; border: 2px solid var(--bg-tertiary); box-shadow: 0 0 8px var(--error);"></span>
                    @endif
                </a>
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <x-ui.button type="submit" variant="secondary" size="sm">Sign Out</x-ui.button>
                </form>
            @else
                <x-ui.button href="/apply" variant="secondary">Request Access</x-ui.button>
                <x-ui.button href="/login">Login</x-ui.button>
            @endauth
        </div>
    </div>
</nav>

<style>
    [data-theme="dark"] .light-icon { display: none; }
    [data-theme="light"] .dark-icon { display: none; }
</style>
