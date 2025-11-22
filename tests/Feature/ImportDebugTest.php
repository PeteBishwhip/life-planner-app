<?php

namespace Tests\Feature;

use App\Livewire\ImportExportManager;
use App\Models\Calendar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ImportDebugTest extends TestCase
{
    use RefreshDatabase;

    public function test_debug_file_upload_mechanism()
    {
        $user = User::factory()->create();
        $calendar = Calendar::factory()->create(['user_id' => $user->id]);
        $this->actingAs($user);

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

        // Ensure temp directory exists
        Storage::makeDirectory('temp');

        $file = UploadedFile::fake()->createWithContent('test.ics', $icsContent);

        $component = Livewire::test(ImportExportManager::class)
            ->set('importFile', $file)
            ->set('selectedCalendarForImport', $calendar->id)
            ->set('importType', 'ics')
            ->call('import');

        // Debug output
        $result = $component->get('importResult');

        echo "\n\n=== IMPORT RESULT ===\n";
        echo 'Success: '.($result['success'] ? 'true' : 'false')."\n";
        echo 'Message: '.$result['message']."\n";

        if (isset($result['log'])) {
            echo "\n=== IMPORT LOG ===\n";
            echo 'Status: '.$result['log']->status."\n";
            echo 'Records Imported: '.$result['log']->records_imported."\n";
            echo 'Records Failed: '.$result['log']->records_failed."\n";

            if ($result['log']->error_log) {
                echo "\n=== ERROR LOG ===\n";
                print_r($result['log']->error_log);
            }
        }

        // Check what files exist in storage
        echo "\n\n=== STORAGE CONTENTS ===\n";
        $allFiles = Storage::allFiles();
        echo "All files in storage:\n";
        foreach ($allFiles as $f) {
            echo "  - $f\n";
            echo '    Full path: '.Storage::path($f)."\n";
            echo '    Exists: '.(file_exists(Storage::path($f)) ? 'yes' : 'no')."\n";
        }

        // This test should help us understand what's happening
        $this->assertTrue(true);
    }

    public function test_direct_file_storage_and_read()
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

        // Test 1: Direct Storage::put and file_get_contents
        echo "\n\n=== TEST 1: Direct Storage ===\n";
        Storage::makeDirectory('temp');
        $path = 'temp/direct-test.ics';
        Storage::put($path, $icsContent);

        $fullPath = Storage::path($path);
        echo "Stored at: $fullPath\n";
        echo 'File exists: '.(file_exists($fullPath) ? 'yes' : 'no')."\n";

        if (file_exists($fullPath)) {
            $content = file_get_contents($fullPath);
            echo 'Successfully read '.strlen($content)." bytes\n";
            echo 'Content matches: '.($content === $icsContent ? 'yes' : 'no')."\n";
        }

        // Test 2: UploadedFile storage
        echo "\n\n=== TEST 2: UploadedFile ===\n";
        $file = UploadedFile::fake()->createWithContent('uploaded.ics', $icsContent);
        $uploadedPath = $file->store('temp');

        $fullUploadedPath = Storage::path($uploadedPath);
        echo "Uploaded to: $uploadedPath\n";
        echo "Full path: $fullUploadedPath\n";
        echo 'File exists: '.(file_exists($fullUploadedPath) ? 'yes' : 'no')."\n";

        if (file_exists($fullUploadedPath)) {
            $uploadedContent = file_get_contents($fullUploadedPath);
            echo 'Successfully read '.strlen($uploadedContent)." bytes\n";
            echo 'Content matches: '.($uploadedContent === $icsContent ? 'yes' : 'no')."\n";
        }

        // Cleanup
        Storage::deleteDirectory('temp');

        $this->assertTrue(true);
    }
}
