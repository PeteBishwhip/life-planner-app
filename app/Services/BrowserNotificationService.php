<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;

class BrowserNotificationService
{
    /**
     * Get unread notifications for a user
     */
    public function getUnreadNotifications(User $user, ?int $limit = 10): Collection
    {
        return $user->unreadNotifications()
            ->limit($limit)
            ->get();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(User $user, string $notificationId): bool
    {
        $notification = $user->notifications()->find($notificationId);

        if ($notification) {
            $notification->markAsRead();

            return true;
        }

        return false;
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(User $user): int
    {
        $count = $user->unreadNotifications()->count();
        $user->unreadNotifications()->update(['read_at' => now()]);

        return $count;
    }

    /**
     * Delete a notification
     */
    public function deleteNotification(User $user, string $notificationId): bool
    {
        $notification = $user->notifications()->find($notificationId);

        if ($notification) {
            $notification->delete();

            return true;
        }

        return false;
    }

    /**
     * Delete all read notifications
     */
    public function deleteReadNotifications(User $user): int
    {
        $count = $user->readNotifications()->count();
        $user->readNotifications()->delete();

        return $count;
    }

    /**
     * Get notification count
     */
    public function getUnreadCount(User $user): int
    {
        return $user->unreadNotifications()->count();
    }

    /**
     * Get formatted notifications for display
     */
    public function getFormattedNotifications(User $user, ?int $limit = 10): Collection
    {
        return $user->notifications()
            ->limit($limit)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at,
                    'formatted_time' => $notification->created_at->diffForHumans(),
                    'is_read' => $notification->read_at !== null,
                ];
            });
    }

    /**
     * Get notification preferences for display
     */
    public function getNotificationPreferences(): array
    {
        return [
            'types' => [
                'appointment_reminder' => 'Appointment Reminders',
                'appointment_created' => 'New Appointments',
                'appointment_updated' => 'Updated Appointments',
                'appointment_cancelled' => 'Cancelled Appointments',
                'daily_digest' => 'Daily Agenda Digest',
            ],
            'channels' => [
                'email' => 'Email',
                'browser' => 'Browser Notifications',
            ],
            'reminder_options' => [
                5 => '5 minutes before',
                15 => '15 minutes before',
                30 => '30 minutes before',
                60 => '1 hour before',
                120 => '2 hours before',
                1440 => '1 day before',
                2880 => '2 days before',
            ],
        ];
    }

    /**
     * Check if browser notifications are supported
     */
    public function areBrowserNotificationsEnabled(User $user): bool
    {
        // In a real implementation, this would check user preferences
        // For now, return true as a default
        return true;
    }

    /**
     * Send test notification to user
     */
    public function sendTestNotification(User $user): void
    {
        $user->notify(new \App\Notifications\TestNotification);
    }
}
