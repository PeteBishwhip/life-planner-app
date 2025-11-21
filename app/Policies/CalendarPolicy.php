<?php

namespace App\Policies;

use App\Models\Calendar;
use App\Models\User;

class CalendarPolicy
{
    /**
     * Determine if the user can view any calendars.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can view the calendar.
     */
    public function view(User $user, Calendar $calendar): bool
    {
        return $user->id === $calendar->user_id;
    }

    /**
     * Determine if the user can create calendars.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if the user can update the calendar.
     */
    public function update(User $user, Calendar $calendar): bool
    {
        return $user->id === $calendar->user_id;
    }

    /**
     * Determine if the user can delete the calendar.
     */
    public function delete(User $user, Calendar $calendar): bool
    {
        return $user->id === $calendar->user_id;
    }

    /**
     * Determine if the user can restore the calendar.
     */
    public function restore(User $user, Calendar $calendar): bool
    {
        return $user->id === $calendar->user_id;
    }

    /**
     * Determine if the user can permanently delete the calendar.
     */
    public function forceDelete(User $user, Calendar $calendar): bool
    {
        return $user->id === $calendar->user_id;
    }
}
