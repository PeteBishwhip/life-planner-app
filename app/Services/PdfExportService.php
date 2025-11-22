<?php

namespace App\Services;

use App\Models\Calendar;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PdfExportService
{
    /**
     * Export a calendar to PDF format (Month view)
     */
    public function exportMonthView(Calendar $calendar, ?Carbon $month = null): \Barryvdh\DomPDF\PDF
    {
        $month = $month ?? Carbon::now();
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        $appointments = $calendar->appointments()
            ->whereBetween('start_datetime', [$startOfMonth, $endOfMonth])
            ->orderBy('start_datetime')
            ->get();

        // Group appointments by day
        $appointmentsByDay = $appointments->groupBy(function ($appointment) {
            return $appointment->start_datetime->format('Y-m-d');
        });

        // Generate calendar grid
        $weeks = $this->generateCalendarGrid($month);

        $data = [
            'calendar' => $calendar,
            'month' => $month,
            'weeks' => $weeks,
            'appointmentsByDay' => $appointmentsByDay,
        ];

        return Pdf::loadView('pdf.calendar-month', $data)
            ->setPaper('a4', 'portrait');
    }

    /**
     * Export a calendar to PDF format (List view)
     */
    public function exportListView(
        Calendar $calendar,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): \Barryvdh\DomPDF\PDF {
        $startDate = $startDate ?? Carbon::now()->startOfMonth();
        $endDate = $endDate ?? Carbon::now()->endOfMonth()->addMonths(2);

        $appointments = $calendar->appointments()
            ->whereBetween('start_datetime', [$startDate, $endDate])
            ->orderBy('start_datetime')
            ->get();

        $data = [
            'calendar' => $calendar,
            'appointments' => $appointments,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];

        return Pdf::loadView('pdf.calendar-list', $data)
            ->setPaper('a4', 'portrait');
    }

    /**
     * Export multiple calendars to PDF format
     */
    public function exportMultipleCalendars(
        array $calendars,
        ?Carbon $startDate = null,
        ?Carbon $endDate = null
    ): \Barryvdh\DomPDF\PDF {
        $startDate = $startDate ?? Carbon::now()->startOfMonth();
        $endDate = $endDate ?? Carbon::now()->endOfMonth()->addMonths(2);

        $calendarData = [];

        foreach ($calendars as $calendar) {
            $appointments = $calendar->appointments()
                ->whereBetween('start_datetime', [$startDate, $endDate])
                ->orderBy('start_datetime')
                ->get();

            $calendarData[] = [
                'calendar' => $calendar,
                'appointments' => $appointments,
            ];
        }

        $data = [
            'calendars' => $calendarData,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ];

        return Pdf::loadView('pdf.calendars-combined', $data)
            ->setPaper('a4', 'portrait');
    }

    /**
     * Generate calendar grid for a month
     */
    protected function generateCalendarGrid(Carbon $month): array
    {
        $startOfMonth = $month->copy()->startOfMonth();
        $endOfMonth = $month->copy()->endOfMonth();

        // Start from the first day of the week containing the 1st of the month
        // Use Sunday as start of week to ensure 7-day weeks
        $current = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);
        $end = $endOfMonth->copy()->endOfWeek(Carbon::SUNDAY);

        $weeks = [];
        $week = [];

        while ($current <= $end) {
            $week[] = [
                'date' => $current->copy(),
                'isCurrentMonth' => $current->month === $month->month,
            ];

            if ($current->dayOfWeek === 6) { // Saturday
                $weeks[] = $week;
                $week = [];
            }

            $current->addDay();
        }

        if (! empty($week)) {
            $weeks[] = $week;
        }

        return $weeks;
    }

    /**
     * Generate a filename for calendar PDF export
     */
    public function generateFilename(Calendar $calendar, string $view = 'calendar'): string
    {
        $slug = \Illuminate\Support\Str::slug($calendar->name);
        $date = Carbon::now()->format('Y-m-d');

        return "{$slug}-{$view}-{$date}.pdf";
    }

    /**
     * Generate filename for multiple calendars export
     */
    public function generateCombinedFilename(): string
    {
        $date = Carbon::now()->format('Y-m-d');

        return "calendars-combined-{$date}.pdf";
    }
}
