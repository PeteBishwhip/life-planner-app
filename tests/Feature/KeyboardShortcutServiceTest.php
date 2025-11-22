<?php

namespace Tests\Feature;

use App\Services\KeyboardShortcutService;
use Tests\TestCase;

class KeyboardShortcutServiceTest extends TestCase
{
    protected KeyboardShortcutService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new KeyboardShortcutService();
    }

    public function test_get_shortcuts_returns_array(): void
    {
        $shortcuts = $this->service->getShortcuts();

        $this->assertIsArray($shortcuts);
        $this->assertNotEmpty($shortcuts);
    }

    public function test_get_shortcuts_has_navigation_category(): void
    {
        $shortcuts = $this->service->getShortcuts();

        $this->assertArrayHasKey('navigation', $shortcuts);
        $this->assertIsArray($shortcuts['navigation']);
        $this->assertNotEmpty($shortcuts['navigation']);
    }

    public function test_get_shortcuts_has_actions_category(): void
    {
        $shortcuts = $this->service->getShortcuts();

        $this->assertArrayHasKey('actions', $shortcuts);
        $this->assertIsArray($shortcuts['actions']);
        $this->assertNotEmpty($shortcuts['actions']);
    }

    public function test_get_shortcuts_has_modifiers_category(): void
    {
        $shortcuts = $this->service->getShortcuts();

        $this->assertArrayHasKey('modifiers', $shortcuts);
        $this->assertIsArray($shortcuts['modifiers']);
        $this->assertNotEmpty($shortcuts['modifiers']);
    }

    public function test_shortcut_structure_contains_required_fields(): void
    {
        $shortcuts = $this->service->getShortcuts();
        $firstShortcut = $shortcuts['navigation'][0];

        $this->assertArrayHasKey('key', $firstShortcut);
        $this->assertArrayHasKey('description', $firstShortcut);
        $this->assertArrayHasKey('action', $firstShortcut);
    }

    public function test_get_shortcuts_by_category_formats_correctly(): void
    {
        $categorized = $this->service->getShortcutsByCategory();

        $this->assertIsArray($categorized);
        $this->assertNotEmpty($categorized);

        foreach ($categorized as $category => $data) {
            $this->assertArrayHasKey('name', $data);
            $this->assertArrayHasKey('shortcuts', $data);
            $this->assertIsString($data['name']);
            $this->assertIsArray($data['shortcuts']);
        }
    }

    public function test_get_shortcuts_by_category_capitalizes_category_names(): void
    {
        $categorized = $this->service->getShortcutsByCategory();

        $this->assertEquals('Navigation', $categorized['navigation']['name']);
        $this->assertEquals('Actions', $categorized['actions']['name']);
        $this->assertEquals('Modifiers', $categorized['modifiers']['name']);
    }

    public function test_get_shortcut_map_returns_flat_array(): void
    {
        $map = $this->service->getShortcutMap();

        $this->assertIsArray($map);
        $this->assertNotEmpty($map);

        foreach ($map as $key => $action) {
            $this->assertIsString($key);
            $this->assertIsString($action);
        }
    }

    public function test_get_shortcut_map_includes_navigation_shortcuts(): void
    {
        $map = $this->service->getShortcutMap();

        $this->assertArrayHasKey('t', $map);
        $this->assertEquals('goToToday', $map['t']);

        $this->assertArrayHasKey('n', $map);
        $this->assertEquals('nextPeriod', $map['n']);

        $this->assertArrayHasKey('p', $map);
        $this->assertEquals('previousPeriod', $map['p']);
    }

    public function test_get_shortcut_map_includes_view_shortcuts(): void
    {
        $map = $this->service->getShortcutMap();

        $this->assertArrayHasKey('d', $map);
        $this->assertEquals('dayView', $map['d']);

        $this->assertArrayHasKey('w', $map);
        $this->assertEquals('weekView', $map['w']);

        $this->assertArrayHasKey('m', $map);
        $this->assertEquals('monthView', $map['m']);
    }

    public function test_get_shortcut_map_includes_action_shortcuts(): void
    {
        $map = $this->service->getShortcutMap();

        $this->assertArrayHasKey('c', $map);
        $this->assertEquals('createAppointment', $map['c']);

        $this->assertArrayHasKey('q', $map);
        $this->assertEquals('quickAdd', $map['q']);

        $this->assertArrayHasKey('s', $map);
        $this->assertEquals('search', $map['s']);

        $this->assertArrayHasKey('r', $map);
        $this->assertEquals('refresh', $map['r']);
    }

    public function test_get_shortcut_map_includes_help_shortcut(): void
    {
        $map = $this->service->getShortcutMap();

        $this->assertArrayHasKey('?', $map);
        $this->assertEquals('showHelp', $map['?']);
    }

    public function test_get_shortcut_map_includes_escape_shortcut(): void
    {
        $map = $this->service->getShortcutMap();

        $this->assertArrayHasKey('Escape', $map);
        $this->assertEquals('closeModal', $map['Escape']);
    }

    public function test_is_valid_shortcut_returns_true_for_valid_keys(): void
    {
        $this->assertTrue($this->service->isValidShortcut('t'));
        $this->assertTrue($this->service->isValidShortcut('c'));
        $this->assertTrue($this->service->isValidShortcut('?'));
        $this->assertTrue($this->service->isValidShortcut('Escape'));
    }

    public function test_is_valid_shortcut_returns_false_for_invalid_keys(): void
    {
        $this->assertFalse($this->service->isValidShortcut('x'));
        $this->assertFalse($this->service->isValidShortcut('z'));
        $this->assertFalse($this->service->isValidShortcut('1'));
        $this->assertFalse($this->service->isValidShortcut('invalid'));
    }

    public function test_get_action_returns_correct_action_for_valid_key(): void
    {
        $this->assertEquals('goToToday', $this->service->getAction('t'));
        $this->assertEquals('createAppointment', $this->service->getAction('c'));
        $this->assertEquals('showHelp', $this->service->getAction('?'));
        $this->assertEquals('closeModal', $this->service->getAction('Escape'));
    }

    public function test_get_action_returns_null_for_invalid_key(): void
    {
        $this->assertNull($this->service->getAction('x'));
        $this->assertNull($this->service->getAction('invalid'));
        $this->assertNull($this->service->getAction('123'));
    }

    public function test_all_shortcuts_have_unique_keys(): void
    {
        $shortcuts = $this->service->getShortcuts();
        $keys = [];

        foreach ($shortcuts as $category => $items) {
            foreach ($items as $shortcut) {
                $this->assertNotContains($shortcut['key'], $keys, "Duplicate key found: {$shortcut['key']}");
                $keys[] = $shortcut['key'];
            }
        }
    }

    public function test_all_shortcuts_have_unique_actions(): void
    {
        $shortcuts = $this->service->getShortcuts();
        $actions = [];

        foreach ($shortcuts as $category => $items) {
            foreach ($items as $shortcut) {
                $this->assertNotContains($shortcut['action'], $actions, "Duplicate action found: {$shortcut['action']}");
                $actions[] = $shortcut['action'];
            }
        }
    }

    public function test_all_shortcuts_have_non_empty_descriptions(): void
    {
        $shortcuts = $this->service->getShortcuts();

        foreach ($shortcuts as $category => $items) {
            foreach ($items as $shortcut) {
                $this->assertNotEmpty($shortcut['description'], "Empty description for key: {$shortcut['key']}");
                $this->assertIsString($shortcut['description']);
            }
        }
    }

    public function test_shortcut_map_count_matches_total_shortcuts(): void
    {
        $shortcuts = $this->service->getShortcuts();
        $map = $this->service->getShortcutMap();

        $totalShortcuts = 0;
        foreach ($shortcuts as $category => $items) {
            $totalShortcuts += count($items);
        }

        $this->assertCount($totalShortcuts, $map);
    }

    public function test_navigation_shortcuts_include_all_required_actions(): void
    {
        $shortcuts = $this->service->getShortcuts();
        $navActions = array_column($shortcuts['navigation'], 'action');

        $requiredActions = ['goToToday', 'nextPeriod', 'previousPeriod', 'dayView', 'weekView', 'monthView'];

        foreach ($requiredActions as $action) {
            $this->assertContains($action, $navActions, "Missing required navigation action: {$action}");
        }
    }

    public function test_action_shortcuts_include_all_required_actions(): void
    {
        $shortcuts = $this->service->getShortcuts();
        $actionShortcuts = array_column($shortcuts['actions'], 'action');

        $requiredActions = ['createAppointment', 'quickAdd', 'search', 'refresh'];

        foreach ($requiredActions as $action) {
            $this->assertContains($action, $actionShortcuts, "Missing required action: {$action}");
        }
    }
}
