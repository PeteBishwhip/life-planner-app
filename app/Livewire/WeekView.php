<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class WeekView extends Component
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
        $weekDays = $this->generateWeekDays();

        return view('livewire.week-view', [
            'weekDays' => $weekDays,
        ]);
    }

    protected function generateWeekDays(): array
    {
        $startOfWeek = $this->currentDate->copy()->startOfWeek(Carbon::SUNDAY);
        $days = [];

        for ($i = 0; $i < 7; $i++) {
            $date = $startOfWeek->copy()->addDays($i);

            $days[] = [
                'date' => $date,
                'isToday' => $date->isToday(),
                'appointments' => $this->getAppointmentsForDate($date),
                'hourlySlots' => $this->generateHourlySlots($date),
            ];
        }

        return $days;
    }

    protected function generateHourlySlots(Carbon $date): array
    {
        $slots = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $slotStart = $date->copy()->setTime($hour, 0);
            $slotEnd = $date->copy()->setTime($hour, 59, 59);

            $slots[] = [
                'time' => $slotStart->format('g:i A'),
                'hour' => $hour,
                'appointments' => $this->getAppointmentsForTimeSlot($slotStart, $slotEnd),
            ];
        }

        return $slots;
    }

    protected function getAppointmentsForDate(Carbon $date): Collection
    {
        return $this->appointments->filter(function ($appointment) use ($date) {
            return $appointment->start_datetime->isSameDay($date);
        });
    }

    protected function getAppointmentsForTimeSlot(Carbon $start, Carbon $end): Collection
    {
        return $this->appointments->filter(function ($appointment) use ($start, $end) {
            return $appointment->start_datetime->between($start, $end) ||
                   $appointment->end_datetime->between($start, $end) ||
                   ($appointment->start_datetime->lte($start) && $appointment->end_datetime->gte($end));
        });
    }

    public function appointmentClicked(int $appointmentId): void
    {
        $this->dispatch('appointment-selected', appointmentId: $appointmentId);
    }

    public function timeSlotClicked(string $date, int $hour): void
    {
        $this->dispatch('time-slot-selected', date: $date, hour: $hour);
    }
}
