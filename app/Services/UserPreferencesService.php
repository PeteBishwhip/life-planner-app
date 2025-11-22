<?php

namespace App\Services;

use App\Models\User;

class UserPreferencesService
{
    /**
     * Get all preferences for a user
     */
    public function getPreferences(User $user): array
    {
        return [
            'general' => [
                'timezone' => $user->timezone ?? 'UTC',
                'date_format' => $user->date_format_preference ?? 'Y-m-d',
                'time_format' => $user->time_format_preference ?? '24h',
                'theme' => $user->theme ?? 'light',
                'week_start_day' => $user->week_start_day ?? 'monday',
            ],
            'calendar' => [
                'default_view' => $user->default_view ?? 'month',
                'default_appointment_duration' => $user->default_appointment_duration ?? 60,
            ],
            'notifications' => [
                'enable_email_notifications' => $user->enable_email_notifications ?? true,
                'enable_browser_notifications' => $user->enable_browser_notifications ?? true,
                'enable_daily_digest' => $user->enable_daily_digest ?? true,
                'daily_digest_time' => $user->daily_digest_time ?? '06:00:00',
                'default_reminder_times' => $user->default_reminder_times ?? [15, 60],
            ],
        ];
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(User $user, array $preferences): bool
    {
        $updateData = [];

        // General preferences
        if (isset($preferences['timezone'])) {
            $updateData['timezone'] = $preferences['timezone'];
        }

        if (isset($preferences['date_format'])) {
            $updateData['date_format_preference'] = $preferences['date_format'];
        }

        if (isset($preferences['time_format'])) {
            $updateData['time_format_preference'] = $preferences['time_format'];
        }

        if (isset($preferences['theme'])) {
            $updateData['theme'] = $preferences['theme'];
        }

        if (isset($preferences['week_start_day'])) {
            $updateData['week_start_day'] = $preferences['week_start_day'];
        }

        // Calendar preferences
        if (isset($preferences['default_view'])) {
            $updateData['default_view'] = $preferences['default_view'];
        }

        if (isset($preferences['default_appointment_duration'])) {
            $updateData['default_appointment_duration'] = $preferences['default_appointment_duration'];
        }

        // Notification preferences
        if (isset($preferences['enable_email_notifications'])) {
            $updateData['enable_email_notifications'] = $preferences['enable_email_notifications'];
        }

        if (isset($preferences['enable_browser_notifications'])) {
            $updateData['enable_browser_notifications'] = $preferences['enable_browser_notifications'];
        }

        if (isset($preferences['enable_daily_digest'])) {
            $updateData['enable_daily_digest'] = $preferences['enable_daily_digest'];
        }

        if (isset($preferences['daily_digest_time'])) {
            $updateData['daily_digest_time'] = $preferences['daily_digest_time'];
        }

        if (isset($preferences['default_reminder_times'])) {
            $updateData['default_reminder_times'] = $preferences['default_reminder_times'];
        }

        return $user->update($updateData);
    }

    /**
     * Get available timezone options
     */
    public function getTimezoneOptions(): array
    {
        return \DateTimeZone::listIdentifiers();
    }

    /**
     * Get available date format options
     */
    public function getDateFormatOptions(): array
    {
        return [
            'Y-m-d' => '2025-01-15',
            'm/d/Y' => '01/15/2025',
            'd/m/Y' => '15/01/2025',
            'F j, Y' => 'January 15, 2025',
            'M j, Y' => 'Jan 15, 2025',
            'j F Y' => '15 January 2025',
        ];
    }

    /**
     * Get available time format options
     */
    public function getTimeFormatOptions(): array
    {
        return [
            '12h' => '12-hour (2:30 PM)',
            '24h' => '24-hour (14:30)',
        ];
    }

    /**
     * Get available theme options
     */
    public function getThemeOptions(): array
    {
        return [
            'light' => 'Light',
            'dark' => 'Dark',
            'auto' => 'Auto (System)',
        ];
    }

    /**
     * Get available view options
     */
    public function getViewOptions(): array
    {
        return [
            'day' => 'Day View',
            'week' => 'Week View',
            'month' => 'Month View',
            'list' => 'List View',
        ];
    }

    /**
     * Get available week start day options
     */
    public function getWeekStartDayOptions(): array
    {
        return [
            'sunday' => 'Sunday',
            'monday' => 'Monday',
            'saturday' => 'Saturday',
        ];
    }

    /**
     * Get available default appointment duration options (in minutes)
     */
    public function getDurationOptions(): array
    {
        return [
            15 => '15 minutes',
            30 => '30 minutes',
            45 => '45 minutes',
            60 => '1 hour',
            90 => '1.5 hours',
            120 => '2 hours',
        ];
    }

    /**
     * Reset preferences to defaults
     */
    public function resetToDefaults(User $user): bool
    {
        return $user->update([
            'timezone' => 'UTC',
            'date_format_preference' => 'Y-m-d',
            'time_format_preference' => '24h',
            'default_view' => 'month',
            'enable_email_notifications' => true,
            'enable_browser_notifications' => true,
            'enable_daily_digest' => true,
            'daily_digest_time' => '06:00:00',
            'week_start_day' => 'monday',
            'default_appointment_duration' => 60,
            'default_reminder_times' => [15, 60],
            'theme' => 'light',
        ]);
    }

    /**
     * Validate preference values
     */
    public function validatePreferences(array $preferences): array
    {
        $errors = [];

        if (isset($preferences['timezone'])) {
            $validTimezones = $this->getTimezoneOptions();
            if (! in_array($preferences['timezone'], $validTimezones)) {
                $errors['timezone'] = 'Invalid timezone';
            }
        }

        if (isset($preferences['default_view'])) {
            $validViews = array_keys($this->getViewOptions());
            if (! in_array($preferences['default_view'], $validViews)) {
                $errors['default_view'] = 'Invalid view option';
            }
        }

        if (isset($preferences['theme'])) {
            $validThemes = array_keys($this->getThemeOptions());
            if (! in_array($preferences['theme'], $validThemes)) {
                $errors['theme'] = 'Invalid theme option';
            }
        }

        if (isset($preferences['week_start_day'])) {
            $validDays = array_keys($this->getWeekStartDayOptions());
            if (! in_array($preferences['week_start_day'], $validDays)) {
                $errors['week_start_day'] = 'Invalid week start day';
            }
        }

        if (isset($preferences['default_appointment_duration'])) {
            if (! is_numeric($preferences['default_appointment_duration']) || $preferences['default_appointment_duration'] < 1) {
                $errors['default_appointment_duration'] = 'Invalid duration';
            }
        }

        return $errors;
    }

    /**
     * Export preferences (for backup or transfer)
     */
    public function exportPreferences(User $user): array
    {
        return $this->getPreferences($user);
    }

    /**
     * Import preferences (from backup)
     */
    public function importPreferences(User $user, array $preferences): bool
    {
        // Flatten the nested array structure
        $flatPreferences = [];

        if (isset($preferences['general'])) {
            $flatPreferences = array_merge($flatPreferences, $preferences['general']);
        }

        if (isset($preferences['calendar'])) {
            $flatPreferences = array_merge($flatPreferences, $preferences['calendar']);
        }

        if (isset($preferences['notifications'])) {
            $flatPreferences = array_merge($flatPreferences, $preferences['notifications']);
        }

        // Validate before importing
        $errors = $this->validatePreferences($flatPreferences);
        if (! empty($errors)) {
            return false;
        }

        return $this->updatePreferences($user, $flatPreferences);
    }
}
