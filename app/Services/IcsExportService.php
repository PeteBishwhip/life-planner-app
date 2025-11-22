<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Calendar;
use Carbon\Carbon;
use Spatie\IcalendarGenerator\Components\Calendar as ICalendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\Enums\RecurrenceFrequency;
use Spatie\IcalendarGenerator\ValueObjects\RRule;

class IcsExportService
{
    /**
     * Export a calendar to ICS format
     */
    public function exportCalendar(
        Calendar $calendar,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): string {
        $appointments = $this->getAppointments($calendar, $startDate, $endDate);

        $icalendar = ICalendar::create($calendar->name)
            ->productIdentifier('Life Planner App')
            ->description($calendar->description ?? 'Calendar exported from Life Planner App');

        foreach ($appointments as $appointment) {
            $event = $this->createEvent($appointment);
            $icalendar->event($event);
        }

        return $icalendar->get();
    }

    /**
     * Export multiple calendars to ICS format
     */
    public function exportMultipleCalendars(
        array $calendars,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): string {
        $icalendar = ICalendar::create('Life Planner - Combined Export')
            ->productIdentifier('Life Planner App')
            ->description('Combined calendar export from Life Planner App');

        foreach ($calendars as $calendar) {
            $appointments = $this->getAppointments($calendar, $startDate, $endDate);

            foreach ($appointments as $appointment) {
                $event = $this->createEvent($appointment);
                $icalendar->event($event);
            }
        }

        return $icalendar->get();
    }

    /**
     * Export a single appointment to ICS format
     */
    public function exportAppointment(Appointment $appointment): string
    {
        $calendar = $appointment->calendar;

        $icalendar = ICalendar::create($calendar->name)
            ->productIdentifier('Life Planner App')
            ->event($this->createEvent($appointment));

        return $icalendar->get();
    }

    /**
     * Get appointments for export
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getAppointments(
        Calendar $calendar,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ) {
        $query = $calendar->appointments()
            ->whereNull('recurrence_parent_id'); // Only get parent appointments, not instances

        if ($startDate) {
            $query->where('start_datetime', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('start_datetime', '<=', $endDate);
        }

        return $query->orderBy('start_datetime')->get();
    }

    /**
     * Create an iCalendar event from an appointment
     */
    protected function createEvent(Appointment $appointment): Event
    {
        $event = Event::create()
            ->name($appointment->title)
            ->uniqueIdentifier($appointment->id)
            ->createdAt($appointment->created_at)
            ->startsAt($appointment->start_datetime)
            ->endsAt($appointment->end_datetime);

        // Add description if present
        if ($appointment->description) {
            $event->description($appointment->description);
        }

        // Add location if present
        if ($appointment->location) {
            $event->address($appointment->location);
        }

        // Handle all-day events
        if ($appointment->is_all_day) {
            $event->fullDay();
        }

        // Handle recurrence
        if ($appointment->recurrence_rule) {
            $this->addRecurrence($event, $appointment->recurrence_rule);
        }

        return $event;
    }

    /**
     * Add recurrence rule to event
     */
    protected function addRecurrence(Event $event, array $recurrenceRule): void
    {
        if (! isset($recurrenceRule['freq'])) {
            return;
        }

        // Map frequency
        $frequency = match (strtolower($recurrenceRule['freq'])) {
            'daily' => RecurrenceFrequency::Daily,
            'weekly' => RecurrenceFrequency::Weekly,
            'monthly' => RecurrenceFrequency::Monthly,
            'yearly' => RecurrenceFrequency::Yearly,
            default => null,
        };

        if (! $frequency) {
            return;
        }

        // Build RRule
        $rrule = RRule::frequency($frequency);

        // Add interval if specified
        if (isset($recurrenceRule['interval']) && $recurrenceRule['interval'] > 1) {
            $rrule = $rrule->interval($recurrenceRule['interval']);
        }

        // Add until date if specified
        if (isset($recurrenceRule['until'])) {
            $until = Carbon::parse($recurrenceRule['until']);
            $rrule = $rrule->until($until);
        }

        // Add count if specified
        if (isset($recurrenceRule['count'])) {
            $rrule = $rrule->times($recurrenceRule['count']);
        }

        $event->rrule($rrule);
    }

    /**
     * Get the MIME type for ICS files
     */
    public function getMimeType(): string
    {
        return 'text/calendar';
    }

    /**
     * Generate a filename for calendar export
     */
    public function generateFilename(Calendar $calendar): string
    {
        $slug = \Illuminate\Support\Str::slug($calendar->name);
        $date = Carbon::now()->format('Y-m-d');

        return "{$slug}-{$date}.ics";
    }
}
