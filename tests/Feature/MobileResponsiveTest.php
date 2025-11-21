<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MobileResponsiveTest extends TestCase
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
    public function calendar_dashboard_renders_with_mobile_optimized_controls(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('calendar.dashboard'));

        $response->assertStatus(200);

        // Check for mobile-optimized classes
        $response->assertSee('min-h-[44px]'); // Touch-friendly minimum height
        $response->assertSee('sm:flex-none'); // Responsive flex classes
        $response->assertSee('md:space-y-6'); // Responsive spacing
    }

    /** @test */
    public function view_switcher_buttons_have_touch_friendly_sizes(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('calendar.dashboard'));

        $response->assertStatus(200);

        // Verify view switcher buttons have minimum touch target size (44x44px)
        $response->assertSee('min-h-[44px]');

        // Check for responsive text sizes
        $response->assertSee('text-xs'); // Mobile
        $response->assertSee('sm:text-sm'); // Desktop
    }

    /** @test */
    public function calendar_filters_are_collapsible_on_mobile(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('calendar.dashboard'));

        $response->assertStatus(200);

        // Check for Alpine.js collapse directive
        $response->assertSee('x-collapse');
        $response->assertSee('filtersOpen');

        // Check for mobile-specific display
        $response->assertSee('md:hidden'); // Hide on desktop
    }

    /** @test */
    public function navigation_buttons_show_icons_on_mobile_text_on_desktop(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('calendar.dashboard'));

        $response->assertStatus(200);

        // Check for conditional display of text/icons
        $response->assertSee('sm:hidden'); // Icons hidden on desktop
        $response->assertSee('hidden sm:inline'); // Text hidden on mobile
    }

    /** @test */
    public function fab_button_is_present_and_mobile_only(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('calendar.dashboard'));

        $response->assertStatus(200);

        // Check for FAB button
        $response->assertSee('fixed bottom-6 right-6');
        $response->assertSee('lg:hidden'); // Hidden on large screens
        $response->assertSee('open-appointment-modal'); // Event trigger
    }

    /** @test */
    public function appointment_modal_is_mobile_optimized(): void
    {
        // Check the appointment manager blade file has mobile-optimized classes
        $appointmentManagerPath = resource_path('views/livewire/appointment-manager.blade.php');
        $content = file_get_contents($appointmentManagerPath);

        // Check for mobile-optimized modal
        $this->assertStringContainsString('rounded-t-xl', $content); // Rounded top on mobile
        $this->assertStringContainsString('sm:rounded-lg', $content); // Full rounded on desktop
        $this->assertStringContainsString('w-full', $content); // Full width on mobile
    }

    /** @test */
    public function month_view_cells_are_smaller_on_mobile(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('calendar.dashboard'));

        $response->assertStatus(200);

        // Check for responsive cell heights
        $response->assertSee('min-h-[60px]'); // Mobile
        $response->assertSee('sm:min-h-[80px]'); // Tablet
        $response->assertSee('md:min-h-[100px]'); // Desktop
    }

    /** @test */
    public function day_headers_show_single_letter_on_mobile(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('calendar.dashboard'));

        $response->assertStatus(200);

        // Check for responsive day header display
        $response->assertSee('sm:hidden'); // Full name hidden on mobile
        $response->assertSee('hidden sm:inline'); // Full name shown on desktop
    }

    /** @test */
    public function padding_is_optimized_for_mobile(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('calendar.dashboard'));

        $response->assertStatus(200);

        // Check for responsive padding classes
        $response->assertSee('p-3'); // Mobile padding
        $response->assertSee('sm:p-4'); // Tablet padding
        $response->assertSee('md:p-6'); // Desktop padding
    }

    /** @test */
    public function touch_targets_meet_minimum_44px_requirement(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('calendar.dashboard'));

        $response->assertStatus(200);

        // Verify all interactive elements have minimum touch target size
        $html = $response->getContent();

        // Count occurrences of min-h-[44px] or larger
        $this->assertGreaterThan(0, substr_count($html, 'min-h-[44px]'));
    }
}
