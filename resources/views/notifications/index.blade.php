@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="notifications-page container" style="padding-top: var(--space-2xl); padding-bottom: var(--space-2xl); min-height: 80vh;">
    <header style="margin-bottom: var(--space-2xl); display: flex; justify-content: space-between; align-items: flex-end;">
        <div>
            <h1 style="font-size: 2.5rem; margin-bottom: var(--space-xs); font-family: var(--font-heading);">Notifications</h1>
            <p class="text-secondary" style="font-size: 1rem;">Stay updated with your latest activities and system alerts.</p>
        </div>
    </header>

    <div style="display: flex; flex-direction: column; gap: var(--space-md); max-width: 950px;">
        @forelse($notifications as $notification)
            <div class="notification-item {{ $notification->status === 'unread' ? 'unread' : '' }}" 
                 id="notification-{{ $notification->id }}"
                 style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 1.5rem; overflow: hidden; transition: all var(--transition-standard); position: relative;">
                
                <div style="padding: var(--space-xl); display: flex; gap: var(--space-xl); align-items: flex-start;">
                    <div class="status-icon" style="width: 52px; height: 52px; border-radius: 1.25rem; background: {{ $notification->status === 'unread' ? 'var(--accent-glow)' : 'var(--bg-tertiary)' }}; border: 1px solid {{ $notification->status === 'unread' ? 'var(--accent-primary)' : 'var(--border-color)' }}; display: flex; align-items: center; justify-content: center; color: {{ $notification->status === 'unread' ? 'var(--accent-primary)' : 'var(--text-muted)' }}; flex-shrink: 0; font-size: 1.25rem;">
                        <i class="fas fa-{{ $notification->status === 'unread' ? 'bell' : 'bell-slash' }}"></i>
                    </div>

                    <div style="flex: 1;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xs);">
                            <h3 style="font-size: 1.25rem; font-weight: 600; color: {{ $notification->status === 'unread' ? 'var(--text-primary)' : 'var(--text-secondary)' }}; margin: 0;">
                                {{ $notification->title }}
                            </h3>
                            <span style="font-size: 0.8125rem; color: var(--text-muted); font-weight: 500;">{{ $notification->created_at->diffForHumans() }}</span>
                        </div>

                        <div class="notification-content" style="cursor: pointer;" onclick="toggleExpand({{ $notification->id }})">
                            <p id="content-short-{{ $notification->id }}" style="color: var(--text-secondary); margin-bottom: 0; line-height: 1.6; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 650px; font-size: 0.9375rem;">
                                {{ $notification->content }}
                            </p>
                            <p id="content-full-{{ $notification->id }}" style="display: none; color: var(--text-secondary); margin-bottom: 0; line-height: 1.7; white-space: pre-wrap; font-size: 0.9375rem;">
                                {{ $notification->content }}
                            </p>
                        </div>
                    </div>

                    <div style="display: flex; gap: var(--space-sm); align-items: center; padding-left: var(--space-md);">
                        <form action="{{ route('notifications.status', $notification) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="{{ $notification->status === 'unread' ? 'read' : 'unread' }}">
                            <button type="submit" class="action-btn" title="Mark as {{ $notification->status === 'unread' ? 'Read' : 'Unread' }}">
                                <i class="fas fa-{{ $notification->status === 'unread' ? 'eye' : 'eye-slash' }}"></i>
                            </button>
                        </form>

                        <form action="{{ route('notifications.status', $notification) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="dismissed">
                            <button type="submit" class="action-btn dismiss" title="Dismiss">
                                <i class="fas fa-times"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div style="text-align: center; padding: var(--space-2xl); background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 1.5rem;">
                <i class="fas fa-bell-slash" style="font-size: 3rem; color: var(--text-muted); margin-bottom: var(--space-md); display: block;"></i>
                <h2 style="font-size: 1.5rem; margin-bottom: var(--space-xs);">No notifications yet</h2>
                <p class="text-secondary">We'll let you know when something important happens.</p>
            </div>
        @endforelse

        <div style="margin-top: var(--space-xl);">
            {{ $notifications->links('vendor.pagination.custom') }}
        </div>
    </div>
</div>

<style>
    .notification-item.unread {
        background: var(--bg-tertiary) !important;
        border-color: var(--accent-primary) !important;
        box-shadow: 0 10px 30px -10px rgba(var(--accent-primary-rgb), 0.15);
    }
    .notification-item:hover {
        border-color: var(--text-muted);
        transform: translateY(-2px);
    }
    .status-icon {
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .notification-item:hover .status-icon {
        transform: scale(1.05);
    }
    .action-btn {
        width: 36px;
        height: 36px;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--bg-tertiary);
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
        transition: all var(--transition-fast);
        cursor: pointer;
    }
    .action-btn:hover {
        background: var(--accent-primary);
        color: white;
        border-color: var(--accent-primary);
    }
    .action-btn.dismiss:hover {
        background: var(--error);
        border-color: var(--error);
    }
</style>

<script>
    function toggleExpand(id) {
        const short = document.getElementById('content-short-' + id);
        const full = document.getElementById('content-full-' + id);
        
        if (short.style.display === 'none') {
            short.style.display = 'block';
            full.style.display = 'none';
        } else {
            short.style.display = 'none';
            full.style.display = 'block';
        }
    }
</script>
@endsection
