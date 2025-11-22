<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\User;
use App\Notifications\DailyDigestNotification;
use Carbon\Carbon;

class DailyDigestService
{
    /**
     * Send daily digest to a user
     */
    public function sendDailyDigest(User $user, ?Carbon $date = null): bool
    {
        $date = $date ?? today();

        // Get user's appointments for the day
        $appointments = Appointment::query()
            ->where('user_id', $user->id)
            ->where('status', 'scheduled')
            ->whereDate('start_datetime', $date)
            ->with(['calendar'])
            ->orderBy('start_datetime')
            ->get();

        // Send notification
        try {
            $user->notify(new DailyDigestNotification($appointments, $date));

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send daily digest: '.$e->getMessage(), [
                'user_id' => $user->id,
                'date' => $date->toDateString(),
            ]);

            return false;
        }
    }

    /**
     * Send daily digest to all users
     */
    public function sendToAllUsers(?Carbon $date = null): array
    {
        $date = $date ?? today();
        $users = User::all();

        $results = [
            'total' => $users->count(),
            'sent' => 0,
            'failed' => 0,
        ];

        foreach ($users as $user) {
            if ($this->sendDailyDigest($user, $date)) {
                $results['sent']++;
            } else {
                $results['failed']++;
            }
        }

        return $results;
    }

    /**
     * Get preview of daily digest for a user
     */
    public function getDigestPreview(User $user, ?Carbon $date = null): array
    {
        $date = $date ?? today();

        $appointments = Appointment::query()
            ->where('user_id', $user->id)
            ->where('status', 'scheduled')
            ->whereDate('start_datetime', $date)
            ->with(['calendar'])
            ->orderBy('start_datetime')
            ->get();

        return [
            'date' => $date->format('l, F j, Y'),
            'appointment_count' => $appointments->count(),
            'appointments' => $appointments->map(function ($appointment) {
                return [
                    'title' => $appointment->title,
                    'time' => $appointment->is_all_day
                        ? 'All Day'
                        : $appointment->start_datetime->format('g:i A').' - '.$appointment->end_datetime->format('g:i A'),
                    'location' => $appointment->location,
                    'calendar' => $appointment->calendar->name,
                ];
            })->toArray(),
        ];
    }

    /**
     * Check if user should receive digest (based on preferences)
     */
    public function shouldSendDigest(User $user): bool
    {
        // In a real implementation, check user preferences
        // For now, return true for all users
        return true;
    }

    /**
     * Get optimal send time for user
     */
    public function getOptimalSendTime(User $user): Carbon
    {
        // In a real implementation, this would check user preferences
        // Default to 6 AM in user's timezone
        return today()->setTime(6, 0);
    }
}
