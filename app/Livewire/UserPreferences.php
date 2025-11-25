<?php

namespace App\Livewire;

use App\Services\UserPreferencesService;
use Livewire\Component;

class UserPreferences extends Component
{
    public array $preferences = [];

    public string $timezone = '';

    public string $date_format = '';

    public string $time_format = '';

    public string $default_view = '';

    public string $week_start_day = '';

    public int $default_appointment_duration = 60;

    public string $theme = '';

    public bool $email_notifications_enabled = true;

    public bool $browser_notifications_enabled = false;

    public bool $daily_digest_enabled = false;

    public string $daily_digest_time = '07:00';

    public array $default_reminder_times = [];

    protected UserPreferencesService $preferencesService;

    public function boot(UserPreferencesService $preferencesService): void
    {
        $this->preferencesService = $preferencesService;
    }

    public function mount(): void
    {
        $user = auth()->user();
        $this->preferences = $this->preferencesService->getPreferences($user->id);

        // General preferences
        $this->timezone = $user->timezone ?? 'UTC';
        $this->date_format = $user->date_format_preference ?? 'Y-m-d';
        $this->time_format = $user->time_format_preference ?? '24h';
        $this->default_view = $user->default_view ?? 'month';
        $this->week_start_day = $user->week_start_day ?? 'sunday';
        $this->default_appointment_duration = $user->default_appointment_duration ?? 60;
        $this->theme = $user->theme_preference ?? 'system';

        // Notification preferences
        $this->email_notifications_enabled = $user->email_notifications_enabled ?? true;
        $this->browser_notifications_enabled = $user->browser_notifications_enabled ?? false;
        $this->daily_digest_enabled = $user->daily_digest_enabled ?? false;
        $this->daily_digest_time = $user->daily_digest_time ?? '07:00';
        $this->default_reminder_times = $user->default_reminder_times ?? [15];
    }

    public function save(): void
    {
        $user = auth()->user();

        $this->preferencesService->updatePreferences($user->id, [
            'timezone' => $this->timezone,
            'date_format_preference' => $this->date_format,
            'time_format_preference' => $this->time_format,
            'default_view' => $this->default_view,
            'week_start_day' => $this->week_start_day,
            'default_appointment_duration' => $this->default_appointment_duration,
            'theme_preference' => $this->theme,
            'email_notifications_enabled' => $this->email_notifications_enabled,
            'browser_notifications_enabled' => $this->browser_notifications_enabled,
            'daily_digest_enabled' => $this->daily_digest_enabled,
            'daily_digest_time' => $this->daily_digest_time,
            'default_reminder_times' => $this->default_reminder_times,
        ]);

        session()->flash('message', 'Preferences saved successfully!');
    }

    public function resetToDefaults(): void
    {
        $user = auth()->user();
        $this->preferencesService->resetToDefaults($user->id);
        $this->mount(); // Reload preferences

        session()->flash('message', 'Preferences reset to defaults!');
    }

    public function render()
    {
        $timezoneOptions = $this->preferencesService->getTimezoneOptions();
        $dateFormatOptions = $this->preferencesService->getDateFormatOptions();
        $timeFormatOptions = $this->preferencesService->getTimeFormatOptions();
        $themeOptions = $this->preferencesService->getThemeOptions();
        $viewOptions = $this->preferencesService->getViewOptions();
        $weekStartDayOptions = $this->preferencesService->getWeekStartDayOptions();
        $durationOptions = $this->preferencesService->getDurationOptions();

        return view('livewire.user-preferences', [
            'timezoneOptions' => $timezoneOptions,
            'dateFormatOptions' => $dateFormatOptions,
            'timeFormatOptions' => $timeFormatOptions,
            'themeOptions' => $themeOptions,
            'viewOptions' => $viewOptions,
            'weekStartDayOptions' => $weekStartDayOptions,
            'durationOptions' => $durationOptions,
        ]);
    }
}
