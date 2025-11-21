<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Calendar;
use Carbon\Carbon;

class CsvExportService
{
    /**
     * Export a calendar to CSV format
     *
     * @param Calendar $calendar
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return string
     */
    public function exportCalendar(
        Calendar $calendar,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): string {
        $appointments = $this->getAppointments($calendar, $startDate, $endDate);

        return $this->generateCsv($appointments);
    }

    /**
     * Export multiple calendars to CSV format
     *
     * @param array $calendars
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return string
     */
    public function exportMultipleCalendars(
        array $calendars,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): string {
        $allAppointments = collect();

        foreach ($calendars as $calendar) {
            $appointments = $this->getAppointments($calendar, $startDate, $endDate);
            $allAppointments = $allAppointments->merge($appointments);
        }

        // Sort by start date
        $allAppointments = $allAppointments->sortBy('start_datetime');

        return $this->generateCsv($allAppointments);
    }

    /**
     * Get appointments for export
     *
     * @param Calendar $calendar
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getAppointments(
        Calendar $calendar,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ) {
        $query = $calendar->appointments();

        if ($startDate) {
            $query->where('start_datetime', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('start_datetime', '<=', $endDate);
        }

        return $query->orderBy('start_datetime')->get();
    }

    /**
     * Generate CSV content from appointments
     *
     * @param \Illuminate\Support\Collection $appointments
     * @return string
     */
    protected function generateCsv($appointments): string
    {
        $output = fopen('php://temp', 'r+');

        // Write header row
        fputcsv($output, [
            'Title',
            'Description',
            'Location',
            'Start Date',
            'Start Time',
            'End Date',
            'End Time',
            'All Day',
            'Calendar',
            'Calendar Type',
            'Status',
            'Is Recurring',
            'Recurrence Pattern',
            'Created At',
        ]);

        // Write appointment rows
        foreach ($appointments as $appointment) {
            $calendar = $appointment->calendar;

            fputcsv($output, [
                $appointment->title,
                $appointment->description ?? '',
                $appointment->location ?? '',
                $appointment->start_datetime->format('Y-m-d'),
                $appointment->is_all_day ? '' : $appointment->start_datetime->format('H:i'),
                $appointment->end_datetime->format('Y-m-d'),
                $appointment->is_all_day ? '' : $appointment->end_datetime->format('H:i'),
                $appointment->is_all_day ? 'Yes' : 'No',
                $calendar->name,
                $calendar->type,
                $appointment->status,
                $appointment->recurrence_rule ? 'Yes' : 'No',
                $appointment->recurrence_rule ? $this->formatRecurrenceRule($appointment->recurrence_rule) : '',
                $appointment->created_at->format('Y-m-d H:i:s'),
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Format recurrence rule for CSV display
     *
     * @param array $rule
     * @return string
     */
    protected function formatRecurrenceRule(array $rule): string
    {
        $parts = [];

        if (isset($rule['freq'])) {
            $parts[] = ucfirst($rule['freq']);
        }

        if (isset($rule['interval']) && $rule['interval'] > 1) {
            $parts[] = "every {$rule['interval']}";
        }

        if (isset($rule['count'])) {
            $parts[] = "{$rule['count']} times";
        }

        if (isset($rule['until'])) {
            $parts[] = "until {$rule['until']}";
        }

        if (isset($rule['byDay']) && !empty($rule['byDay'])) {
            $days = is_array($rule['byDay']) ? implode(',', $rule['byDay']) : $rule['byDay'];
            $parts[] = "on {$days}";
        }

        return implode(', ', $parts);
    }

    /**
     * Get the MIME type for CSV files
     *
     * @return string
     */
    public function getMimeType(): string
    {
        return 'text/csv';
    }

    /**
     * Generate a filename for calendar export
     *
     * @param Calendar $calendar
     * @return string
     */
    public function generateFilename(Calendar $calendar): string
    {
        $slug = \Illuminate\Support\Str::slug($calendar->name);
        $date = Carbon::now()->format('Y-m-d');

        return "{$slug}-{$date}.csv";
    }

    /**
     * Generate filename for multiple calendars export
     *
     * @return string
     */
    public function generateCombinedFilename(): string
    {
        $date = Carbon::now()->format('Y-m-d');

        return "calendars-combined-{$date}.csv";
    }
}
