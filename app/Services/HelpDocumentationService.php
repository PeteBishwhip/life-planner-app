<?php

namespace App\Services;

class HelpDocumentationService
{
    /**
     * Get all help topics organized by category
     */
    public function getHelpTopics(): array
    {
        return [
            'getting_started' => [
                'name' => 'Getting Started',
                'icon' => 'rocket',
                'topics' => [
                    [
                        'id' => 'creating-appointment',
                        'title' => 'Creating Your First Appointment',
                        'content' => 'Learn how to create appointments using the appointment manager or quick add feature.',
                        'tags' => ['basics', 'appointments'],
                    ],
                    [
                        'id' => 'calendar-views',
                        'title' => 'Understanding Calendar Views',
                        'content' => 'Switch between Day, Week, Month, and List views to organize your schedule.',
                        'tags' => ['basics', 'navigation'],
                    ],
                    [
                        'id' => 'quick-add',
                        'title' => 'Using Quick Add',
                        'content' => 'Create appointments quickly using natural language like "Meeting tomorrow at 2pm".',
                        'tags' => ['basics', 'quick-add'],
                    ],
                ],
            ],
            'appointments' => [
                'name' => 'Managing Appointments',
                'icon' => 'calendar',
                'topics' => [
                    [
                        'id' => 'recurring-appointments',
                        'title' => 'Setting Up Recurring Appointments',
                        'content' => 'Create daily, weekly, monthly, or yearly recurring appointments with custom patterns.',
                        'tags' => ['appointments', 'recurring'],
                    ],
                    [
                        'id' => 'reminders',
                        'title' => 'Managing Reminders',
                        'content' => 'Set up email and browser notifications for your appointments.',
                        'tags' => ['appointments', 'reminders', 'notifications'],
                    ],
                    [
                        'id' => 'all-day-events',
                        'title' => 'Creating All-Day Events',
                        'content' => 'Mark appointments as all-day events for vacations, holidays, or full-day activities.',
                        'tags' => ['appointments'],
                    ],
                ],
            ],
            'calendars' => [
                'name' => 'Calendar Management',
                'icon' => 'calendar-plus',
                'topics' => [
                    [
                        'id' => 'multiple-calendars',
                        'title' => 'Working with Multiple Calendars',
                        'content' => 'Create and manage personal, business, and custom calendars.',
                        'tags' => ['calendars'],
                    ],
                    [
                        'id' => 'calendar-colors',
                        'title' => 'Customizing Calendar Colors',
                        'content' => 'Assign colors to calendars and appointments for easy visual organization.',
                        'tags' => ['calendars', 'customization'],
                    ],
                    [
                        'id' => 'conflict-detection',
                        'title' => 'Understanding Conflict Detection',
                        'content' => 'Learn how the system detects and prevents scheduling conflicts.',
                        'tags' => ['calendars', 'appointments'],
                    ],
                ],
            ],
            'search_filter' => [
                'name' => 'Search & Filtering',
                'icon' => 'search',
                'topics' => [
                    [
                        'id' => 'searching-appointments',
                        'title' => 'Searching for Appointments',
                        'content' => 'Use the search feature to find appointments by title, description, or location.',
                        'tags' => ['search'],
                    ],
                    [
                        'id' => 'filtering-appointments',
                        'title' => 'Filtering Appointments',
                        'content' => 'Filter appointments by calendar, status, date range, and more.',
                        'tags' => ['search', 'filter'],
                    ],
                    [
                        'id' => 'quick-filters',
                        'title' => 'Using Quick Filters',
                        'content' => 'Access pre-defined filters like Today, This Week, and Upcoming.',
                        'tags' => ['search', 'filter'],
                    ],
                ],
            ],
            'import_export' => [
                'name' => 'Import & Export',
                'icon' => 'download',
                'topics' => [
                    [
                        'id' => 'importing-calendars',
                        'title' => 'Importing Calendars',
                        'content' => 'Import appointments from ICS files, Google Calendar, or Outlook.',
                        'tags' => ['import', 'integration'],
                    ],
                    [
                        'id' => 'exporting-calendars',
                        'title' => 'Exporting Calendars',
                        'content' => 'Export your calendars to ICS, PDF, or CSV formats.',
                        'tags' => ['export'],
                    ],
                ],
            ],
            'preferences' => [
                'name' => 'Settings & Preferences',
                'icon' => 'cog',
                'topics' => [
                    [
                        'id' => 'user-preferences',
                        'title' => 'Customizing Your Preferences',
                        'content' => 'Set your timezone, date format, default view, and notification preferences.',
                        'tags' => ['preferences', 'settings'],
                    ],
                    [
                        'id' => 'notification-settings',
                        'title' => 'Managing Notification Settings',
                        'content' => 'Configure email notifications, browser alerts, and daily digest emails.',
                        'tags' => ['preferences', 'notifications'],
                    ],
                    [
                        'id' => 'keyboard-shortcuts',
                        'title' => 'Using Keyboard Shortcuts',
                        'content' => 'Navigate faster with keyboard shortcuts. Press ? to see all available shortcuts.',
                        'tags' => ['preferences', 'productivity'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get a specific help topic by ID
     */
    public function getTopic(string $topicId): ?array
    {
        $allTopics = $this->getHelpTopics();

        foreach ($allTopics as $category) {
            foreach ($category['topics'] as $topic) {
                if ($topic['id'] === $topicId) {
                    return $topic;
                }
            }
        }

        return null;
    }

    /**
     * Search help topics
     */
    public function searchTopics(string $query): array
    {
        $query = strtolower($query);
        $results = [];
        $allTopics = $this->getHelpTopics();

        foreach ($allTopics as $categoryKey => $category) {
            foreach ($category['topics'] as $topic) {
                $titleMatch = str_contains(strtolower($topic['title']), $query);
                $contentMatch = str_contains(strtolower($topic['content']), $query);
                $tagMatch = collect($topic['tags'])->contains(fn ($tag) => str_contains(strtolower($tag), $query));

                if ($titleMatch || $contentMatch || $tagMatch) {
                    $results[] = array_merge($topic, [
                        'category' => $category['name'],
                    ]);
                }
            }
        }

        return $results;
    }

    /**
     * Get topics by tag
     */
    public function getTopicsByTag(string $tag): array
    {
        $results = [];
        $allTopics = $this->getHelpTopics();

        foreach ($allTopics as $category) {
            foreach ($category['topics'] as $topic) {
                if (in_array($tag, $topic['tags'])) {
                    $results[] = array_merge($topic, [
                        'category' => $category['name'],
                    ]);
                }
            }
        }

        return $results;
    }

    /**
     * Get all unique tags
     */
    public function getAllTags(): array
    {
        $tags = [];
        $allTopics = $this->getHelpTopics();

        foreach ($allTopics as $category) {
            foreach ($category['topics'] as $topic) {
                $tags = array_merge($tags, $topic['tags']);
            }
        }

        return array_values(array_unique($tags));
    }

    /**
     * Get frequently asked questions
     */
    public function getFAQ(): array
    {
        return [
            [
                'question' => 'How do I create a recurring appointment?',
                'answer' => 'When creating an appointment, expand the "Recurrence" section and select your desired pattern (daily, weekly, monthly, or yearly). You can customize the frequency and set an end date.',
                'topic_id' => 'recurring-appointments',
            ],
            [
                'question' => 'Can I import my Google Calendar?',
                'answer' => 'Yes! Go to Settings > Import/Export and select "Import from Google Calendar". Follow the authentication steps to import your events.',
                'topic_id' => 'importing-calendars',
            ],
            [
                'question' => 'How do I set up appointment reminders?',
                'answer' => 'When creating or editing an appointment, you can add multiple reminders. Choose from preset times (5 minutes, 15 minutes, 1 hour, 1 day before) or set a custom time.',
                'topic_id' => 'reminders',
            ],
            [
                'question' => 'What does the conflict detection feature do?',
                'answer' => 'The system automatically detects when you\'re trying to schedule an appointment that overlaps with an existing one. You\'ll receive a warning and can choose to override it if needed.',
                'topic_id' => 'conflict-detection',
            ],
            [
                'question' => 'How can I quickly add an appointment using natural language?',
                'answer' => 'Press "Q" or click the Quick Add button, then type something like "Meeting tomorrow at 2pm" or "Lunch with client next Friday at noon". The system will parse your input and create the appointment.',
                'topic_id' => 'quick-add',
            ],
            [
                'question' => 'Can I change the week start day?',
                'answer' => 'Yes! Go to Settings > Preferences and select your preferred week start day (Sunday, Monday, or Saturday).',
                'topic_id' => 'user-preferences',
            ],
            [
                'question' => 'How do I export my calendar?',
                'answer' => 'Go to Settings > Import/Export and select your desired export format (ICS, PDF, or CSV). You can export a single calendar or all calendars.',
                'topic_id' => 'exporting-calendars',
            ],
            [
                'question' => 'What keyboard shortcuts are available?',
                'answer' => 'Press "?" at any time to see all available keyboard shortcuts. Common ones include: T (today), N (next period), P (previous period), C (create appointment), and Q (quick add).',
                'topic_id' => 'keyboard-shortcuts',
            ],
        ];
    }

    /**
     * Get quick start guide
     */
    public function getQuickStartGuide(): array
    {
        return [
            [
                'step' => 1,
                'title' => 'Create Your First Calendar',
                'description' => 'Set up personal and business calendars to organize your appointments.',
            ],
            [
                'step' => 2,
                'title' => 'Add an Appointment',
                'description' => 'Click the "Create Appointment" button or use Quick Add (press Q) to create your first event.',
            ],
            [
                'step' => 3,
                'title' => 'Set Up Reminders',
                'description' => 'Configure email and browser notifications so you never miss an appointment.',
            ],
            [
                'step' => 4,
                'title' => 'Customize Your View',
                'description' => 'Switch between Day, Week, and Month views to find what works best for you.',
            ],
            [
                'step' => 5,
                'title' => 'Explore Features',
                'description' => 'Try recurring appointments, import your existing calendars, and use keyboard shortcuts for faster navigation.',
            ],
        ];
    }

    /**
     * Get troubleshooting tips
     */
    public function getTroubleshootingTips(): array
    {
        return [
            [
                'issue' => 'Not receiving email notifications',
                'solution' => 'Check your notification settings in Settings > Preferences. Ensure your email address is verified and check your spam folder.',
            ],
            [
                'issue' => 'Calendar not syncing',
                'solution' => 'Refresh the page or clear your browser cache. If the issue persists, try logging out and back in.',
            ],
            [
                'issue' => 'Import failed',
                'solution' => 'Make sure your file is in a supported format (ICS, CSV). Check that the file isn\'t corrupted and try again.',
            ],
            [
                'issue' => 'Keyboard shortcuts not working',
                'solution' => 'Make sure you\'re not in an input field. Keyboard shortcuts are disabled while typing in text boxes.',
            ],
        ];
    }
}
