<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Calendar;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ConflictDetectionService
{
    /**
     * Check for conflicts across all user's calendars
     */
    public function hasConflictAcrossCalendars(
        int $userId,
        Carbon $start,
        Carbon $end,
        ?int $excludeAppointmentId = null,
        ?int $excludeCalendarId = null
    ): bool {
        $conflicts = $this->findConflicts($userId, $start, $end, $excludeAppointmentId, $excludeCalendarId);

        return $conflicts->isNotEmpty();
    }

    /**
     * Find all conflicting appointments across user's calendars
     */
    public function findConflicts(
        int $userId,
        Carbon $start,
        Carbon $end,
        ?int $excludeAppointmentId = null,
        ?int $excludeCalendarId = null
    ): Collection {
        $query = Appointment::query()
            ->where('user_id', $userId)
            ->where('status', 'scheduled')
            ->where(function ($q) use ($start, $end) {
                // Check for any overlap
                $q->where(function ($q) use ($start, $end) {
                    // Appointment starts within the range
                    $q->whereBetween('start_datetime', [$start, $end]);
                })
                    ->orWhere(function ($q) use ($start, $end) {
                        // Appointment ends within the range
                        $q->whereBetween('end_datetime', [$start, $end]);
                    })
                    ->orWhere(function ($q) use ($start, $end) {
                        // Appointment spans the entire range
                        $q->where('start_datetime', '<=', $start)
                            ->where('end_datetime', '>=', $end);
                    });
            });

        if ($excludeAppointmentId) {
            $query->where('id', '!=', $excludeAppointmentId);
        }

        if ($excludeCalendarId) {
            $query->where('calendar_id', '!=', $excludeCalendarId);
        }

        return $query->with('calendar')->get();
    }

    /**
     * Get blocked time slots for a specific calendar based on other calendars
     * Business calendar appointments block personal calendar and vice versa
     */
    public function getBlockedSlots(int $userId, int $calendarId, Carbon $startDate, Carbon $endDate): Collection
    {
        $calendar = Calendar::find($calendarId);

        if (! $calendar) {
            return collect([]);
        }

        // Get appointments from other calendars that should block this calendar
        $query = Appointment::query()
            ->where('user_id', $userId)
            ->where('calendar_id', '!=', $calendarId)
            ->where('status', 'scheduled')
            ->whereBetween('start_datetime', [$startDate, $endDate])
            ->with('calendar');

        return $query->get()->map(function ($appointment) {
            return [
                'id' => $appointment->id,
                'calendar_name' => $appointment->calendar->name,
                'calendar_type' => $appointment->calendar->type,
                'title' => $appointment->title,
                'start_datetime' => $appointment->start_datetime,
                'end_datetime' => $appointment->end_datetime,
                'is_blocking' => true,
            ];
        });
    }

    /**
     * Check if appointment can be scheduled (no conflicts or intentional override)
     */
    public function canSchedule(
        int $userId,
        int $calendarId,
        Carbon $start,
        Carbon $end,
        ?int $excludeAppointmentId = null,
        bool $allowOverride = false
    ): array {
        $conflicts = $this->findConflicts($userId, $start, $end, $excludeAppointmentId);

        if ($conflicts->isEmpty()) {
            return [
                'can_schedule' => true,
                'conflicts' => [],
                'message' => 'No conflicts found.',
            ];
        }

        if ($allowOverride) {
            return [
                'can_schedule' => true,
                'conflicts' => $conflicts,
                'message' => 'Conflicts exist but override is allowed.',
                'warning' => 'This appointment overlaps with existing appointments.',
            ];
        }

        return [
            'can_schedule' => false,
            'conflicts' => $conflicts,
            'message' => 'Conflicts detected. Please choose a different time or enable override.',
        ];
    }

    /**
     * Get conflict summary for display
     */
    public function getConflictSummary(Collection $conflicts): string
    {
        if ($conflicts->isEmpty()) {
            return 'No conflicts.';
        }

        $count = $conflicts->count();
        $calendarNames = $conflicts->pluck('calendar.name')->unique()->implode(', ');

        if ($count === 1) {
            return "Conflicts with 1 appointment in {$calendarNames}.";
        }

        return "Conflicts with {$count} appointments across: {$calendarNames}.";
    }

    /**
     * Find available time slots within a date range
     */
    public function findAvailableSlots(
        int $userId,
        Carbon $startDate,
        Carbon $endDate,
        int $durationMinutes,
        ?int $calendarId = null,
        array $workingHours = ['start' => 9, 'end' => 17]
    ): Collection {
        $appointments = Appointment::query()
            ->where('user_id', $userId)
            ->where('status', 'scheduled')
            ->whereBetween('start_datetime', [$startDate, $endDate])
            ->orderBy('start_datetime')
            ->get();

        $availableSlots = collect([]);
        $current = $startDate->copy()->setTime($workingHours['start'], 0);
        $end = $endDate->copy()->setTime($workingHours['end'], 0);

        while ($current->lte($end)) {
            $slotEnd = $current->copy()->addMinutes($durationMinutes);

            // Check if this slot is within working hours
            if ($slotEnd->hour < $workingHours['start'] || $current->hour >= $workingHours['end']) {
                $current->addMinutes(30); // Move to next 30-min slot

                continue;
            }

            // Check if slot conflicts with any appointment
            $hasConflict = $appointments->contains(function ($appointment) use ($current, $slotEnd) {
                return $this->timesOverlap(
                    $current,
                    $slotEnd,
                    $appointment->start_datetime,
                    $appointment->end_datetime
                );
            });

            if (! $hasConflict) {
                $availableSlots->push([
                    'start' => $current->copy(),
                    'end' => $slotEnd->copy(),
                ]);
            }

            // Move to next slot (30-minute increments)
            $current->addMinutes(30);

            // Skip to next day if we've passed working hours
            if ($current->hour >= $workingHours['end']) {
                $current->addDay()->setTime($workingHours['start'], 0);
            }
        }

        return $availableSlots;
    }

    /**
     * Check if two time ranges overlap
     */
    protected function timesOverlap(Carbon $start1, Carbon $end1, Carbon $start2, Carbon $end2): bool
    {
        return $start1->lt($end2) && $end1->gt($start2);
    }

    /**
     * Calculate conflict percentage (how much time is conflicted)
     */
    public function calculateConflictPercentage(
        Carbon $start,
        Carbon $end,
        Collection $conflicts
    ): float {
        $totalDuration = $start->diffInMinutes($end);

        if ($totalDuration === 0) {
            return 0;
        }

        $conflictedMinutes = 0;

        foreach ($conflicts as $conflict) {
            $conflictStart = max($start, $conflict->start_datetime);
            $conflictEnd = min($end, $conflict->end_datetime);

            if ($conflictStart->lt($conflictEnd)) {
                $conflictedMinutes += $conflictStart->diffInMinutes($conflictEnd);
            }
        }

        return ($conflictedMinutes / $totalDuration) * 100;
    }
}
