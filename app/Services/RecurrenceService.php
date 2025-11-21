<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\Carbon;

class RecurrenceService
{
    /**
     * Generate recurrence instances for an appointment within a date range
     */
    public function generateInstances(Appointment $appointment, Carbon $startDate, Carbon $endDate): array
    {
        if (! $appointment->isRecurring()) {
            return [$appointment];
        }

        $rule = $appointment->recurrence_rule;
        $instances = [];

        $frequency = $rule['frequency'] ?? 'daily';
        $interval = $rule['interval'] ?? 1;
        $until = isset($rule['until']) ? Carbon::parse($rule['until']) : $endDate;
        $count = $rule['count'] ?? null;

        // Limit the until date to the requested end date
        $until = $until->min($endDate);

        $current = Carbon::parse($appointment->start_datetime);
        $duration = Carbon::parse($appointment->start_datetime)
            ->diffInMinutes(Carbon::parse($appointment->end_datetime));

        $generatedCount = 0;
        $maxCount = $count ?? 730; // Default max 2 years of daily occurrences

        while ($current->lte($until) && $generatedCount < $maxCount) {
            if ($current->gte($startDate)) {
                $instanceStart = $current->copy();
                $instanceEnd = $current->copy()->addMinutes($duration);

                $instances[] = [
                    'id' => $appointment->id.'_'.$instanceStart->format('YmdHis'),
                    'appointment_id' => $appointment->id,
                    'title' => $appointment->title,
                    'description' => $appointment->description,
                    'location' => $appointment->location,
                    'start_datetime' => $instanceStart,
                    'end_datetime' => $instanceEnd,
                    'is_all_day' => $appointment->is_all_day,
                    'color' => $appointment->color,
                    'status' => $appointment->status,
                    'is_recurring_instance' => true,
                    'recurrence_parent_id' => $appointment->id,
                ];

                $generatedCount++;
            }

            $current = $this->getNextOccurrence($current, $frequency, $interval, $rule);
        }

        return $instances;
    }

    /**
     * Calculate the next occurrence based on frequency and interval
     */
    protected function getNextOccurrence(Carbon $current, string $frequency, int $interval, array $rule): Carbon
    {
        $next = $current->copy();

        switch ($frequency) {
            case 'daily':
                $next->addDays($interval);
                break;

            case 'weekly':
                // Handle specific days of week if provided
                if (isset($rule['by_day']) && is_array($rule['by_day'])) {
                    $next = $this->getNextWeeklyOccurrence($current, $rule['by_day'], $interval);
                } else {
                    $next->addWeeks($interval);
                }
                break;

            case 'monthly':
                if (isset($rule['by_month_day'])) {
                    $next = $this->getNextMonthlyByDayOccurrence($current, $rule['by_month_day'], $interval);
                } else {
                    $next->addMonths($interval);
                }
                break;

            case 'yearly':
                $next->addYears($interval);
                break;

            default:
                $next->addDays($interval);
        }

        return $next;
    }

    /**
     * Get next weekly occurrence for specific days
     */
    protected function getNextWeeklyOccurrence(Carbon $current, array $days, int $interval): Carbon
    {
        $dayMap = [
            'MO' => Carbon::MONDAY,
            'TU' => Carbon::TUESDAY,
            'WE' => Carbon::WEDNESDAY,
            'TH' => Carbon::THURSDAY,
            'FR' => Carbon::FRIDAY,
            'SA' => Carbon::SATURDAY,
            'SU' => Carbon::SUNDAY,
        ];

        $next = $current->copy()->addDay();
        $weeksAdded = 0;

        // Find next matching day
        while (true) {
            $dayOfWeek = $next->format('l');
            $dayCode = strtoupper(substr($dayOfWeek, 0, 2));

            // Convert day names to codes
            $dayCodeMap = [
                'MO' => 'MO', 'TU' => 'TU', 'WE' => 'WE', 'TH' => 'TH',
                'FR' => 'FR', 'SA' => 'SA', 'SU' => 'SU',
            ];

            foreach ($dayCodeMap as $code => $value) {
                if (strtoupper(substr($next->format('l'), 0, 2)) === substr($code, 0, 2)) {
                    $dayCode = $code;
                    break;
                }
            }

            if (in_array($dayCode, $days)) {
                // Check if we've completed a week cycle
                if ($next->dayOfWeek < $current->dayOfWeek || $next->diffInDays($current) >= 7) {
                    if ($weeksAdded >= $interval - 1) {
                        break;
                    }
                }

                if ($next->gt($current)) {
                    break;
                }
            }

            // Move to next day
            $next->addDay();

            // Check if we completed a week
            if ($next->dayOfWeek === $current->dayOfWeek && $next->gt($current)) {
                $weeksAdded++;
            }

            // Prevent infinite loop
            if ($next->diffInDays($current) > 365) {
                break;
            }
        }

        return $next;
    }

