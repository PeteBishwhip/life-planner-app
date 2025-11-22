<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\UserPreferencesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPreferencesServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserPreferencesService $service;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new UserPreferencesService;
        $this->user = User::factory()->create([
            'timezone' => 'America/New_York',
            'date_format_preference' => 'Y-m-d',
            'time_format_preference' => '24h',
            'default_view' => 'week',
            'theme' => 'dark',
        ]);
    }

    public function test_get_preferences_returns_all_categories(): void
    {
        $preferences = $this->service->getPreferences($this->user);

        $this->assertIsArray($preferences);
        $this->assertArrayHasKey('general', $preferences);
        $this->assertArrayHasKey('calendar', $preferences);
        $this->assertArrayHasKey('notifications', $preferences);
    }

    public function test_get_preferences_returns_user_values(): void
    {
        $preferences = $this->service->getPreferences($this->user);

        $this->assertEquals('America/New_York', $preferences['general']['timezone']);
        $this->assertEquals('Y-m-d', $preferences['general']['date_format']);
        $this->assertEquals('24h', $preferences['general']['time_format']);
        $this->assertEquals('dark', $preferences['general']['theme']);
        $this->assertEquals('week', $preferences['calendar']['default_view']);
    }

    public function test_get_preferences_returns_defaults_for_missing_values(): void
    {
        $newUser = User::factory()->create();

        // Reset to database defaults to simulate a user created before preference columns
        $newUser->update([
            'timezone' => 'UTC',
            'default_view' => 'month',
        ]);

        $preferences = $this->service->getPreferences($newUser);

        $this->assertEquals('UTC', $preferences['general']['timezone']);
        $this->assertEquals('month', $preferences['calendar']['default_view']);
    }

    public function test_update_preferences_updates_timezone(): void
    {
        $result = $this->service->updatePreferences($this->user, [
            'timezone' => 'Europe/London',
        ]);

        $this->assertTrue($result);
        $this->user->refresh();
        $this->assertEquals('Europe/London', $this->user->timezone);
    }

    public function test_update_preferences_updates_date_format(): void
    {
        $result = $this->service->updatePreferences($this->user, [
            'date_format' => 'm/d/Y',
        ]);

        $this->assertTrue($result);
        $this->user->refresh();
        $this->assertEquals('m/d/Y', $this->user->date_format_preference);
    }

    public function test_update_preferences_updates_theme(): void
    {
        $result = $this->service->updatePreferences($this->user, [
            'theme' => 'light',
        ]);

        $this->assertTrue($result);
        $this->user->refresh();
        $this->assertEquals('light', $this->user->theme);
    }

    public function test_update_preferences_updates_default_view(): void
    {
        $result = $this->service->updatePreferences($this->user, [
            'default_view' => 'day',
        ]);

        $this->assertTrue($result);
        $this->user->refresh();
        $this->assertEquals('day', $this->user->default_view);
    }

    public function test_update_preferences_updates_notification_settings(): void
    {
        $result = $this->service->updatePreferences($this->user, [
            'enable_email_notifications' => false,
            'enable_daily_digest' => false,
        ]);

        $this->assertTrue($result);
        $this->user->refresh();
        $this->assertFalse($this->user->enable_email_notifications);
        $this->assertFalse($this->user->enable_daily_digest);
    }

    public function test_update_preferences_updates_multiple_values(): void
    {
        $result = $this->service->updatePreferences($this->user, [
            'timezone' => 'Asia/Tokyo',
            'theme' => 'auto',
            'default_view' => 'month',
            'enable_browser_notifications' => false,
        ]);

        $this->assertTrue($result);
        $this->user->refresh();
        $this->assertEquals('Asia/Tokyo', $this->user->timezone);
        $this->assertEquals('auto', $this->user->theme);
        $this->assertEquals('month', $this->user->default_view);
        $this->assertFalse($this->user->enable_browser_notifications);
    }

    public function test_get_timezone_options_returns_array(): void
    {
        $timezones = $this->service->getTimezoneOptions();

        $this->assertIsArray($timezones);
        $this->assertNotEmpty($timezones);
        $this->assertContains('UTC', $timezones);
        $this->assertContains('America/New_York', $timezones);
        $this->assertContains('Europe/London', $timezones);
    }

    public function test_get_date_format_options_returns_array(): void
    {
        $formats = $this->service->getDateFormatOptions();

        $this->assertIsArray($formats);
        $this->assertNotEmpty($formats);
        $this->assertArrayHasKey('Y-m-d', $formats);
        $this->assertArrayHasKey('m/d/Y', $formats);
    }

    public function test_get_time_format_options_returns_array(): void
    {
        $formats = $this->service->getTimeFormatOptions();

        $this->assertIsArray($formats);
        $this->assertArrayHasKey('12h', $formats);
        $this->assertArrayHasKey('24h', $formats);
    }

    public function test_get_theme_options_returns_array(): void
    {
        $themes = $this->service->getThemeOptions();

        $this->assertIsArray($themes);
        $this->assertArrayHasKey('light', $themes);
        $this->assertArrayHasKey('dark', $themes);
        $this->assertArrayHasKey('auto', $themes);
    }

    public function test_get_view_options_returns_array(): void
    {
        $views = $this->service->getViewOptions();

        $this->assertIsArray($views);
        $this->assertArrayHasKey('day', $views);
        $this->assertArrayHasKey('week', $views);
        $this->assertArrayHasKey('month', $views);
        $this->assertArrayHasKey('list', $views);
    }

    public function test_get_week_start_day_options_returns_array(): void
    {
        $days = $this->service->getWeekStartDayOptions();

        $this->assertIsArray($days);
        $this->assertArrayHasKey('sunday', $days);
        $this->assertArrayHasKey('monday', $days);
        $this->assertArrayHasKey('saturday', $days);
    }

    public function test_get_duration_options_returns_array(): void
    {
        $durations = $this->service->getDurationOptions();

        $this->assertIsArray($durations);
        $this->assertArrayHasKey(15, $durations);
        $this->assertArrayHasKey(30, $durations);
        $this->assertArrayHasKey(60, $durations);
    }

    public function test_reset_to_defaults_resets_all_preferences(): void
    {
        $this->user->update([
            'timezone' => 'Asia/Tokyo',
            'theme' => 'dark',
            'default_view' => 'day',
            'enable_email_notifications' => false,
        ]);

        $result = $this->service->resetToDefaults($this->user);

        $this->assertTrue($result);
        $this->user->refresh();
        $this->assertEquals('UTC', $this->user->timezone);
        $this->assertEquals('light', $this->user->theme);
        $this->assertEquals('month', $this->user->default_view);
        $this->assertTrue($this->user->enable_email_notifications);
    }

    public function test_validate_preferences_accepts_valid_timezone(): void
    {
        $errors = $this->service->validatePreferences([
            'timezone' => 'America/New_York',
        ]);

        $this->assertEmpty($errors);
    }

    public function test_validate_preferences_rejects_invalid_timezone(): void
    {
        $errors = $this->service->validatePreferences([
            'timezone' => 'Invalid/Timezone',
        ]);

        $this->assertArrayHasKey('timezone', $errors);
    }

    public function test_validate_preferences_accepts_valid_view(): void
    {
        $errors = $this->service->validatePreferences([
            'default_view' => 'week',
        ]);

        $this->assertEmpty($errors);
    }

    public function test_validate_preferences_rejects_invalid_view(): void
    {
        $errors = $this->service->validatePreferences([
            'default_view' => 'invalid_view',
        ]);

        $this->assertArrayHasKey('default_view', $errors);
    }

    public function test_validate_preferences_accepts_valid_theme(): void
    {
        $errors = $this->service->validatePreferences([
            'theme' => 'dark',
        ]);

        $this->assertEmpty($errors);
    }

    public function test_validate_preferences_rejects_invalid_theme(): void
    {
        $errors = $this->service->validatePreferences([
            'theme' => 'invalid_theme',
        ]);

        $this->assertArrayHasKey('theme', $errors);
    }

    public function test_validate_preferences_accepts_valid_week_start_day(): void
    {
        $errors = $this->service->validatePreferences([
            'week_start_day' => 'monday',
        ]);

        $this->assertEmpty($errors);
    }

    public function test_validate_preferences_rejects_invalid_week_start_day(): void
    {
        $errors = $this->service->validatePreferences([
            'week_start_day' => 'invalid_day',
        ]);

        $this->assertArrayHasKey('week_start_day', $errors);
    }

    public function test_validate_preferences_accepts_valid_duration(): void
    {
        $errors = $this->service->validatePreferences([
            'default_appointment_duration' => 60,
        ]);

        $this->assertEmpty($errors);
    }

    public function test_validate_preferences_rejects_invalid_duration(): void
    {
        $errors = $this->service->validatePreferences([
            'default_appointment_duration' => -10,
        ]);

        $this->assertArrayHasKey('default_appointment_duration', $errors);
    }

    public function test_export_preferences_returns_all_settings(): void
    {
        $exported = $this->service->exportPreferences($this->user);

        $this->assertIsArray($exported);
        $this->assertArrayHasKey('general', $exported);
        $this->assertArrayHasKey('calendar', $exported);
        $this->assertArrayHasKey('notifications', $exported);
    }

    public function test_import_preferences_updates_user_settings(): void
    {
        $preferences = [
            'general' => [
                'timezone' => 'Europe/Paris',
                'theme' => 'dark',
            ],
            'calendar' => [
                'default_view' => 'day',
            ],
            'notifications' => [
                'enable_daily_digest' => false,
            ],
        ];

        $result = $this->service->importPreferences($this->user, $preferences);

        $this->assertTrue($result);
        $this->user->refresh();
        $this->assertEquals('Europe/Paris', $this->user->timezone);
        $this->assertEquals('dark', $this->user->theme);
        $this->assertEquals('day', $this->user->default_view);
        $this->assertFalse($this->user->enable_daily_digest);
    }

    public function test_import_preferences_validates_before_importing(): void
    {
        $preferences = [
            'general' => [
                'timezone' => 'Invalid/Timezone',
            ],
        ];

        $result = $this->service->importPreferences($this->user, $preferences);

        $this->assertFalse($result);
        $this->user->refresh();
        $this->assertNotEquals('Invalid/Timezone', $this->user->timezone);
    }

    public function test_update_default_reminder_times(): void
    {
        $result = $this->service->updatePreferences($this->user, [
            'default_reminder_times' => [5, 15, 30, 60],
        ]);

        $this->assertTrue($result);
        $this->user->refresh();
        $this->assertEquals([5, 15, 30, 60], $this->user->default_reminder_times);
    }

    public function test_update_daily_digest_time(): void
    {
        $result = $this->service->updatePreferences($this->user, [
            'daily_digest_time' => '08:00:00',
        ]);

        $this->assertTrue($result);
        $this->user->refresh();
        $this->assertEquals('08:00:00', $this->user->daily_digest_time);
    }

    public function test_update_week_start_day(): void
    {
        $result = $this->service->updatePreferences($this->user, [
            'week_start_day' => 'sunday',
        ]);

        $this->assertTrue($result);
        $this->user->refresh();
        $this->assertEquals('sunday', $this->user->week_start_day);
    }

    public function test_update_default_appointment_duration(): void
    {
        $result = $this->service->updatePreferences($this->user, [
            'default_appointment_duration' => 90,
        ]);

        $this->assertTrue($result);
        $this->user->refresh();
        $this->assertEquals(90, $this->user->default_appointment_duration);
    }
}
