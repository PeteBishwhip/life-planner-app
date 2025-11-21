<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TouchGestureTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Calendar $calendar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->calendar = Calendar::factory()->create([
            'user_id' => $this->user->id,
            'is_default' => true,
        ]);
    }

    /** @test */
    public function touch_gestures_javascript_file_exists(): void
    {
        $touchGesturesPath = resource_path('js/touch-gestures.js');

        $this->assertFileExists($touchGesturesPath);
    }

    /** @test */
    public function touch_gestures_is_imported_in_app_js(): void
    {
        $appJsPath = resource_path('js/app.js');
        $content = file_get_contents($appJsPath);

        $this->assertStringContainsString("import './touch-gestures'", $content);
    }

    /** @test */
    public function swipe_gesture_functions_are_defined(): void
    {
        $touchGesturesPath = resource_path('js/touch-gestures.js');
        $content = file_get_contents($touchGesturesPath);

        // Check for swipe detection functions
        $this->assertStringContainsString('initSwipeGestures', $content);
        $this->assertStringContainsString('touchstart', $content);
        $this->assertStringContainsString('touchend', $content);
        $this->assertStringContainsString('handleSwipe', $content);
    }

    /** @test */
    public function pull_to_refresh_function_is_defined(): void
    {
        $touchGesturesPath = resource_path('js/touch-gestures.js');
        $content = file_get_contents($touchGesturesPath);

        // Check for pull-to-refresh functionality
        $this->assertStringContainsString('initPullToRefresh', $content);
        $this->assertStringContainsString('pullThreshold', $content);
    }

    /** @test */
    public function long_press_detection_is_implemented(): void
    {
        $touchGesturesPath = resource_path('js/touch-gestures.js');
        $content = file_get_contents($touchGesturesPath);

        // Check for long press functionality
        $this->assertStringContainsString('initLongPress', $content);
        $this->assertStringContainsString('longPressDuration', $content);
    }

    /** @test */
    public function swipe_respects_minimum_distance(): void
    {
        $touchGesturesPath = resource_path('js/touch-gestures.js');
        $content = file_get_contents($touchGesturesPath);

        // Check for swipe distance threshold
        $this->assertStringContainsString('minSwipeDistance', $content);
    }

    /** @test */
    public function swipe_excludes_form_inputs(): void
    {
        $touchGesturesPath = resource_path('js/touch-gestures.js');
        $content = file_get_contents($touchGesturesPath);

        // Verify that swipes on input elements are ignored
        $this->assertStringContainsString('INPUT', $content);
        $this->assertStringContainsString('TEXTAREA', $content);
        $this->assertStringContainsString('SELECT', $content);
    }

    /** @test */
    public function gestures_reinitialize_after_livewire_navigation(): void
    {
        $touchGesturesPath = resource_path('js/touch-gestures.js');
        $content = file_get_contents($touchGesturesPath);

        // Check for Livewire navigation listener
        $this->assertStringContainsString('livewire:navigated', $content);
    }

    /** @test */
    public function swipe_right_triggers_previous_navigation(): void
    {
        $touchGesturesPath = resource_path('js/touch-gestures.js');
        $content = file_get_contents($touchGesturesPath);

        // Check that swipe right goes to previous
        $this->assertStringContainsString('[wire\\:click="previous"]', $content);
    }

    /** @test */
    public function swipe_left_triggers_next_navigation(): void
    {
        $touchGesturesPath = resource_path('js/touch-gestures.js');
        $content = file_get_contents($touchGesturesPath);

        // Check that swipe left goes to next
        $this->assertStringContainsString('[wire\\:click="next"]', $content);
    }

    /** @test */
    public function pull_to_refresh_has_visual_feedback(): void
    {
        $touchGesturesPath = resource_path('js/touch-gestures.js');
        $content = file_get_contents($touchGesturesPath);

        // Check for transform feedback
        $this->assertStringContainsString('transform', $content);
        $this->assertStringContainsString('translateY', $content);
    }

    /** @test */
    public function gestures_use_passive_event_listeners(): void
    {
        $touchGesturesPath = resource_path('js/touch-gestures.js');
        $content = file_get_contents($touchGesturesPath);

        // Check for passive event listeners (better performance)
        $this->assertStringContainsString('passive: true', $content);
    }
}
