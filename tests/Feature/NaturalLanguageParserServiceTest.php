<?php

namespace Tests\Feature;

use App\Services\NaturalLanguageParserService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NaturalLanguageParserServiceTest extends TestCase
{
    use RefreshDatabase;

    protected NaturalLanguageParserService $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new NaturalLanguageParserService();

        // Set a fixed date for consistent testing
        Carbon::setTestNow('2025-01-15 10:00:00'); // Wednesday
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_parse_simple_title_with_time(): void
    {
        $result = $this->parser->parse('Team meeting at 2pm');

        $this->assertEquals('Team meeting', $result['title']);
        $this->assertEquals(14, $result['start_datetime']->hour);
        $this->assertEquals(0, $result['start_datetime']->minute);
        $this->assertEquals(15, $result['end_datetime']->hour); // Default 1 hour duration
        $this->assertFalse($result['is_all_day']);
    }

    public function test_parse_with_tomorrow(): void
    {
        $result = $this->parser->parse('Meeting tomorrow at 3pm');

        $this->assertEquals('Meeting', $result['title']);
        $this->assertTrue($result['start_datetime']->isTomorrow());
        $this->assertEquals(15, $result['start_datetime']->hour);
    }

    public function test_parse_with_today(): void
    {
        $result = $this->parser->parse('Lunch today at 12:30pm');

        $this->assertEquals('Lunch', $result['title']);
        $this->assertTrue($result['start_datetime']->isToday());
        $this->assertEquals(12, $result['start_datetime']->hour);
        $this->assertEquals(30, $result['start_datetime']->minute);
    }

    public function test_parse_with_specific_day(): void
    {
        $result = $this->parser->parse('Team standup Monday at 9am');

        $this->assertEquals('Team standup', $result['title']);
        $this->assertEquals(Carbon::MONDAY, $result['start_datetime']->dayOfWeek);
        $this->assertEquals(9, $result['start_datetime']->hour);
    }

    public function test_parse_with_next_day(): void
    {
        $result = $this->parser->parse('Conference call next Friday at 10am');

        $this->assertEquals('Conference call', $result['title']);
        $this->assertEquals(Carbon::FRIDAY, $result['start_datetime']->dayOfWeek);
        $this->assertGreaterThan(now(), $result['start_datetime']);
    }

    public function test_parse_24_hour_format(): void
    {
        $result = $this->parser->parse('Team meeting at 14:30');

        $this->assertEquals('Team meeting', $result['title']);
        $this->assertEquals(14, $result['start_datetime']->hour);
        $this->assertEquals(30, $result['start_datetime']->minute);
    }

    public function test_parse_with_duration_hours(): void
    {
        $result = $this->parser->parse('Client meeting tomorrow at 2pm for 2 hours');

        $this->assertEquals('Client meeting', $result['title']);
        $this->assertEquals(14, $result['start_datetime']->hour);
        $this->assertEquals(16, $result['end_datetime']->hour);
    }

    public function test_parse_with_duration_minutes(): void
    {
        $result = $this->parser->parse('Quick standup today at 9am for 15 minutes');

        $this->assertEquals('Quick standup', $result['title']);
        $this->assertEquals(9, $result['start_datetime']->hour);
        $this->assertEquals(0, $result['start_datetime']->minute);
        $this->assertEquals(9, $result['end_datetime']->hour);
        $this->assertEquals(15, $result['end_datetime']->minute);
    }

    public function test_parse_with_fractional_hours(): void
    {
        $result = $this->parser->parse('Workshop tomorrow at 1pm for 1.5 hours');

        $this->assertEquals('Workshop', $result['title']);
        $this->assertEquals(13, $result['start_datetime']->hour);
        $this->assertEquals(14, $result['end_datetime']->hour);
        $this->assertEquals(30, $result['end_datetime']->minute);
    }

    public function test_parse_with_location(): void
    {
        $result = $this->parser->parse('Meeting at Conference Room A tomorrow at 2pm');

        $this->assertEquals('Meeting', $result['title']);
        $this->assertEquals('Conference Room A', $result['location']);
        $this->assertTrue($result['start_datetime']->isTomorrow());
    }

    public function test_parse_with_location_using_in(): void
    {
        $result = $this->parser->parse('Lunch in Downtown Restaurant today at noon');

        $this->assertEquals('Lunch', $result['title']);
        $this->assertEquals('Downtown Restaurant', $result['location']);
    }

    public function test_parse_all_day_event(): void
    {
        $result = $this->parser->parse('Vacation tomorrow');

        $this->assertEquals('Vacation', $result['title']);
        $this->assertTrue($result['is_all_day']);
        $this->assertTrue($result['start_datetime']->isStartOfDay());
        $this->assertTrue($result['end_datetime']->isEndOfDay());
    }

    public function test_parse_all_day_event_with_day(): void
    {
        $result = $this->parser->parse('Conference Friday');

        $this->assertEquals('Conference', $result['title']);
        $this->assertTrue($result['is_all_day']);
        $this->assertEquals(Carbon::FRIDAY, $result['start_datetime']->dayOfWeek);
    }

    public function test_parse_am_time(): void
    {
        $result = $this->parser->parse('Breakfast meeting at 8am');

        $this->assertEquals('Breakfast meeting', $result['title']);
        $this->assertEquals(8, $result['start_datetime']->hour);
    }

    public function test_parse_pm_time(): void
    {
        $result = $this->parser->parse('Afternoon session at 3pm');

        $this->assertEquals('Afternoon session', $result['title']);
        $this->assertEquals(15, $result['start_datetime']->hour);
    }

    public function test_parse_noon(): void
    {
        $result = $this->parser->parse('Lunch at 12pm');

        $this->assertEquals('Lunch', $result['title']);
        $this->assertEquals(12, $result['start_datetime']->hour);
    }

    public function test_parse_midnight(): void
    {
        $result = $this->parser->parse('Event at 12am');

        $this->assertEquals('Event', $result['title']);
        $this->assertEquals(0, $result['start_datetime']->hour);
    }

    public function test_parse_complex_title(): void
    {
        $result = $this->parser->parse('Q1 Planning Session with Marketing Team tomorrow at 10am for 2 hours');

        $this->assertEquals('Q1 Planning Session with Marketing Team', $result['title']);
        $this->assertEquals(10, $result['start_datetime']->hour);
        $this->assertEquals(12, $result['end_datetime']->hour);
    }

    public function test_parse_with_this_week(): void
    {
        $result = $this->parser->parse('Meeting this Friday at 2pm');

        $this->assertEquals('Meeting', $result['title']);
        $this->assertEquals(Carbon::FRIDAY, $result['start_datetime']->dayOfWeek);
    }

    public function test_parse_abbreviations(): void
    {
        $result = $this->parser->parse('Standup mon at 9am for 15 mins');

        $this->assertEquals('Standup', $result['title']);
        $this->assertEquals(Carbon::MONDAY, $result['start_datetime']->dayOfWeek);
        $this->assertEquals(9, $result['start_datetime']->hour);
        $this->assertEquals(15, $result['end_datetime']->diffInMinutes($result['start_datetime']));
    }

    public function test_parse_without_explicit_date_defaults_to_today(): void
    {
        $result = $this->parser->parse('Team meeting at 3pm');

        $this->assertTrue($result['start_datetime']->isToday());
    }

    public function test_parse_uppercase_input(): void
    {
        $result = $this->parser->parse('MEETING TOMORROW AT 2PM');

        $this->assertEquals('MEETING', $result['title']);
        $this->assertTrue($result['start_datetime']->isToday()->addDay()->isSameDay($result['start_datetime']));
    }

    public function test_parse_mixed_case_input(): void
    {
        $result = $this->parser->parse('Client Call ToMoRRoW at 3PM');

        $this->assertEquals('Client Call', $result['title']);
        $this->assertTrue($result['start_datetime']->isToday()->addDay()->isSameDay($result['start_datetime']));
    }

    public function test_parse_minimal_input(): void
    {
        $result = $this->parser->parse('Meeting');

        $this->assertEquals('Meeting', $result['title']);
        $this->assertTrue($result['is_all_day']);
        $this->assertNotNull($result['start_datetime']);
    }

    public function test_parse_empty_string_returns_default_title(): void
    {
        $result = $this->parser->parse('');

        $this->assertEquals('New Appointment', $result['title']);
        $this->assertTrue($result['is_all_day']);
    }

    public function test_parse_only_whitespace_returns_default_title(): void
    {
        $result = $this->parser->parse('   ');

        $this->assertEquals('New Appointment', $result['title']);
        $this->assertTrue($result['is_all_day']);
    }

    public function test_parse_date_format_yyyy_mm_dd(): void
    {
        $result = $this->parser->parse('Meeting on 2025-01-20 at 2pm');

        $this->assertEquals('Meeting', $result['title']);
        $this->assertEquals('2025-01-20', $result['start_datetime']->format('Y-m-d'));
        $this->assertEquals(14, $result['start_datetime']->hour);
    }

    public function test_parse_date_format_mm_dd_yyyy(): void
    {
        $result = $this->parser->parse('Meeting on 01/20/2025 at 3pm');

        $this->assertEquals('Meeting', $result['title']);
        $this->assertEquals('2025-01-20', $result['start_datetime']->format('Y-m-d'));
        $this->assertEquals(15, $result['start_datetime']->hour);
    }

    public function test_parse_full_example_with_all_components(): void
    {
        $result = $this->parser->parse('Team retrospective at Conference Room B next Monday at 2pm for 90 minutes');

        $this->assertEquals('Team retrospective', $result['title']);
        $this->assertEquals('Conference Room B', $result['location']);
        $this->assertEquals(Carbon::MONDAY, $result['start_datetime']->dayOfWeek);
        $this->assertEquals(14, $result['start_datetime']->hour);
        $this->assertEquals(90, $result['end_datetime']->diffInMinutes($result['start_datetime']));
    }

    public function test_get_supported_formats_returns_examples(): void
    {
        $formats = $this->parser->getSupportedFormats();

        $this->assertIsArray($formats);
        $this->assertArrayHasKey('Basic', $formats);
        $this->assertArrayHasKey('With Duration', $formats);
        $this->assertArrayHasKey('With Location', $formats);
        $this->assertArrayHasKey('Day of Week', $formats);
        $this->assertArrayHasKey('All Day Events', $formats);

        $this->assertIsArray($formats['Basic']);
        $this->assertNotEmpty($formats['Basic']);
    }

    public function test_parse_handles_extra_whitespace(): void
    {
        $result = $this->parser->parse('  Meeting   tomorrow   at   2pm  ');

        $this->assertEquals('Meeting', $result['title']);
        $this->assertTrue($result['start_datetime']->isToday()->addDay()->isSameDay($result['start_datetime']));
    }

    public function test_parse_saturday(): void
    {
        $result = $this->parser->parse('Weekend planning Saturday at 10am');

        $this->assertEquals('Weekend planning', $result['title']);
        $this->assertEquals(Carbon::SATURDAY, $result['start_datetime']->dayOfWeek);
        $this->assertEquals(10, $result['start_datetime']->hour);
    }

    public function test_parse_sunday(): void
    {
        $result = $this->parser->parse('Family brunch Sunday at 11am');

        $this->assertEquals('Family brunch', $result['title']);
        $this->assertEquals(Carbon::SUNDAY, $result['start_datetime']->dayOfWeek);
        $this->assertEquals(11, $result['start_datetime']->hour);
    }
}
