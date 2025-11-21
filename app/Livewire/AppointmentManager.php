<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\Calendar;
use App\Services\ConflictDetectionService;
use App\Services\RecurrenceService;
use App\Services\ReminderService;
use Carbon\Carbon;
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

    // Recurrence fields
    public bool $is_recurring = false;

    public string $recurrence_frequency = 'daily';

    public int $recurrence_interval = 1;

    public ?string $recurrence_end_date = null;

    public ?int $recurrence_count = null;

    public array $recurrence_days = []; // For weekly recurrence

    // Reminder fields
    public array $reminder_minutes = [];

    public array $available_reminders = [];

    // Conflict detection
    public array $conflicts = [];

    public bool $allow_override = false;

    public bool $show_conflicts = false;

    protected function rules(): array
    {
        return Appointment::rules();
    }

    public function mount(ReminderService $reminderService): void
    {
        // Set default calendar
        $defaultCalendar = auth()->user()->calendars()->default()->first();
        if ($defaultCalendar) {
            $this->calendar_id = $defaultCalendar->id;
            $this->color = $defaultCalendar->color;
        }

        // Load available reminder options
        $this->available_reminders = $reminderService->getDefaultReminderOptions();
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

    public function save(
        RecurrenceService $recurrenceService,
        ReminderService $reminderService,
        ConflictDetectionService $conflictService
    ): void {
        $validated = $this->validate();
        $validated['user_id'] = auth()->id();

        // Check for conflicts if not allowing override
        if (! $this->allow_override) {
            $startDate = Carbon::parse($this->start_datetime);
            $endDate = Carbon::parse($this->end_datetime);

            $result = $conflictService->canSchedule(
                auth()->id(),
                $this->calendar_id,
                $startDate,
                $endDate,
                $this->appointmentId
            );

            if (! $result['can_schedule']) {
                $this->conflicts = $result['conflicts']->toArray();
                $this->show_conflicts = true;
                session()->flash('warning', $result['message']);

                return;
            }
        }

        // Build recurrence rule if recurring
        if ($this->is_recurring) {
            $validated['recurrence_rule'] = $recurrenceService->createRecurrenceRule(
                $this->recurrence_frequency,
                $this->recurrence_interval,
                $this->recurrence_end_date,
                $this->recurrence_count,
                ! empty($this->recurrence_days) ? $this->recurrence_days : null
            );
        } else {
            $validated['recurrence_rule'] = null;
        }

        // Handle all-day events - set time to start/end of day
        if ($this->is_all_day) {
            $start = Carbon::parse($this->start_datetime)->startOfDay();
            $end = Carbon::parse($this->end_datetime)->endOfDay();
            $validated['start_datetime'] = $start;
            $validated['end_datetime'] = $end;
        }

        if ($this->isEditing && $this->appointmentId) {
            $appointment = Appointment::findOrFail($this->appointmentId);

            if ($appointment->user_id !== auth()->id()) {
                session()->flash('error', 'Unauthorized action.');

                return;
            }

            $appointment->update($validated);

            // Update reminders
            if (! empty($this->reminder_minutes)) {
                $reminderService->updateReminders($appointment, $this->reminder_minutes);
            }

            session()->flash('success', 'Appointment updated successfully.');
        } else {
            $appointment = Appointment::create($validated);

            // Create reminders
            if (! empty($this->reminder_minutes)) {
                $reminderService->createReminders($appointment, $this->reminder_minutes);
            }

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
        $appointment = Appointment::with('reminders')->findOrFail($appointmentId);

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

        // Load recurrence settings
        if ($appointment->isRecurring()) {
            $this->is_recurring = true;
            $rule = $appointment->recurrence_rule;
            $this->recurrence_frequency = $rule['frequency'] ?? 'daily';
            $this->recurrence_interval = $rule['interval'] ?? 1;
            $this->recurrence_end_date = $rule['until'] ?? null;
            $this->recurrence_count = $rule['count'] ?? null;
            $this->recurrence_days = $rule['by_day'] ?? [];
        }

        // Load reminders
        $this->reminder_minutes = $appointment->reminders->pluck('reminder_minutes_before')->toArray();
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
            'is_recurring',
            'recurrence_frequency',
            'recurrence_interval',
            'recurrence_end_date',
            'recurrence_count',
            'recurrence_days',
            'reminder_minutes',
            'conflicts',
            'allow_override',
            'show_conflicts',
        ]);

        $this->recurrence_interval = 1;
        $this->recurrence_frequency = 'daily';

        // Reset to default calendar
        $defaultCalendar = auth()->user()->calendars()->default()->first();
        if ($defaultCalendar) {
            $this->calendar_id = $defaultCalendar->id;
            $this->color = $defaultCalendar->color;
        }
    }

    /**
     * Check for conflicts when datetime changes
     */
    public function checkConflicts(ConflictDetectionService $conflictService): void
    {
        if (empty($this->start_datetime) || empty($this->end_datetime)) {
            return;
        }

        $startDate = Carbon::parse($this->start_datetime);
        $endDate = Carbon::parse($this->end_datetime);

        $conflicts = $conflictService->findConflicts(
            auth()->id(),
            $startDate,
            $endDate,
            $this->appointmentId
        );

        $this->conflicts = $conflicts->toArray();
        $this->show_conflicts = $conflicts->isNotEmpty();
    }

    /**
     * Override conflicts and save anyway
     */
    public function overrideConflicts(): void
    {
        $this->allow_override = true;
        $this->show_conflicts = false;
    }

    /**
     * Handle all-day toggle
     */
    public function updatedIsAllDay(): void
    {
        if ($this->is_all_day && ! empty($this->start_datetime)) {
            // When enabling all-day, set times to start/end of day
            $start = Carbon::parse($this->start_datetime)->startOfDay();
            $this->start_datetime = $start->format('Y-m-d');

            if (! empty($this->end_datetime)) {
                $end = Carbon::parse($this->end_datetime)->endOfDay();
                $this->end_datetime = $end->format('Y-m-d');
            } else {
                $this->end_datetime = $start->format('Y-m-d');
            }
        }
    }

    /**
     * Handle drag and drop rescheduling
     */
    public function reschedule(int $appointmentId, string $newStart, string $newEnd): void
    {
        $appointment = Appointment::findOrFail($appointmentId);

        if ($appointment->user_id !== auth()->id()) {
            session()->flash('error', 'Unauthorized action.');

            return;
        }

        $appointment->update([
            'start_datetime' => Carbon::parse($newStart),
            'end_datetime' => Carbon::parse($newEnd),
        ]);

        session()->flash('success', 'Appointment rescheduled successfully.');
        $this->dispatch('appointment-saved');
    }
}
