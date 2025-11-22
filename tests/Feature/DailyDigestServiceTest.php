<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Calendar;
use App\Models\User;
use App\Notifications\DailyDigestNotification;
use App\Services\DailyDigestService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class DailyDigestServiceTest extends TestCase
{
    use RefreshDatabase;

    protected DailyDigestService $service;

    protected User $user;

    protected Calendar $calendar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DailyDigestService();
        $this->user = User::factory()->create();
        $this->calendar = Calendar::factory()->create([
            'user_id' => $this->user->id,
        ]);

        Carbon::setTestNow('2025-01-15 08:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_send_daily_digest_with_appointments(): void
    {
        Notification::fake();

        // Create appointments for today
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'Morning Meeting',
            'status' => 'scheduled',
            'start_datetime' => today()->setTime(9, 0),
            'end_datetime' => today()->setTime(10, 0),
        ]);

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'Lunch',
            'status' => 'scheduled',
            'start_datetime' => today()->setTime(12, 0),
            'end_datetime' => today()->setTime(13, 0),
        ]);

        $result = $this->service->sendDailyDigest($this->user);

        $this->assertTrue($result);
        Notification::assertSentTo($this->user, DailyDigestNotification::class);
    }

    public function test_send_daily_digest_without_appointments(): void
    {
        Notification::fake();

        $result = $this->service->sendDailyDigest($this->user);

        $this->assertTrue($result);
        Notification::assertSentTo($this->user, DailyDigestNotification::class);
    }

    public function test_send_daily_digest_only_includes_scheduled_appointments(): void
    {
        Notification::fake();

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'Scheduled Meeting',
            'status' => 'scheduled',
            'start_datetime' => today()->setTime(9, 0),
            'end_datetime' => today()->setTime(10, 0),
        ]);

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'Cancelled Meeting',
            'status' => 'cancelled',
            'start_datetime' => today()->setTime(11, 0),
            'end_datetime' => today()->setTime(12, 0),
        ]);

        $this->service->sendDailyDigest($this->user);

        Notification::assertSentTo($this->user, DailyDigestNotification::class, function ($notification) {
            return $notification->toArray($this->user)['appointment_count'] === 1;
        });
    }

    public function test_send_daily_digest_for_specific_date(): void
    {
        Notification::fake();

        $specificDate = today()->addDays(5);

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'Future Meeting',
            'status' => 'scheduled',
            'start_datetime' => $specificDate->copy()->setTime(9, 0),
            'end_datetime' => $specificDate->copy()->setTime(10, 0),
        ]);

        $result = $this->service->sendDailyDigest($this->user, $specificDate);

        $this->assertTrue($result);
        Notification::assertSentTo($this->user, DailyDigestNotification::class);
    }

    public function test_send_to_all_users(): void
    {
        Notification::fake();

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        $results = $this->service->sendToAllUsers();

        $this->assertEquals(4, $results['total']); // Including setup user
        $this->assertEquals(4, $results['sent']);
        $this->assertEquals(0, $results['failed']);

        Notification::assertSentTo([$user1, $user2, $user3, $this->user], DailyDigestNotification::class);
    }

    public function test_get_digest_preview_with_appointments(): void
    {
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'Morning Meeting',
            'location' => 'Conference Room A',
            'status' => 'scheduled',
            'is_all_day' => false,
            'start_datetime' => today()->setTime(9, 0),
            'end_datetime' => today()->setTime(10, 0),
        ]);

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'All Day Event',
            'status' => 'scheduled',
            'is_all_day' => true,
            'start_datetime' => today()->startOfDay(),
            'end_datetime' => today()->endOfDay(),
        ]);

        $preview = $this->service->getDigestPreview($this->user);

        $this->assertIsArray($preview);
        $this->assertArrayHasKey('date', $preview);
        $this->assertArrayHasKey('appointment_count', $preview);
        $this->assertArrayHasKey('appointments', $preview);
        $this->assertEquals(2, $preview['appointment_count']);
        $this->assertCount(2, $preview['appointments']);

        $firstAppointment = $preview['appointments'][0];
        $this->assertEquals('All Day Event', $firstAppointment['title']);
        $this->assertEquals('All Day', $firstAppointment['time']);

        $secondAppointment = $preview['appointments'][1];
        $this->assertEquals('Morning Meeting', $secondAppointment['title']);
        $this->assertStringContainsString('9:00 AM', $secondAppointment['time']);
        $this->assertEquals('Conference Room A', $secondAppointment['location']);
    }

    public function test_get_digest_preview_without_appointments(): void
    {
        $preview = $this->service->getDigestPreview($this->user);

        $this->assertEquals(0, $preview['appointment_count']);
        $this->assertEmpty($preview['appointments']);
    }

    public function test_digest_includes_calendar_information(): void
    {
        $businessCalendar = Calendar::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Business Calendar',
        ]);

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $businessCalendar->id,
            'title' => 'Business Meeting',
            'status' => 'scheduled',
            'start_datetime' => today()->setTime(9, 0),
            'end_datetime' => today()->setTime(10, 0),
        ]);

        $preview = $this->service->getDigestPreview($this->user);

        $this->assertEquals('Business Calendar', $preview['appointments'][0]['calendar']);
    }

    public function test_digest_orders_appointments_by_time(): void
    {
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'Afternoon Meeting',
            'status' => 'scheduled',
            'start_datetime' => today()->setTime(15, 0),
            'end_datetime' => today()->setTime(16, 0),
        ]);

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'Morning Meeting',
            'status' => 'scheduled',
            'start_datetime' => today()->setTime(9, 0),
            'end_datetime' => today()->setTime(10, 0),
        ]);

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'Lunch',
            'status' => 'scheduled',
            'start_datetime' => today()->setTime(12, 0),
            'end_datetime' => today()->setTime(13, 0),
        ]);

        $preview = $this->service->getDigestPreview($this->user);

        $this->assertEquals('Morning Meeting', $preview['appointments'][0]['title']);
        $this->assertEquals('Lunch', $preview['appointments'][1]['title']);
        $this->assertEquals('Afternoon Meeting', $preview['appointments'][2]['title']);
    }

    public function test_should_send_digest_returns_true(): void
    {
        $result = $this->service->shouldSendDigest($this->user);

        $this->assertTrue($result);
    }

    public function test_get_optimal_send_time_returns_morning_time(): void
    {
        $sendTime = $this->service->getOptimalSendTime($this->user);

        $this->assertEquals(6, $sendTime->hour);
        $this->assertEquals(0, $sendTime->minute);
        $this->assertTrue($sendTime->isToday());
    }

    public function test_digest_only_includes_todays_appointments(): void
    {
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'Today Meeting',
            'status' => 'scheduled',
            'start_datetime' => today()->setTime(9, 0),
            'end_datetime' => today()->setTime(10, 0),
        ]);

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'Tomorrow Meeting',
            'status' => 'scheduled',
            'start_datetime' => tomorrow()->setTime(9, 0),
            'end_datetime' => tomorrow()->setTime(10, 0),
        ]);

        $preview = $this->service->getDigestPreview($this->user);

        $this->assertEquals(1, $preview['appointment_count']);
        $this->assertEquals('Today Meeting', $preview['appointments'][0]['title']);
    }

    public function test_digest_respects_user_isolation(): void
    {
        $otherUser = User::factory()->create();
        $otherCalendar = Calendar::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'title' => 'My Meeting',
            'status' => 'scheduled',
            'start_datetime' => today()->setTime(9, 0),
            'end_datetime' => today()->setTime(10, 0),
        ]);

        Appointment::factory()->create([
            'user_id' => $otherUser->id,
            'calendar_id' => $otherCalendar->id,
            'title' => 'Other User Meeting',
            'status' => 'scheduled',
            'start_datetime' => today()->setTime(9, 0),
            'end_datetime' => today()->setTime(10, 0),
        ]);

        $preview = $this->service->getDigestPreview($this->user);

        $this->assertEquals(1, $preview['appointment_count']);
        $this->assertEquals('My Meeting', $preview['appointments'][0]['title']);
    }
}
