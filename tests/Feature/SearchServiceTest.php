<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Calendar;
use App\Models\User;
use App\Services\SearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SearchService $searchService;

    protected User $user;

    protected Calendar $personalCalendar;

    protected Calendar $businessCalendar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->searchService = new SearchService;
        $this->user = User::factory()->create();
        $this->personalCalendar = Calendar::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'personal',
            'name' => 'Personal',
        ]);
        $this->businessCalendar = Calendar::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'business',
            'name' => 'Business',
        ]);
    }

    public function test_search_by_title(): void
    {
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'Team Meeting',
            'start_datetime' => now(),
            'end_datetime' => now()->addHour(),
        ]);

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'Doctor Appointment',
            'start_datetime' => now()->addDay(),
            'end_datetime' => now()->addDay()->addHour(),
        ]);

        $results = $this->searchService->search($this->user->id, ['search' => 'Meeting'])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Team Meeting', $results->first()->title);
    }

    public function test_search_by_description(): void
    {
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'Meeting',
            'description' => 'Discuss quarterly results',
            'start_datetime' => now(),
            'end_datetime' => now()->addHour(),
        ]);

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'Conference',
            'description' => 'Annual tech conference',
            'start_datetime' => now()->addDay(),
            'end_datetime' => now()->addDay()->addHour(),
        ]);

        $results = $this->searchService->search($this->user->id, ['search' => 'quarterly'])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Meeting', $results->first()->title);
    }

    public function test_search_by_location(): void
    {
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'Client Meeting',
            'location' => 'Conference Room A',
            'start_datetime' => now(),
            'end_datetime' => now()->addHour(),
        ]);

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'Team Standup',
            'location' => 'Office',
            'start_datetime' => now()->addDay(),
            'end_datetime' => now()->addDay()->addHour(),
        ]);

        $results = $this->searchService->search($this->user->id, ['location' => 'Conference'])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Client Meeting', $results->first()->title);
    }

    public function test_filter_by_calendar(): void
    {
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'Personal Task',
            'start_datetime' => now(),
            'end_datetime' => now()->addHour(),
        ]);

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->businessCalendar->id,
            'title' => 'Business Meeting',
            'start_datetime' => now()->addDay(),
            'end_datetime' => now()->addDay()->addHour(),
        ]);

        $results = $this->searchService->search($this->user->id, [
            'calendar_id' => $this->businessCalendar->id,
        ])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Business Meeting', $results->first()->title);
    }

    public function test_filter_by_status(): void
    {
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'Upcoming Meeting',
            'status' => 'scheduled',
            'start_datetime' => now(),
            'end_datetime' => now()->addHour(),
        ]);

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'Past Meeting',
            'status' => 'completed',
            'start_datetime' => now()->subDay(),
            'end_datetime' => now()->subDay()->addHour(),
        ]);

        $results = $this->searchService->search($this->user->id, [
            'status' => 'completed',
        ])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Past Meeting', $results->first()->title);
    }

    public function test_filter_by_date_range(): void
    {
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'This Week',
            'start_datetime' => now(),
            'end_datetime' => now()->addHour(),
        ]);

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'Next Month',
            'start_datetime' => now()->addMonth(),
            'end_datetime' => now()->addMonth()->addHour(),
        ]);

        $startDate = now()->startOfWeek();
        $endDate = now()->endOfWeek();

        $results = $this->searchService->search($this->user->id, [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('This Week', $results->first()->title);
    }

    public function test_quick_filter_today(): void
    {
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'Today Appointment',
            'start_datetime' => today()->addHours(10),
            'end_datetime' => today()->addHours(11),
        ]);

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'Tomorrow Appointment',
            'start_datetime' => tomorrow()->addHours(10),
            'end_datetime' => tomorrow()->addHours(11),
        ]);

        $results = $this->searchService->search($this->user->id, [
            'quick_filter' => 'today',
        ])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Today Appointment', $results->first()->title);
    }

    public function test_quick_filter_this_week(): void
    {
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'This Week',
            'start_datetime' => now()->startOfWeek()->addDay(),
            'end_datetime' => now()->startOfWeek()->addDay()->addHour(),
        ]);

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'Next Week',
            'start_datetime' => now()->addWeek(),
            'end_datetime' => now()->addWeek()->addHour(),
        ]);

        $results = $this->searchService->search($this->user->id, [
            'quick_filter' => 'this_week',
        ])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('This Week', $results->first()->title);
    }

    public function test_quick_filter_upcoming(): void
    {
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'Future Meeting',
            'status' => 'scheduled',
            'start_datetime' => now()->addDay(),
            'end_datetime' => now()->addDay()->addHour(),
        ]);

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'Past Meeting',
            'status' => 'scheduled',
            'start_datetime' => now()->subDay(),
            'end_datetime' => now()->subDay()->addHour(),
        ]);

        $results = $this->searchService->search($this->user->id, [
            'quick_filter' => 'upcoming',
        ])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Future Meeting', $results->first()->title);
    }

    public function test_filter_by_recurring(): void
    {
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'Weekly Meeting',
            'recurrence_rule' => ['frequency' => 'weekly'],
            'start_datetime' => now(),
            'end_datetime' => now()->addHour(),
        ]);

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'One-time Event',
            'recurrence_rule' => null,
            'start_datetime' => now()->addDay(),
            'end_datetime' => now()->addDay()->addHour(),
        ]);

        $recurringResults = $this->searchService->search($this->user->id, [
            'is_recurring' => true,
        ])->get();

        $this->assertCount(1, $recurringResults);
        $this->assertEquals('Weekly Meeting', $recurringResults->first()->title);

        $nonRecurringResults = $this->searchService->search($this->user->id, [
            'is_recurring' => false,
        ])->get();

        $this->assertCount(1, $nonRecurringResults);
        $this->assertEquals('One-time Event', $nonRecurringResults->first()->title);
    }

    public function test_filter_by_all_day(): void
    {
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'All Day Event',
            'is_all_day' => true,
            'start_datetime' => today(),
            'end_datetime' => today()->endOfDay(),
        ]);

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'Timed Event',
            'is_all_day' => false,
            'start_datetime' => now(),
            'end_datetime' => now()->addHour(),
        ]);

        $allDayResults = $this->searchService->search($this->user->id, [
            'is_all_day' => true,
        ])->get();

        $this->assertCount(1, $allDayResults);
        $this->assertEquals('All Day Event', $allDayResults->first()->title);
    }

    public function test_combined_filters(): void
    {
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->businessCalendar->id,
            'title' => 'Business Meeting in Conference Room',
            'location' => 'Conference Room A',
            'status' => 'scheduled',
            'start_datetime' => now(),
            'end_datetime' => now()->addHour(),
        ]);

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'Personal Lunch',
            'location' => 'Restaurant',
            'status' => 'scheduled',
            'start_datetime' => now()->addDay(),
            'end_datetime' => now()->addDay()->addHour(),
        ]);

        $results = $this->searchService->search($this->user->id, [
            'search' => 'Meeting',
            'calendar_id' => $this->businessCalendar->id,
            'location' => 'Conference',
            'status' => 'scheduled',
        ])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Business Meeting in Conference Room', $results->first()->title);
    }

    public function test_sorting(): void
    {
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'Third',
            'start_datetime' => now()->addDays(3),
            'end_datetime' => now()->addDays(3)->addHour(),
        ]);

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'First',
            'start_datetime' => now()->addDay(),
            'end_datetime' => now()->addDay()->addHour(),
        ]);

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'Second',
            'start_datetime' => now()->addDays(2),
            'end_datetime' => now()->addDays(2)->addHour(),
        ]);

        $ascResults = $this->searchService->search($this->user->id, [
            'sort_by' => 'start_datetime',
            'sort_direction' => 'asc',
        ])->get();

        $this->assertEquals('First', $ascResults->first()->title);
        $this->assertEquals('Third', $ascResults->last()->title);

        $descResults = $this->searchService->search($this->user->id, [
            'sort_by' => 'start_datetime',
            'sort_direction' => 'desc',
        ])->get();

        $this->assertEquals('Third', $descResults->first()->title);
        $this->assertEquals('First', $descResults->last()->title);
    }

    public function test_search_suggestions(): void
    {
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'Team Meeting',
            'location' => 'Conference Room A',
            'start_datetime' => now(),
            'end_datetime' => now()->addHour(),
        ]);

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'Team Standup',
            'location' => 'Office',
            'start_datetime' => now()->addDay(),
            'end_datetime' => now()->addDay()->addHour(),
        ]);

        $suggestions = $this->searchService->getSearchSuggestions($this->user->id, 'Team');

        $this->assertCount(2, $suggestions);
        $this->assertTrue($suggestions->contains('Team Meeting'));
        $this->assertTrue($suggestions->contains('Team Standup'));
    }

    public function test_filter_statistics(): void
    {
        // Today
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'Today',
            'status' => 'scheduled',
            'start_datetime' => today()->addHours(10),
            'end_datetime' => today()->addHours(11),
        ]);

        // Completed
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'Completed',
            'status' => 'completed',
            'start_datetime' => now()->subDay(),
            'end_datetime' => now()->subDay()->addHour(),
        ]);

        // Recurring
        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'Recurring',
            'status' => 'scheduled',
            'recurrence_rule' => ['frequency' => 'daily'],
            'start_datetime' => now(),
            'end_datetime' => now()->addHour(),
        ]);

        $stats = $this->searchService->getFilterStatistics($this->user->id);

        $this->assertEquals(3, $stats['total']);
        $this->assertGreaterThanOrEqual(1, $stats['today']);
        $this->assertEquals(2, $stats['scheduled']);
        $this->assertEquals(1, $stats['completed']);
        $this->assertEquals(1, $stats['recurring']);
    }

    public function test_user_isolation(): void
    {
        $otherUser = User::factory()->create();
        $otherCalendar = Calendar::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        Appointment::factory()->create([
            'user_id' => $this->user->id,
            'calendar_id' => $this->personalCalendar->id,
            'title' => 'My Appointment',
            'start_datetime' => now(),
            'end_datetime' => now()->addHour(),
        ]);

        Appointment::factory()->create([
            'user_id' => $otherUser->id,
            'calendar_id' => $otherCalendar->id,
            'title' => 'Other User Appointment',
            'start_datetime' => now(),
            'end_datetime' => now()->addHour(),
        ]);

        $results = $this->searchService->search($this->user->id, [])->get();

        $this->assertCount(1, $results);
        $this->assertEquals('My Appointment', $results->first()->title);
    }
}