    /**
     * Get next monthly occurrence by day of month
     */
    protected function getNextMonthlyByDayOccurrence(Carbon $current, int $dayOfMonth, int $interval): Carbon
    {
        $next = $current->copy()->addMonths($interval);

        // Handle months with fewer days
        $daysInMonth = $next->daysInMonth;
        if ($dayOfMonth > $daysInMonth) {
            $next->day($daysInMonth);
        } else {
            $next->day($dayOfMonth);
        }

        return $next;
    }

    /**
     * Create recurrence rule array from parameters
     */
    public function createRecurrenceRule(
        string $frequency,
        int $interval = 1,
        ?string $until = null,
        ?int $count = null,
        ?array $byDay = null,
        ?int $byMonthDay = null
    ): array {
        $rule = [
            'frequency' => $frequency,
            'interval' => $interval,
        ];

        if ($until) {
            $rule['until'] = $until;
        }

        if ($count) {
            $rule['count'] = $count;
        }

        if ($byDay) {
            $rule['by_day'] = $byDay;
        }

        if ($byMonthDay) {
            $rule['by_month_day'] = $byMonthDay;
        }

        return $rule;
    }

    /**
     * Format recurrence rule to human-readable text
     */
    public function formatRecurrenceRule(array $rule): string
    {
        $frequency = $rule['frequency'] ?? 'daily';
        $interval = $rule['interval'] ?? 1;

        $text = 'Repeats ';

        if ($interval === 1) {
            $text .= match ($frequency) {
                'daily' => 'daily',
                'weekly' => 'weekly',
                'monthly' => 'monthly',
                'yearly' => 'yearly',
                default => 'daily',
            };
        } else {
            $text .= match ($frequency) {
                'daily' => "every {$interval} days",
                'weekly' => "every {$interval} weeks",
                'monthly' => "every {$interval} months",
                'yearly' => "every {$interval} years",
                default => "every {$interval} days",
            };
        }

        // Add day of week for weekly recurrence
        if ($frequency === 'weekly' && isset($rule['by_day'])) {
            $days = array_map(function ($day) {
                return match ($day) {
                    'MO' => 'Monday',
                    'TU' => 'Tuesday',
                    'WE' => 'Wednesday',
                    'TH' => 'Thursday',
                    'FR' => 'Friday',
                    'SA' => 'Saturday',
                    'SU' => 'Sunday',
                    default => $day,
                };
            }, $rule['by_day']);

            $text .= ' on '.implode(', ', $days);
        }

        // Add end condition
        if (isset($rule['until'])) {
            $until = Carbon::parse($rule['until']);
            $text .= ' until '.$until->format('M d, Y');
        } elseif (isset($rule['count'])) {
            $text .= ' for '.$rule['count'].' occurrences';
        }

        return $text;
    }

    /**
     * Validate recurrence rule
     */
    public function validateRecurrenceRule(array $rule): bool
    {
        if (! isset($rule['frequency'])) {
            return false;
        }

        $validFrequencies = ['daily', 'weekly', 'monthly', 'yearly'];
        if (! in_array($rule['frequency'], $validFrequencies)) {
            return false;
        }

        if (isset($rule['interval']) && (! is_int($rule['interval']) || $rule['interval'] < 1)) {
            return false;
        }

        if (isset($rule['count']) && (! is_int($rule['count']) || $rule['count'] < 1)) {
            return false;
        }

        return true;
    }
}
