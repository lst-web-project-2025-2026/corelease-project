<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Send an in-app notification to a user.
     */
    public function notify(User $user, string $title, string $content): Notification
    {
        return Notification::create([
            'user_id' => $user->id,
            'title' => $title,
            'content' => $content,
            'status' => 'unread',
        ]);
    }

    /**
     * Mark all notifications for a user as read before a certain time.
     */
    public function markAllAsRead(User $user, ?\Carbon\Carbon $before = null): int
    {
        return Notification::where('user_id', $user->id)
            ->where('status', 'unread')
            ->when($before, fn($q) => $q->where('created_at', '<=', $before))
            ->update(['status' => 'read']);
    }

    /**
     * Dismiss a notification.
     */
    public function dismiss(Notification $notification): bool
    {
        return $notification->update(['status' => 'dismissed']);
    }
}
