<?php

namespace Tests\Feature;

use App\Livewire\ImportExportManager;
use App\Models\Calendar;
use App\Models\ImportLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ImportExportManagerTest extends TestCase
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
    public function it_can_render_the_component()
    {
        $this->actingAs($this->user);

        Livewire::test(ImportExportManager::class)
            ->assertStatus(200)
            ->assertSee('Import')
            ->assertSee('Export')
            ->assertSee('Import Calendar')
            ->assertSee('Export Calendar');
    }

    #[Test]
    public function it_can_open_and_close_import_modal()
    {
        $this->actingAs($this->user);

        Livewire::test(ImportExportManager::class)
            ->assertSet('showImportModal', false)
            ->call('openImportModal')
            ->assertSet('showImportModal', true)
            ->call('closeImportModal')
            ->assertSet('showImportModal', false);
    }

    #[Test]
    public function it_can_open_and_close_export_modal()
    {
        $this->actingAs($this->user);

        Livewire::test(ImportExportManager::class)
            ->assertSet('showExportModal', false)
            ->call('openExportModal')
            ->assertSet('showExportModal', true)
            ->call('closeExportModal')
            ->assertSet('showExportModal', false);
    }

    #[Test]
    public function it_validates_import_file_is_required()
    {
        $this->actingAs($this->user);

        Livewire::test(ImportExportManager::class)
            ->set('selectedCalendarForImport', $this->calendar->id)
            ->call('import')
            ->assertHasErrors(['importFile' => 'required']);
    }

    #[Test]
    public function it_validates_selected_calendar_is_required()
    {
        $this->actingAs($this->user);

        Storage::fake('temp');
        $file = UploadedFile::fake()->create('test.ics', 100);

        Livewire::test(ImportExportManager::class)
            ->set('importFile', $file)
            ->set('selectedCalendarForImport', null)
            ->call('import')
            ->assertHasErrors(['selectedCalendarForImport' => 'required']);
    }

    #[Test]
    public function it_can_import_ics_file()
    {
        $this->actingAs($this->user);

        $icsContent = <<<'ICS'
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Life Planner//Test//EN
BEGIN:VEVENT
UID:test-event@lifeplanner.test
DTSTART:20250115T100000Z
DTEND:20250115T110000Z
SUMMARY:Test Event
DESCRIPTION:Test Description
END:VEVENT
END:VCALENDAR
ICS;

        // Create a real temporary file for testing file I/O
        $tempPath = sys_get_temp_dir().'/test-'.uniqid().'.ics';
        file_put_contents($tempPath, $icsContent);
        $file = new \Illuminate\Http\UploadedFile($tempPath, 'test.ics', 'text/calendar', null, true);

        Livewire::test(ImportExportManager::class)
            ->set('importFile', $file)
            ->set('selectedCalendarForImport', $this->calendar->id)
            ->set('importType', 'ics')
            ->call('import')
            ->assertSet('importResult.success', true);

        // Clean up
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }

        // Assert import log was created
        $this->assertDatabaseHas('import_logs', [
            'user_id' => $this->user->id,
            'import_type' => 'ics',
            'status' => 'completed',
        ]);

        // Assert appointment was created
        $this->assertDatabaseHas('appointments', [
            'calendar_id' => $this->calendar->id,
            'user_id' => $this->user->id,
            'title' => 'Test Event',
        ]);
    }

    #[Test]
    public function it_displays_import_history()
    {
        $this->actingAs($this->user);

        // Create some import logs
        ImportLog::factory()->count(3)->create([
            'user_id' => $this->user->id,
        ]);

        Livewire::test(ImportExportManager::class)
            ->assertSet('showImportHistory', false)
            ->call('toggleImportHistory')
            ->assertSet('showImportHistory', true)
            ->assertCount('importLogs', 3);
    }

    #[Test]
    public function it_loads_import_logs_on_mount()
    {
        $this->actingAs($this->user);

        ImportLog::factory()->count(5)->create([
            'user_id' => $this->user->id,
        ]);

        $component = Livewire::test(ImportExportManager::class);

        $this->assertCount(5, $component->get('importLogs'));
    }

    #[Test]
    public function it_sets_default_calendar_for_import_on_mount()
    {
        $this->actingAs($this->user);

        Livewire::test(ImportExportManager::class)
            ->assertSet('selectedCalendarForImport', $this->calendar->id);
    }

    #[Test]
    public function it_displays_all_user_calendars()
    {
        $this->actingAs($this->user);

        $calendar2 = Calendar::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Second Calendar',
        ]);

        Livewire::test(ImportExportManager::class)
            ->call('openExportModal')
            ->assertSee($this->calendar->name)
            ->assertSee($calendar2->name);
    }

    #[Test]
    public function it_handles_import_errors_gracefully()
    {
        $this->actingAs($this->user);

        // Create an invalid ICS file
        $icsContent = 'INVALID ICS CONTENT';

        // Create a real temporary file for testing file I/O
        $tempPath = sys_get_temp_dir().'/invalid-'.uniqid().'.ics';
        file_put_contents($tempPath, $icsContent);
        $file = new \Illuminate\Http\UploadedFile($tempPath, 'invalid.ics', 'text/calendar', null, true);

        Livewire::test(ImportExportManager::class)
            ->set('importFile', $file)
            ->set('selectedCalendarForImport', $this->calendar->id)
            ->set('importType', 'ics')
            ->call('import')
            ->assertSet('importResult.success', true); // Completes but with 0 records

        // Clean up
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }

    #[Test]
    public function it_emits_event_after_successful_import()
    {
        $this->actingAs($this->user);

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

        // Create a real temporary file for testing file I/O
        $tempPath = sys_get_temp_dir().'/test-'.uniqid().'.ics';
        file_put_contents($tempPath, $icsContent);
        $file = new \Illuminate\Http\UploadedFile($tempPath, 'test.ics', 'text/calendar', null, true);

        Livewire::test(ImportExportManager::class)
            ->set('importFile', $file)
            ->set('selectedCalendarForImport', $this->calendar->id)
            ->set('importType', 'ics')
            ->call('import')
            ->assertDispatched('appointments-updated');

        // Clean up
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }

    #[Test]
    public function it_only_shows_user_own_calendars()
    {
        $this->actingAs($this->user);

        $otherUser = User::factory()->create();
        $otherCalendar = Calendar::factory()->create([
            'user_id' => $otherUser->id,
            'name' => 'Other User Calendar',
        ]);

        Livewire::test(ImportExportManager::class)
            ->call('openExportModal')
            ->assertSee($this->calendar->name)
            ->assertDontSee($otherCalendar->name);
    }

    #[Test]
    public function it_limits_import_logs_to_recent_10()
    {
        $this->actingAs($this->user);

        // Create 15 import logs
        ImportLog::factory()->count(15)->create([
            'user_id' => $this->user->id,
        ]);

        $component = Livewire::test(ImportExportManager::class);

        // Should only load the 10 most recent
        $this->assertCount(10, $component->get('importLogs'));
    }
}
