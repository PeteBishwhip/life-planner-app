<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Str;

class NaturalLanguageParserService
{
    protected array $timePatterns = [
        // Time patterns like "2pm", "14:00", "2:30 PM"
        '/\b(\d{1,2})(?::(\d{2}))?\s*(am|pm|AM|PM)\b/' => 'parseTime12Hour',
        '/\b(\d{1,2}):(\d{2})\b/' => 'parseTime24Hour',
        '/\bat\s+(\d{1,2})\s*(am|pm|AM|PM)\b/' => 'parseTimeWithAt',
    ];

    protected array $datePatterns = [
        // Relative dates
        'today' => 0,
        'tomorrow' => 1,
        'day after tomorrow' => 2,
        'next week' => 7,
    ];

    protected array $dayOfWeekPatterns = [
        'monday' => Carbon::MONDAY,
        'tuesday' => Carbon::TUESDAY,
        'wednesday' => Carbon::WEDNESDAY,
        'thursday' => Carbon::THURSDAY,
        'friday' => Carbon::FRIDAY,
        'saturday' => Carbon::SATURDAY,
        'sunday' => Carbon::SUNDAY,
        'mon' => Carbon::MONDAY,
        'tue' => Carbon::TUESDAY,
        'wed' => Carbon::WEDNESDAY,
        'thu' => Carbon::THURSDAY,
        'fri' => Carbon::FRIDAY,
        'sat' => Carbon::SATURDAY,
        'sun' => Carbon::SUNDAY,
    ];

    protected array $durationPatterns = [
        '/for\s+(\d+)\s*(?:hour|hr|hours|hrs)\b/i' => 'hours',
        '/for\s+(\d+)\s*(?:minute|min|minutes|mins)\b/i' => 'minutes',
        '/for\s+(\d+\.?\d*)\s*(?:hour|hr|hours|hrs)\b/i' => 'hours',
    ];

    /**
     * Parse natural language input into appointment data
     */
    public function parse(string $input): array
    {
        $input = trim($input);
        $result = [
            'title' => null,
            'start_datetime' => null,
            'end_datetime' => null,
            'is_all_day' => false,
            'location' => null,
        ];

        // Extract location (text after " at " or " in ")
        if (preg_match('/\b(?:at|in)\s+([A-Z][A-Za-z0-9\s,]+?)(?:\s+(?:on|from|at|for|tomorrow|today|next|this)|\s*$)/i', $input, $locationMatch)) {
            $potentialLocation = trim($locationMatch[1]);
            // Make sure it's not a time expression
            if (! preg_match('/^\d{1,2}(?::\d{2})?\s*(?:am|pm)?$/i', $potentialLocation)) {
                $result['location'] = $potentialLocation;
                $input = str_replace($locationMatch[0], ' ', $input);
            }
        }

        // Parse date
        $date = $this->parseDate($input);
        if (! $date) {
            $date = today();
        }

        // Parse time
        $time = $this->parseTime($input);

        // Parse duration
        $duration = $this->parseDuration($input);

        // Set start and end times
        if ($time) {
            $result['start_datetime'] = $date->copy()->setTimeFrom($time);
            if ($duration) {
                $result['end_datetime'] = $result['start_datetime']->copy()->add($duration);
            } else {
                // Default to 1 hour if no duration specified
                $result['end_datetime'] = $result['start_datetime']->copy()->addHour();
            }
        } else {
            // No specific time, make it all-day
            $result['is_all_day'] = true;
            $result['start_datetime'] = $date->copy()->startOfDay();
            $result['end_datetime'] = $date->copy()->endOfDay();
        }

        // Extract title (everything that's left after removing date/time/location info)
        $title = $this->extractTitle($input);
        $result['title'] = $title ?: 'New Appointment';

        return $result;
    }

    /**
     * Parse date from natural language
     */
    protected function parseDate(string $input): ?Carbon
    {
        $lowerInput = strtolower($input);

        // Check for specific date patterns (YYYY-MM-DD, MM/DD/YYYY, etc.)
        if (preg_match('/\b(\d{4})-(\d{1,2})-(\d{1,2})\b/', $input, $matches)) {
            return Carbon::createFromDate($matches[1], $matches[2], $matches[3]);
        }

        if (preg_match('/\b(\d{1,2})\/(\d{1,2})\/(\d{4})\b/', $input, $matches)) {
            return Carbon::createFromDate($matches[3], $matches[1], $matches[2]);
        }

        // Check for relative dates
        foreach ($this->datePatterns as $pattern => $daysToAdd) {
            if (str_contains($lowerInput, $pattern)) {
                return now()->addDays($daysToAdd)->startOfDay();
            }
        }

        // Check for "next [day of week]" or "this [day of week]"
        foreach ($this->dayOfWeekPatterns as $day => $dayNumber) {
            if (preg_match("/\b(?:next|this)\s+{$day}\b/i", $lowerInput)) {
                $modifier = str_contains($lowerInput, 'next') ? 'next' : 'this';
                return Carbon::parse("{$modifier} {$day}")->startOfDay();
            }
        }

        // Check for standalone day of week (assume next occurrence)
        foreach ($this->dayOfWeekPatterns as $day => $dayNumber) {
            if (preg_match("/\b{$day}\b/i", $lowerInput)) {
                $targetDate = now()->next($dayNumber);
                // If the day is today or already passed this week, get next week's occurrence
                if ($targetDate->isSameDay(now()) || $targetDate->isPast()) {
                    $targetDate = now()->next($dayNumber);
                }
                return $targetDate->startOfDay();
            }
        }

        return null;
    }

