<?php

namespace Tests\Unit;

use App\Models\Appointment;
use App\Models\AppointmentReminder;
use App\Models\Calendar;
use App\Models\User;
use App\Services\ReminderService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReminderServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ReminderService $service;
    protected User $user;
    protected Calendar $calendar;
    protected Appointment $appointment;

    protected function setUp(): void
    {
        parent::setUp();

        // Freeze time for predictable testing
        Carbon::setTestNow('2025-01-15 10:00:00');

        $this->service = new ReminderService();
        $this->user = User::factory()->create();
        $this->calendar = Calendar::factory()->create(['user_id' => $this->user->id]);
        $this->appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'start_datetime' => now()->addHours(2),
            'end_datetime' => now()->addHours(3),
        ]);
    }

    protected function tearDown(): void
    {
        // Unfreeze time after each test
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_creates_reminders_for_appointment(): void
    {
        $reminderMinutes = [15, 30, 60];

        $reminders = $this->service->createReminders($this->appointment, $reminderMinutes);

        $this->assertCount(3, $reminders);
        $this->assertEquals(15, $reminders[0]->reminder_minutes_before);
        $this->assertEquals(30, $reminders[1]->reminder_minutes_before);
        $this->assertEquals(60, $reminders[2]->reminder_minutes_before);
    }

    public function test_gets_due_reminders(): void
    {
        // Create appointment starting in 30 minutes
        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'start_datetime' => now()->addMinutes(30),
            'end_datetime' => now()->addMinutes(90),
        ]);

        // Create reminder for 60 minutes before (should be due)
        AppointmentReminder::factory()->create([
            'appointment_id' => $appointment->id,
            'reminder_minutes_before' => 60,
            'is_sent' => false,
        ]);

        // Create reminder for 15 minutes before (should not be due yet)
        AppointmentReminder::factory()->create([
            'appointment_id' => $appointment->id,
            'reminder_minutes_before' => 15,
            'is_sent' => false,
        ]);

        $dueReminders = $this->service->getDueReminders();

        $this->assertCount(1, $dueReminders);
        $this->assertEquals(60, $dueReminders->first()->reminder_minutes_before);
    }

    public function test_sends_reminder(): void
    {
        Notification::fake();

        $reminder = AppointmentReminder::factory()->create([
            'appointment_id' => $this->appointment->id,
            'reminder_minutes_before' => 30,
            'is_sent' => false,
        ]);

        $result = $this->service->sendReminder($reminder);

        $this->assertTrue($result);
        $reminder->refresh();
        $this->assertTrue($reminder->is_sent);
        $this->assertNotNull($reminder->sent_at);
    }

    public function test_does_not_send_already_sent_reminders(): void
    {
        // Create reminder that was already sent
        $reminder = AppointmentReminder::factory()->create([
            'appointment_id' => $this->appointment->id,
            'reminder_minutes_before' => 30,
            'is_sent' => true,
            'sent_at' => now()->subHour(),
        ]);

        $dueReminders = $this->service->getDueReminders();

        $this->assertCount(0, $dueReminders);
    }

    public function test_updates_reminders_for_appointment(): void
    {
        // Create initial reminders
        $this->service->createReminders($this->appointment, [15, 30]);

        $this->assertEquals(2, $this->appointment->reminders()->count());

        // Update reminders
        $updatedReminders = $this->service->updateReminders($this->appointment, [60, 120]);

        $this->assertCount(2, $updatedReminders);
        $this->assertEquals(2, $this->appointment->reminders()->count());
        $this->assertEquals(60, $this->appointment->reminders()->first()->reminder_minutes_before);
    }

    public function test_deletes_reminders_for_appointment(): void
    {
        $this->service->createReminders($this->appointment, [15, 30, 60]);

        $this->assertEquals(3, $this->appointment->reminders()->count());

        $this->service->deleteReminders($this->appointment);

        $this->assertEquals(0, $this->appointment->reminders()->count());
    }

    public function test_gets_upcoming_reminders_for_user(): void
    {
        // Create future appointment with reminders
        $futureAppointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'start_datetime' => now()->addDays(2),
            'end_datetime' => now()->addDays(2)->addHour(),
        ]);

        AppointmentReminder::factory()->create([
            'appointment_id' => $futureAppointment->id,
            'reminder_minutes_before' => 1440, // 1 day before
            'is_sent' => false,
        ]);

        $upcomingReminders = $this->service->getUpcomingReminders($this->user->id);

        $this->assertGreaterThan(0, $upcomingReminders->count());
    }

    public function test_calculates_reminder_time(): void
    {
        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'start_datetime' => Carbon::parse('2025-01-01 14:00:00'),
            'end_datetime' => Carbon::parse('2025-01-01 15:00:00'),
        ]);

        $reminderTime = $this->service->calculateReminderTime($appointment, 60);

        $this->assertEquals('2025-01-01 13:00:00', $reminderTime->format('Y-m-d H:i:s'));
    }

    public function test_formats_reminder_time(): void
    {
        $this->assertEquals('5 minutes before', $this->service->formatReminderTime(5));
        $this->assertEquals('30 minutes before', $this->service->formatReminderTime(30));
        $this->assertEquals('1 hour before', $this->service->formatReminderTime(60));
        $this->assertEquals('2 hours before', $this->service->formatReminderTime(120));
        $this->assertEquals('1 day before', $this->service->formatReminderTime(1440));
        $this->assertEquals('2 days before', $this->service->formatReminderTime(2880));
    }

    public function test_gets_default_reminder_options(): void
    {
        $options = $this->service->getDefaultReminderOptions();

        $this->assertIsArray($options);
        $this->assertArrayHasKey(5, $options);
        $this->assertArrayHasKey(15, $options);
        $this->assertArrayHasKey(60, $options);
        $this->assertArrayHasKey(1440, $options);
    }

    public function test_checks_if_reminder_is_overdue(): void
    {
        // Create past appointment with unsent reminder
        $pastAppointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'start_datetime' => now()->subHours(2),
            'end_datetime' => now()->subHour(),
        ]);

        $overdueReminder = AppointmentReminder::factory()->create([
            'appointment_id' => $pastAppointment->id,
            'reminder_minutes_before' => 30,
            'is_sent' => false,
        ]);

        $this->assertTrue($this->service->isOverdue($overdueReminder));

        // Create future reminder
        $futureAppointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'start_datetime' => now()->addHours(2),
            'end_datetime' => now()->addHours(3),
        ]);

        $futureReminder = AppointmentReminder::factory()->create([
            'appointment_id' => $futureAppointment->id,
            'reminder_minutes_before' => 30,
            'is_sent' => false,
        ]);

        $this->assertFalse($this->service->isOverdue($futureReminder));
    }

    public function test_gets_reminder_statistics(): void
    {
        $startDate = Carbon::parse('2025-01-01');
        $endDate = Carbon::parse('2025-01-31');

        // Create appointments with reminders
        $appointment1 = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'start_datetime' => Carbon::parse('2025-01-15 10:00:00'),
            'end_datetime' => Carbon::parse('2025-01-15 11:00:00'),
        ]);

        AppointmentReminder::factory()->create([
            'appointment_id' => $appointment1->id,
            'reminder_minutes_before' => 30,
            'notification_type' => 'email',
            'is_sent' => true,
        ]);

        AppointmentReminder::factory()->create([
            'appointment_id' => $appointment1->id,
            'reminder_minutes_before' => 60,
            'notification_type' => 'browser',
            'is_sent' => false,
        ]);

        $stats = $this->service->getReminderStats($this->user->id, $startDate, $endDate);

        $this->assertEquals(2, $stats['total']);
        $this->assertEquals(1, $stats['sent']);
        $this->assertEquals(1, $stats['pending']);
        $this->assertEquals(1, $stats['by_type']['email']);
        $this->assertEquals(1, $stats['by_type']['browser']);
    }

    public function test_processes_due_reminders(): void
    {
        Notification::fake();

        // Create appointment starting in 30 minutes
        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'start_datetime' => now()->addMinutes(30),
            'end_datetime' => now()->addMinutes(90),
        ]);

        // Create due reminder
        AppointmentReminder::factory()->create([
            'appointment_id' => $appointment->id,
            'reminder_minutes_before' => 60,
            'is_sent' => false,
        ]);

        $results = $this->service->processDueReminders();

        $this->assertEquals(1, $results['total']);
        $this->assertEquals(1, $results['sent']);
        $this->assertEquals(0, $results['failed']);
    }
}
