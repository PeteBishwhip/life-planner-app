<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\Calendar;
use Livewire\Component;

class AppointmentManager extends Component
{
    public ?int $appointmentId = null;

    public ?int $calendar_id = null;

    public string $title = '';

    public string $description = '';

    public string $location = '';

    public string $start_datetime = '';

    public string $end_datetime = '';

    public bool $is_all_day = false;

    public string $color = '';

    public string $status = 'scheduled';

    public bool $isOpen = false;

    public bool $isEditing = false;

    protected function rules(): array
    {
        return Appointment::rules();
    }

    public function mount(): void
    {
        // Set default calendar
        $defaultCalendar = auth()->user()->calendars()->default()->first();
        if ($defaultCalendar) {
            $this->calendar_id = $defaultCalendar->id;
            $this->color = $defaultCalendar->color;
        }
    }

    public function render()
    {
        $calendars = auth()->user()->calendars()->visible()->get();

        return view('livewire.appointment-manager', [
            'calendars' => $calendars,
        ]);
    }

    public function open(?int $appointmentId = null, ?string $date = null, ?int $hour = null): void
    {
        $this->resetForm();

        if ($appointmentId) {
            $this->loadAppointment($appointmentId);
        } elseif ($date) {
            $this->setDefaultDateTime($date, $hour);
        }

        $this->isOpen = true;
    }

    public function close(): void
    {
        $this->isOpen = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $validated = $this->validate();
        $validated['user_id'] = auth()->id();

        if ($this->isEditing && $this->appointmentId) {
            $appointment = Appointment::findOrFail($this->appointmentId);

            if ($appointment->user_id !== auth()->id()) {
                session()->flash('error', 'Unauthorized action.');

                return;
            }

            $appointment->update($validated);
            session()->flash('success', 'Appointment updated successfully.');
        } else {
            Appointment::create($validated);
            session()->flash('success', 'Appointment created successfully.');
        }

        $this->close();
        $this->dispatch('appointment-saved');
    }

    public function delete(): void
    {
        if ($this->appointmentId) {
            $appointment = Appointment::findOrFail($this->appointmentId);

            if ($appointment->user_id !== auth()->id()) {
                session()->flash('error', 'Unauthorized action.');

                return;
            }

            $appointment->delete();
            session()->flash('success', 'Appointment deleted successfully.');

            $this->close();
            $this->dispatch('appointment-deleted');
        }
    }

    public function onCalendarChanged(): void
    {
        if ($this->calendar_id && empty($this->color)) {
            $calendar = Calendar::find($this->calendar_id);
            if ($calendar) {
                $this->color = $calendar->color;
            }
        }
    }

    protected function loadAppointment(int $appointmentId): void
    {
        $appointment = Appointment::findOrFail($appointmentId);

        if ($appointment->user_id !== auth()->id()) {
            return;
        }

        $this->isEditing = true;
        $this->appointmentId = $appointment->id;
        $this->calendar_id = $appointment->calendar_id;
        $this->title = $appointment->title;
        $this->description = $appointment->description ?? '';
        $this->location = $appointment->location ?? '';
        $this->start_datetime = $appointment->start_datetime->format('Y-m-d\TH:i');
        $this->end_datetime = $appointment->end_datetime->format('Y-m-d\TH:i');
        $this->is_all_day = $appointment->is_all_day;
        $this->color = $appointment->color;
        $this->status = $appointment->status;
    }

    protected function setDefaultDateTime(?string $date, ?int $hour): void
    {
        $dateTime = $date ? \Carbon\Carbon::parse($date) : now();

        if ($hour !== null) {
            $dateTime->setTime($hour, 0);
        } else {
            $dateTime->addHour()->minutes(0);
        }

        $this->start_datetime = $dateTime->format('Y-m-d\TH:i');
        $this->end_datetime = $dateTime->copy()->addHour()->format('Y-m-d\TH:i');
    }

    protected function resetForm(): void
    {
        $this->reset([
            'appointmentId',
            'title',
            'description',
            'location',
            'start_datetime',
            'end_datetime',
            'is_all_day',
            'color',
            'status',
            'isEditing',
        ]);

        // Reset to default calendar
        $defaultCalendar = auth()->user()->calendars()->default()->first();
        if ($defaultCalendar) {
            $this->calendar_id = $defaultCalendar->id;
            $this->color = $defaultCalendar->color;
        }
    }
}