    /**
     * Parse time from natural language
     */
    protected function parseTime(string $input): ?Carbon
    {
        // 12-hour format with am/pm
        if (preg_match('/\b(\d{1,2})(?::(\d{2}))?\s*(am|pm)\b/i', $input, $matches)) {
            $hour = (int) $matches[1];
            $minute = isset($matches[2]) ? (int) $matches[2] : 0;
            $meridiem = strtolower($matches[3]);

            if ($meridiem === 'pm' && $hour !== 12) {
                $hour += 12;
            } elseif ($meridiem === 'am' && $hour === 12) {
                $hour = 0;
            }

            return now()->setTime($hour, $minute);
        }

        // 24-hour format
        if (preg_match('/\b(\d{1,2}):(\d{2})\b/', $input, $matches)) {
            $hour = (int) $matches[1];
            $minute = (int) $matches[2];

            if ($hour >= 0 && $hour <= 23 && $minute >= 0 && $minute <= 59) {
                return now()->setTime($hour, $minute);
            }
        }

        // Just hour with "at" (e.g., "at 2")
        if (preg_match('/\bat\s+(\d{1,2})\b/', $input, $matches)) {
            $hour = (int) $matches[1];
            // Assume PM if hour is between 1-7, AM if 8-11
            if ($hour >= 1 && $hour <= 7) {
                $hour += 12;
            }
            return now()->setTime($hour, 0);
        }

        return null;
    }

    /**
     * Parse duration from natural language
     */
    protected function parseDuration(string $input): ?\DateInterval
    {
        // Check for "from X to Y" time range
        if (preg_match('/from\s+(\d{1,2})(?::(\d{2}))?\s*(am|pm)?\s+to\s+(\d{1,2})(?::(\d{2}))?\s*(am|pm)?/i', $input, $matches)) {
            return null; // This will be handled separately in the full time range parsing
        }

        // Check for explicit duration
        if (preg_match('/for\s+(\d+)\s*(?:hour|hr|hours|hrs)\b/i', $input, $matches)) {
            $hours = (int) $matches[1];
            return new \DateInterval("PT{$hours}H");
        }

        if (preg_match('/for\s+(\d+)\s*(?:minute|min|minutes|mins)\b/i', $input, $matches)) {
            $minutes = (int) $matches[1];
            return new \DateInterval("PT{$minutes}M");
        }

        // Check for fractional hours like "1.5 hours"
        if (preg_match('/for\s+(\d+\.?\d*)\s*(?:hour|hr|hours|hrs)\b/i', $input, $matches)) {
            $hours = (float) $matches[1];
            $totalMinutes = (int) ($hours * 60);
            return new \DateInterval("PT{$totalMinutes}M");
        }

        return null;
    }

    /**
     * Extract the title by removing date/time/location keywords
     */
    protected function extractTitle(string $input): string
    {
        $title = $input;

        // Remove common temporal keywords
        $temporalKeywords = [
            'today', 'tomorrow', 'yesterday', 'next week',
            'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday',
            'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun',
            'next', 'this', 'last',
        ];

        foreach ($temporalKeywords as $keyword) {
            $title = preg_replace('/\b' . $keyword . '\b/i', '', $title);
        }

        // Remove time expressions
        $title = preg_replace('/\b(\d{1,2})(?::(\d{2}))?\s*(am|pm)\b/i', '', $title);
        $title = preg_replace('/\bat\s+(\d{1,2})\b/', '', $title);
        $title = preg_replace('/\b(\d{1,2}):(\d{2})\b/', '', $title);

        // Remove duration expressions
        $title = preg_replace('/for\s+(\d+\.?\d*)\s*(?:hour|hr|hours|hrs|minute|min|minutes|mins)\b/i', '', $title);

        // Remove location markers
        $title = preg_replace('/\b(?:at|in)\s+[A-Z][A-Za-z0-9\s,]+/i', '', $title);

        // Remove date patterns
        $title = preg_replace('/\b(\d{4})-(\d{1,2})-(\d{1,2})\b/', '', $title);
        $title = preg_replace('/\b(\d{1,2})\/(\d{1,2})\/(\d{4})\b/', '', $title);

        // Clean up multiple spaces and trim
        $title = preg_replace('/\s+/', ' ', $title);
        $title = trim($title);

        return $title;
    }

    /**
     * Get examples of supported formats
     */
    public function getSupportedFormats(): array
    {
        return [
            'Basic' => [
                'Meeting tomorrow at 2pm',
                'Lunch today at 12:30pm',
                'Team standup at 9am',
            ],
            'With Duration' => [
                'Client call tomorrow at 3pm for 2 hours',
                'Dentist appointment Friday at 10am for 30 minutes',
                'Workshop next Monday at 2pm for 1.5 hours',
            ],
            'With Location' => [
                'Meeting at Conference Room A tomorrow at 2pm',
                'Lunch at The Restaurant Friday at noon',
                'Interview at Office Building next Tuesday at 10am',
            ],
            'Day of Week' => [
                'Team meeting Monday at 10am',
                'Doctor appointment next Friday at 3pm',
                'Conference call this Thursday at 2pm',
            ],
            'All Day Events' => [
                'Vacation tomorrow',
                'Conference next week',
                'Holiday Friday',
            ],
        ];
    }
}
