<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NavigationUiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    #[Test]
    public function authenticated_users_see_all_nav_links(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Dashboard');
        $response->assertSee('Calendar');
        $response->assertSee('Calendars');
        $response->assertSee('Appointments');
        $response->assertSee('Import/Export');
        $response->assertSee('Help');
    }

    #[Test]
    public function calendar_dashboard_shows_search_and_quick_add(): void
    {
        $response = $this->actingAs($this->user)->get(route('calendar.dashboard'));

        $response->assertStatus(200);
        // Search input placeholder text
        $response->assertSee('Search appointments...');
        // Quick Add button
        $response->assertSee('Quick Add');
    }
}
