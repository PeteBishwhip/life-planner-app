<?php

namespace App\Livewire;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Livewire\Component;

class DayView extends Component
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
        $hourlySlots = $this->generateHourlySlots();

        return view('livewire.day-view', [
            'hourlySlots' => $hourlySlots,
            'allDayAppointments' => $this->getAllDayAppointments(),
        ]);
    }

    protected function generateHourlySlots(): array
    {
        $slots = [];

        for ($hour = 0; $hour < 24; $hour++) {
            $slotStart = $this->currentDate->copy()->setTime($hour, 0);
            $slotEnd = $this->currentDate->copy()->setTime($hour, 59, 59);

            $slots[] = [
                'time' => $slotStart->format('g:i A'),
                'hour' => $hour,
                'appointments' => $this->getAppointmentsForTimeSlot($slotStart, $slotEnd),
                'isBusiness HoursStart' => $hour === 9,
                'isBusinessHoursEnd' => $hour === 17,
            ];
        }

        return $slots;
    }

    protected function getAppointmentsForTimeSlot(Carbon $start, Carbon $end): Collection
    {
        return $this->appointments->filter(function ($appointment) use ($start, $end) {
            // Exclude all-day events from hourly slots
            if ($appointment->is_all_day) {
                return false;
            }

            return $appointment->start_datetime->between($start, $end) ||
                   $appointment->end_datetime->between($start, $end) ||
                   ($appointment->start_datetime->lte($start) && $appointment->end_datetime->gte($end));
        });
    }

    protected function getAllDayAppointments(): Collection
    {
        return $this->appointments->filter(function ($appointment) {
            return $appointment->is_all_day && $appointment->start_datetime->isSameDay($this->currentDate);
        });
    }

    public function appointmentClicked(int $appointmentId): void
    {
        $this->dispatch('appointment-selected', appointmentId: $appointmentId);
    }

    public function timeSlotClicked(int $hour): void
    {
        $this->dispatch('time-slot-selected', date: $this->currentDate->toDateString(), hour: $hour);
    }
}
