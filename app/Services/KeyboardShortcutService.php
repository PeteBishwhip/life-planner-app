<?php

namespace App\Services;

class KeyboardShortcutService
{
    /**
     * Get all available keyboard shortcuts
     */
    public function getShortcuts(): array
    {
        return [
            'navigation' => [
                [
                    'key' => 't',
                    'description' => 'Go to Today',
                    'action' => 'goToToday',
                ],
                [
                    'key' => 'n',
                    'description' => 'Next period (day/week/month)',
                    'action' => 'nextPeriod',
                ],
                [
                    'key' => 'p',
                    'description' => 'Previous period (day/week/month)',
                    'action' => 'previousPeriod',
                ],
                [
                    'key' => 'd',
                    'description' => 'Switch to Day view',
                    'action' => 'dayView',
                ],
                [
                    'key' => 'w',
                    'description' => 'Switch to Week view',
                    'action' => 'weekView',
                ],
                [
                    'key' => 'm',
                    'description' => 'Switch to Month view',
                    'action' => 'monthView',
                ],
            ],
            'actions' => [
                [
                    'key' => 'c',
                    'description' => 'Create new appointment',
                    'action' => 'createAppointment',
                ],
                [
                    'key' => 'q',
                    'description' => 'Quick add (natural language)',
                    'action' => 'quickAdd',
                ],
                [
                    'key' => 's',
                    'description' => 'Search appointments',
                    'action' => 'search',
                ],
                [
                    'key' => 'r',
                    'description' => 'Refresh calendar',
                    'action' => 'refresh',
                ],
            ],
            'modifiers' => [
                [
                    'key' => '?',
                    'description' => 'Show keyboard shortcuts help',
                    'action' => 'showHelp',
                ],
                [
                    'key' => 'Escape',
                    'description' => 'Close modal/dialog',
                    'action' => 'closeModal',
                ],
            ],
        ];
    }

    /**
     * Get shortcuts grouped by category
     */
    public function getShortcutsByCategory(): array
    {
        $shortcuts = $this->getShortcuts();
        $categorized = [];

        foreach ($shortcuts as $category => $items) {
            $categorized[$category] = [
                'name' => ucfirst($category),
                'shortcuts' => $items,
            ];
        }

        return $categorized;
    }

    /**
     * Get all shortcut keys for frontend consumption
     */
    public function getShortcutMap(): array
    {
        $shortcuts = $this->getShortcuts();
        $map = [];

        foreach ($shortcuts as $category => $items) {
            foreach ($items as $shortcut) {
                $map[$shortcut['key']] = $shortcut['action'];
            }
        }

        return $map;
    }

    /**
     * Check if a key is a valid shortcut
     */
    public function isValidShortcut(string $key): bool
    {
        $map = $this->getShortcutMap();

        return isset($map[$key]);
    }

    /**
     * Get action for a given key
     */
    public function getAction(string $key): ?string
    {
        $map = $this->getShortcutMap();

        return $map[$key] ?? null;
    }
}
