<?php

namespace Tests\Unit;

use App\Models\Appointment;
use App\Models\Calendar;
use App\Models\User;
use App\Services\RecurrenceService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecurrenceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected RecurrenceService $service;

    protected User $user;

    protected Calendar $calendar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new RecurrenceService;
        $this->user = User::factory()->create();
        $this->calendar = Calendar::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_creates_daily_recurrence_rule(): void
    {
        $rule = $this->service->createRecurrenceRule('daily', 1);

        $this->assertEquals('daily', $rule['frequency']);
        $this->assertEquals(1, $rule['interval']);
    }

    public function test_creates_weekly_recurrence_rule_with_days(): void
    {
        $rule = $this->service->createRecurrenceRule(
            'weekly',
            2,
            null,
            null,
            ['MO', 'WE', 'FR']
        );

        $this->assertEquals('weekly', $rule['frequency']);
        $this->assertEquals(2, $rule['interval']);
        $this->assertEquals(['MO', 'WE', 'FR'], $rule['by_day']);
    }

    public function test_generates_daily_recurrence_instances(): void
    {
        $startDate = Carbon::parse('2025-01-01 10:00:00');
        $endDate = Carbon::parse('2025-01-05 10:00:00');

        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'start_datetime' => $startDate,
            'end_datetime' => $startDate->copy()->addHour(),
            'recurrence_rule' => [
                'frequency' => 'daily',
                'interval' => 1,
                'count' => 5,
            ],
        ]);

        $instances = $this->service->generateInstances(
            $appointment,
            $startDate,
            $endDate
        );

        $this->assertCount(5, $instances);
        $this->assertEquals('2025-01-01 10:00:00', $instances[0]['start_datetime']->format('Y-m-d H:i:s'));
        $this->assertEquals('2025-01-05 10:00:00', $instances[4]['start_datetime']->format('Y-m-d H:i:s'));
    }

    public function test_generates_weekly_recurrence_instances(): void
    {
        $startDate = Carbon::parse('2025-01-06 10:00:00'); // Monday
        $endDate = Carbon::parse('2025-01-31 10:00:00');

        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'start_datetime' => $startDate,
            'end_datetime' => $startDate->copy()->addHour(),
            'recurrence_rule' => [
                'frequency' => 'weekly',
                'interval' => 1,
                'count' => 4,
            ],
        ]);

        $instances = $this->service->generateInstances(
            $appointment,
            $startDate,
            $endDate
        );

        $this->assertCount(4, $instances);
    }

    public function test_generates_monthly_recurrence_instances(): void
    {
        $startDate = Carbon::parse('2025-01-15 10:00:00');
        $endDate = Carbon::parse('2025-04-15 10:00:00');

        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'start_datetime' => $startDate,
            'end_datetime' => $startDate->copy()->addHour(),
            'recurrence_rule' => [
                'frequency' => 'monthly',
                'interval' => 1,
                'count' => 3,
            ],
        ]);

        $instances = $this->service->generateInstances(
            $appointment,
            $startDate,
            $endDate
        );

        $this->assertCount(3, $instances);
        $this->assertEquals(15, Carbon::parse($instances[0]['start_datetime'])->day);
        $this->assertEquals(15, Carbon::parse($instances[1]['start_datetime'])->day);
    }

    public function test_formats_recurrence_rule_to_human_readable(): void
    {
        $rule = ['frequency' => 'daily', 'interval' => 1];
        $formatted = $this->service->formatRecurrenceRule($rule);
        $this->assertEquals('Repeats daily', $formatted);

        $rule = ['frequency' => 'weekly', 'interval' => 2];
        $formatted = $this->service->formatRecurrenceRule($rule);
        $this->assertEquals('Repeats every 2 weeks', $formatted);

        $rule = [
            'frequency' => 'weekly',
            'interval' => 1,
            'by_day' => ['MO', 'WE', 'FR'],
        ];
        $formatted = $this->service->formatRecurrenceRule($rule);
        $this->assertStringContainsString('Monday', $formatted);
        $this->assertStringContainsString('Wednesday', $formatted);
        $this->assertStringContainsString('Friday', $formatted);
    }

    public function test_validates_recurrence_rule(): void
    {
        $validRule = ['frequency' => 'daily', 'interval' => 1];
        $this->assertTrue($this->service->validateRecurrenceRule($validRule));

        $invalidRule = ['frequency' => 'invalid'];
        $this->assertFalse($this->service->validateRecurrenceRule($invalidRule));

        $invalidRule = ['frequency' => 'daily', 'interval' => -1];
        $this->assertFalse($this->service->validateRecurrenceRule($invalidRule));
    }

    public function test_respects_until_date(): void
    {
        $startDate = Carbon::parse('2025-01-01 10:00:00');
        $untilDate = Carbon::parse('2025-01-03 23:59:59');
        $endDate = Carbon::parse('2025-01-10 10:00:00');

        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'start_datetime' => $startDate,
            'end_datetime' => $startDate->copy()->addHour(),
            'recurrence_rule' => [
                'frequency' => 'daily',
                'interval' => 1,
                'until' => $untilDate->toDateTimeString(),
            ],
        ]);

        $instances = $this->service->generateInstances(
            $appointment,
            $startDate,
            $endDate
        );

        // Should only generate instances up to the until date (3 days)
        $this->assertCount(3, $instances);
        $lastInstance = end($instances);
        $this->assertLessThanOrEqual($untilDate, $lastInstance['start_datetime']);
    }

    public function test_handles_non_recurring_appointment(): void
    {
        $startDate = Carbon::parse('2025-01-01 10:00:00');
        $endDate = Carbon::parse('2025-01-10 10:00:00');

        $appointment = Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->calendar->id,
            'start_datetime' => $startDate,
            'end_datetime' => $startDate->copy()->addHour(),
            'recurrence_rule' => null,
        ]);

        $instances = $this->service->generateInstances(
            $appointment,
            $startDate,
            $endDate
        );

        $this->assertCount(1, $instances);
        // For non-recurring appointments, it returns the model itself
        $this->assertInstanceOf(Appointment::class, $instances[0]);
        $this->assertEquals($appointment->id, $instances[0]->id);
    }
}
