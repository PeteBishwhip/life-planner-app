<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\User;
use App\Services\IcsImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IcsImportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Calendar $calendar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->calendar = Calendar::factory()->create(['user_id' => $this->user->id]);
    }

    #[Test]
    public function it_can_import_a_simple_ics_file()
    {
        // Create a simple ICS file content
        $icsContent = <<<'ICS'
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Life Planner//Test//EN
BEGIN:VEVENT
UID:test-event-1@lifeplanner.test
DTSTART:20250115T100000Z
DTEND:20250115T110000Z
SUMMARY:Test Event
DESCRIPTION:This is a test event
LOCATION:Test Location
END:VEVENT
END:VCALENDAR
ICS;

        // Store the ICS file temporarily
        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $path = $tempDir.'/test-import.ics';
        file_put_contents($path, $icsContent);

        // Import the file
        $service = new IcsImportService($this->user, $this->calendar);
        $importLog = $service->import($path, 'test-import.ics');

        // Assert import was successful
        $this->assertEquals('completed', $importLog->status);
        $this->assertEquals(1, $importLog->records_imported);
        $this->assertEquals(0, $importLog->records_failed);

        // Assert appointment was created
        $this->assertDatabaseHas('appointments', [
            'calendar_id' => $this->calendar->id,
            'user_id' => $this->user->id,
            'title' => 'Test Event',
            'description' => 'This is a test event',
            'location' => 'Test Location',
        ]);

        // Clean up
        unlink($path);
    }

    #[Test]
    public function it_can_import_an_all_day_event()
    {
        $icsContent = <<<'ICS'
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Life Planner//Test//EN
BEGIN:VEVENT
UID:all-day-event@lifeplanner.test
DTSTART;VALUE=DATE:20250120
DTEND;VALUE=DATE:20250121
SUMMARY:All Day Event
END:VEVENT
END:VCALENDAR
ICS;

        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $path = $tempDir.'/all-day.ics';
        file_put_contents($path, $icsContent);

        $service = new IcsImportService($this->user, $this->calendar);
        $importLog = $service->import($path, 'all-day.ics');

        $this->assertEquals('completed', $importLog->status);
        $this->assertEquals(1, $importLog->records_imported);

        $this->assertDatabaseHas('appointments', [
            'calendar_id' => $this->calendar->id,
            'title' => 'All Day Event',
            'is_all_day' => true,
        ]);

        unlink($path);
    }

    #[Test]
    public function it_handles_import_errors_gracefully()
    {
        // Create an ICS file with invalid content
        $icsContent = <<<'ICS'
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Life Planner//Test//EN
BEGIN:VEVENT
UID:invalid-event@lifeplanner.test
SUMMARY:Event without dates
END:VEVENT
END:VCALENDAR
ICS;

        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $path = $tempDir.'/invalid.ics';
        file_put_contents($path, $icsContent);

        $service = new IcsImportService($this->user, $this->calendar);
        $importLog = $service->import($path, 'invalid.ics');

        // Import should complete but with errors recorded
        $this->assertEquals('completed', $importLog->status);
        $this->assertGreaterThan(0, $importLog->records_failed);

        unlink($path);
    }

    #[Test]
    public function it_creates_import_log_with_correct_information()
    {
        $icsContent = <<<'ICS'
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Life Planner//Test//EN
BEGIN:VEVENT
UID:test-event@lifeplanner.test
DTSTART:20250115T100000Z
DTEND:20250115T110000Z
SUMMARY:Test Event
END:VEVENT
END:VCALENDAR
ICS;

        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $path = $tempDir.'/test.ics';
        file_put_contents($path, $icsContent);

        $service = new IcsImportService($this->user, $this->calendar);
        $importLog = $service->import($path, 'test-calendar.ics');

        $this->assertDatabaseHas('import_logs', [
            'user_id' => $this->user->id,
            'filename' => 'test-calendar.ics',
            'import_type' => 'ics',
            'status' => 'completed',
            'records_imported' => 1,
            'records_failed' => 0,
        ]);

        unlink($path);
    }

    #[Test]
    public function it_can_import_multiple_events_from_one_file()
    {
        $icsContent = <<<'ICS'
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Life Planner//Test//EN
BEGIN:VEVENT
UID:event-1@lifeplanner.test
DTSTART:20250115T100000Z
DTEND:20250115T110000Z
SUMMARY:Event 1
END:VEVENT
BEGIN:VEVENT
UID:event-2@lifeplanner.test
DTSTART:20250116T140000Z
DTEND:20250116T150000Z
SUMMARY:Event 2
END:VEVENT
BEGIN:VEVENT
UID:event-3@lifeplanner.test
DTSTART:20250117T090000Z
DTEND:20250117T100000Z
SUMMARY:Event 3
END:VEVENT
END:VCALENDAR
ICS;

        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        $path = $tempDir.'/multiple.ics';
        file_put_contents($path, $icsContent);

        $service = new IcsImportService($this->user, $this->calendar);
        $importLog = $service->import($path, 'multiple.ics');

        $this->assertEquals('completed', $importLog->status);
        $this->assertEquals(3, $importLog->records_imported);
        $this->assertEquals(0, $importLog->records_failed);

        // Assert all appointments were created
        $this->assertEquals(3, $this->calendar->appointments()->count());

        unlink($path);
    }
}
