<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\AppointmentReminder;
use App\Notifications\AppointmentReminderNotification;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReminderService
{
    /**
     * Create reminders for an appointment
     */
    public function createReminders(Appointment $appointment, array $reminderMinutes): Collection
    {
        $reminders = collect([]);

        foreach ($reminderMinutes as $minutes) {
            $reminder = AppointmentReminder::create([
                'appointment_id' => $appointment->id,
                'reminder_minutes_before' => $minutes,
                'notification_type' => 'email',
                'is_sent' => false,
            ]);

            $reminders->push($reminder);
        }

        return $reminders;
    }

    /**
     * Get due reminders that need to be sent
     */
    public function getDueReminders(?Carbon $checkTime = null): Collection
    {
        $checkTime = $checkTime ?? now();

        return AppointmentReminder::query()
            ->where('is_sent', false)
            ->whereHas('appointment', function ($query) use ($checkTime) {
                $query->where('status', 'scheduled')
                    ->where(function ($q) use ($checkTime) {
                        // Get appointments whose reminder time has passed
                        $q->whereRaw('DATE_SUB(start_datetime, INTERVAL reminder_minutes_before MINUTE) <= ?', [$checkTime]);
                    });
            })
            ->with(['appointment', 'appointment.user', 'appointment.calendar'])
            ->get();
    }

    /**
     * Send a reminder
     */
    public function sendReminder(AppointmentReminder $reminder): bool
    {
        try {
            $appointment = $reminder->appointment;
            $user = $appointment->user;

            // Send notification based on type
            switch ($reminder->notification_type) {
                case 'email':
                    $user->notify(new AppointmentReminderNotification($appointment, $reminder));
                    break;

                case 'browser':
                    // Browser notifications would be handled via web push
                    // For now, we'll just mark it as sent
                    break;

                default:
                    return false;
            }

            // Mark reminder as sent
            $reminder->update([
                'is_sent' => true,
                'sent_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send reminder: ' . $e->getMessage(), [
                'reminder_id' => $reminder->id,
                'appointment_id' => $reminder->appointment_id,
            ]);

            return false;
        }
    }

    /**
     * Process all due reminders
     */
    public function processDueReminders(): array
    {
        $dueReminders = $this->getDueReminders();

        $results = [
            'total' => $dueReminders->count(),
            'sent' => 0,
            'failed' => 0,
        ];

        foreach ($dueReminders as $reminder) {
            if ($this->sendReminder($reminder)) {
                $results['sent']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Get default reminder options
     */
    public function getDefaultReminderOptions(): array
    {
        return [
            5 => '5 minutes before',
            15 => '15 minutes before',
            30 => '30 minutes before',
            60 => '1 hour before',
            120 => '2 hours before',
            1440 => '1 day before',
            2880 => '2 days before',
            10080 => '1 week before',
        ];
    }

    /**
     * Format reminder time for display
     */
    public function formatReminderTime(int $minutes): string
    {
        if ($minutes < 60) {
            return "{$minutes} minutes before";
        }

        if ($minutes < 1440) {
            $hours = $minutes / 60;
            return $hours == 1 ? "1 hour before" : "{$hours} hours before";
        }

        $days = $minutes / 1440;
        return $days == 1 ? "1 day before" : "{$days} days before";
    }

    /**
     * Update reminders for an appointment
     */
    public function updateReminders(Appointment $appointment, array $reminderMinutes): Collection
    {
        // Delete existing unsent reminders
        $appointment->reminders()
            ->where('is_sent', false)
            ->delete();

        // Create new reminders
        return $this->createReminders($appointment, $reminderMinutes);
    }

    /**
     * Delete all reminders for an appointment
     */
    public function deleteReminders(Appointment $appointment): void
    {
        $appointment->reminders()->delete();
    }

    /**
     * Get upcoming reminders for a user
     */
    public function getUpcomingReminders(int $userId, ?int $limit = 10): Collection
    {
        return AppointmentReminder::query()
            ->where('is_sent', false)
            ->whereHas('appointment', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('status', 'scheduled')
                    ->where('start_datetime', '>', now());
            })
            ->with(['appointment', 'appointment.calendar'])
            ->orderByRaw('(SELECT start_datetime FROM appointments WHERE appointments.id = appointment_reminders.appointment_id)')
            ->limit($limit)
            ->get();
    }

    /**
     * Calculate when a reminder should be sent
     */
    public function calculateReminderTime(Appointment $appointment, int $minutesBefore): Carbon
    {
        return Carbon::parse($appointment->start_datetime)->subMinutes($minutesBefore);
    }

    /**
     * Check if a reminder is overdue
     */
    public function isOverdue(AppointmentReminder $reminder): bool
    {
        $reminderTime = $this->calculateReminderTime($reminder->appointment, $reminder->reminder_minutes_before);

        return $reminderTime->isPast() && !$reminder->is_sent;
    }

    /**
     * Get reminder statistics for a user
     */
    public function getReminderStats(int $userId, Carbon $startDate, Carbon $endDate): array
    {
        $reminders = AppointmentReminder::query()
            ->whereHas('appointment', function ($query) use ($userId, $startDate, $endDate) {
                $query->where('user_id', $userId)
                    ->whereBetween('start_datetime', [$startDate, $endDate]);
            })
            ->get();

        return [
            'total' => $reminders->count(),
            'sent' => $reminders->where('is_sent', true)->count(),
            'pending' => $reminders->where('is_sent', false)->count(),
            'by_type' => [
                'email' => $reminders->where('notification_type', 'email')->count(),
                'browser' => $reminders->where('notification_type', 'browser')->count(),
            ],
        ];
    }
}
