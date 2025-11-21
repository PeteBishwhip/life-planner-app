<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PWAFunctionalityTest extends TestCase
{
    use RefreshDatabase; // Needed for app_layout tests that require authentication

    /** @test */
    public function manifest_file_exists_and_is_accessible(): void
    {
        $manifestPath = public_path('manifest.json');

        $this->assertFileExists($manifestPath);

        // Verify it's valid JSON
        $content = file_get_contents($manifestPath);
        $this->assertJson($content);
    }

    /** @test */
    public function manifest_contains_required_fields(): void
    {
        $manifestPath = public_path('manifest.json');
        $content = file_get_contents($manifestPath);
        $manifest = json_decode($content, true);

        // Check required PWA manifest fields
        $this->assertArrayHasKey('name', $manifest);
        $this->assertArrayHasKey('short_name', $manifest);
        $this->assertArrayHasKey('start_url', $manifest);
        $this->assertArrayHasKey('display', $manifest);
        $this->assertArrayHasKey('theme_color', $manifest);
        $this->assertArrayHasKey('background_color', $manifest);
        $this->assertArrayHasKey('icons', $manifest);

        // Verify values
        $this->assertEquals('Life Planner', $manifest['name']);
        $this->assertEquals('standalone', $manifest['display']);
        $this->assertIsArray($manifest['icons']);
    }

    /** @test */
    public function manifest_has_multiple_icon_sizes(): void
    {
        $manifestPath = public_path('manifest.json');
        $content = file_get_contents($manifestPath);
        $manifest = json_decode($content, true);
        $icons = $manifest['icons'];

        // Verify multiple icon sizes for different devices
        $this->assertGreaterThanOrEqual(5, count($icons));

        // Check for common sizes
        $sizes = array_column($icons, 'sizes');
        $this->assertContains('192x192', $sizes);
        $this->assertContains('512x512', $sizes);
    }

    /** @test */
    public function manifest_has_shortcuts(): void
    {
        $manifestPath = public_path('manifest.json');
        $content = file_get_contents($manifestPath);
        $manifest = json_decode($content, true);

        $this->assertArrayHasKey('shortcuts', $manifest);
        $this->assertIsArray($manifest['shortcuts']);
        $this->assertGreaterThan(0, count($manifest['shortcuts']));
    }

    /** @test */
    public function service_worker_file_exists(): void
    {
        $serviceWorkerPath = public_path('service-worker.js');

        $this->assertFileExists($serviceWorkerPath);
    }

    /** @test */
    public function service_worker_has_cache_strategy(): void
    {
        $serviceWorkerPath = public_path('service-worker.js');
        $content = file_get_contents($serviceWorkerPath);

        // Check for caching functionality
        $this->assertStringContainsString('CACHE_NAME', $content);
        $this->assertStringContainsString('caches.open', $content);
        $this->assertStringContainsString('cache.addAll', $content);
    }

    /** @test */
    public function service_worker_handles_install_event(): void
    {
        $serviceWorkerPath = public_path('service-worker.js');
        $content = file_get_contents($serviceWorkerPath);

        $this->assertStringContainsString('addEventListener(\'install\'', $content);
        $this->assertStringContainsString('PRECACHE_ASSETS', $content);
    }

    /** @test */
    public function service_worker_handles_activate_event(): void
    {
        $serviceWorkerPath = public_path('service-worker.js');
        $content = file_get_contents($serviceWorkerPath);

        $this->assertStringContainsString('addEventListener(\'activate\'', $content);
        $this->assertStringContainsString('caches.keys', $content);
    }

    /** @test */
    public function service_worker_handles_fetch_event(): void
    {
        $serviceWorkerPath = public_path('service-worker.js');
        $content = file_get_contents($serviceWorkerPath);

        $this->assertStringContainsString('addEventListener(\'fetch\'', $content);
        $this->assertStringContainsString('caches.match', $content);
    }

    /** @test */
    public function offline_page_exists(): void
    {
        $offlinePath = public_path('offline.html');

        $this->assertFileExists($offlinePath);

        $content = file_get_contents($offlinePath);
        $this->assertStringContainsString('offline', strtolower($content));
    }

    /** @test */
    public function offline_page_has_retry_functionality(): void
    {
        $offlinePath = public_path('offline.html');
        $content = file_get_contents($offlinePath);

        $this->assertStringContainsString('Retry', $content);
        $this->assertStringContainsString('window.location.reload', $content);
    }

    /** @test */
    public function app_layout_includes_pwa_meta_tags(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('calendar.dashboard'));

        $response->assertStatus(200);

        // Check for PWA meta tags
        $response->assertSee('theme-color', false);
        $response->assertSee('apple-mobile-web-app-capable', false);
        $response->assertSee('manifest.json', false);
    }

    /** @test */
    public function app_layout_registers_service_worker(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('calendar.dashboard'));

        $response->assertStatus(200);

        // Check for service worker registration script
        $response->assertSee('serviceWorker', false);
        $response->assertSee('register(\'/service-worker.js\')', false);
    }

    /** @test */
    public function service_worker_uses_network_first_strategy(): void
    {
        $serviceWorkerPath = public_path('service-worker.js');
        $content = file_get_contents($serviceWorkerPath);

        // Verify network-first, fallback-to-cache strategy
        $this->assertStringContainsString('fetch(event.request)', $content);
        $this->assertStringContainsString('.catch', $content);
    }

    /** @test */
    public function service_worker_handles_offline_navigation(): void
    {
        $serviceWorkerPath = public_path('service-worker.js');
        $content = file_get_contents($serviceWorkerPath);

        // Check for offline page fallback
        $this->assertStringContainsString('OFFLINE_URL', $content);
        $this->assertStringContainsString('mode === \'navigate\'', $content);
    }

    /** @test */
    public function manifest_specifies_portrait_orientation(): void
    {
        $manifestPath = public_path('manifest.json');
        $content = file_get_contents($manifestPath);
        $manifest = json_decode($content, true);

        $this->assertArrayHasKey('orientation', $manifest);
        $this->assertEquals('portrait-primary', $manifest['orientation']);
    }

    /** @test */
    public function manifest_has_productivity_category(): void
    {
        $manifestPath = public_path('manifest.json');
        $content = file_get_contents($manifestPath);
        $manifest = json_decode($content, true);

        $this->assertArrayHasKey('categories', $manifest);
        $this->assertContains('productivity', $manifest['categories']);
    }
}
