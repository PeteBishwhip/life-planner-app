<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class MonthView extends Component
{
    public Carbon $currentDate;

    public Collection $appointments;

    public array $visibleCalendars;

    public function mount(Carbon $currentDate, Collection $appointments, array $visibleCalendars): void
    {
        $this->currentDate = $currentDate;
        $this->appointments = $appointments;
        $this->visibleCalendars = $visibleCalendars;
    }

    public function render()
    {
        $calendar = $this->generateMonthCalendar();

        return view('livewire.month-view', [
            'calendar' => $calendar,
        ]);
    }

    protected function generateMonthCalendar(): array
    {
        $startOfMonth = $this->currentDate->copy()->startOfMonth();
        $endOfMonth = $this->currentDate->copy()->endOfMonth();

        // Start from the Sunday before the first day of the month
        $startDate = $startOfMonth->copy()->startOfWeek(Carbon::SUNDAY);

        // End on the Saturday after the last day of the month
        $endDate = $endOfMonth->copy()->endOfWeek(Carbon::SATURDAY);

        $weeks = [];
        $currentWeek = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $currentWeek[] = [
                'date' => $date->copy(),
                'isCurrentMonth' => $date->month === $this->currentDate->month,
                'isToday' => $date->isToday(),
                'appointments' => $this->getAppointmentsForDate($date),
            ];

            if ($date->isSaturday() || $date->eq($endDate)) {
                $weeks[] = $currentWeek;
                $currentWeek = [];
            }
        }

        return $weeks;
    }

    protected function getAppointmentsForDate(Carbon $date): Collection
    {
        return $this->appointments->filter(function ($appointment) use ($date) {
            return $appointment->start_datetime->isSameDay($date) ||
                   ($appointment->is_all_day && $date->between(
                       $appointment->start_datetime->startOfDay(),
                       $appointment->end_datetime->endOfDay()
                   ));
        });
    }

    public function appointmentClicked(int $appointmentId): void
    {
        $this->dispatch('appointment-selected', appointmentId: $appointmentId);
    }

    public function dateClicked(string $date): void
    {
        $this->dispatch('date-selected', date: $date);
    }
}
