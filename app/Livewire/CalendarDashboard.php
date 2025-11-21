<?php

namespace App\Livewire;

use App\Models\Appointment;
use Carbon\Carbon;
use Livewire\Component;

class CalendarDashboard extends Component
{
    public string $viewType = 'month'; // month, week, day, list

    public Carbon $currentDate;

    public array $visibleCalendars = [];

    public ?int $selectedAppointment = null;

    public bool $showAppointmentModal = false;

    public function mount(): void
    {
        $this->currentDate = now();

        // Load user's visible calendars by default
        $this->visibleCalendars = auth()->user()
            ->calendars()
            ->visible()
            ->pluck('id')
            ->toArray();
    }

    public function render()
    {
        // Cache calendars for 1 hour to reduce queries
        $calendars = cache()->remember(
            'user_calendars_' . auth()->id(),
            3600,
            fn () => auth()->user()->calendars()->get()
        );

        $appointments = $this->getAppointmentsForCurrentView();

        return view('livewire.calendar-dashboard', [
            'calendars' => $calendars,
            'appointments' => $appointments,
            'currentDate' => $this->currentDate,
        ]);
    }

    public function changeView(string $type): void
    {
        if (in_array($type, ['month', 'week', 'day', 'list'])) {
            $this->viewType = $type;
        }
    }

    public function previous(): void
    {
        $this->currentDate = match ($this->viewType) {
            'month' => $this->currentDate->copy()->subMonth(),
            'week' => $this->currentDate->copy()->subWeek(),
            'day' => $this->currentDate->copy()->subDay(),
            'list' => $this->currentDate->copy()->subWeek(),
        };
    }

    public function next(): void
    {
        $this->currentDate = match ($this->viewType) {
            'month' => $this->currentDate->copy()->addMonth(),
            'week' => $this->currentDate->copy()->addWeek(),
            'day' => $this->currentDate->copy()->addDay(),
            'list' => $this->currentDate->copy()->addWeek(),
        };
    }

    public function today(): void
    {
        $this->currentDate = now();
    }

    public function toggleCalendar(int $calendarId): void
    {
        if (in_array($calendarId, $this->visibleCalendars)) {
            $this->visibleCalendars = array_values(
                array_filter($this->visibleCalendars, fn ($id) => $id !== $calendarId)
            );
        } else {
            $this->visibleCalendars[] = $calendarId;
        }
    }

    public function selectAppointment(int $appointmentId): void
    {
        $this->selectedAppointment = $appointmentId;
        $this->showAppointmentModal = true;
    }

    public function closeAppointmentModal(): void
    {
        $this->showAppointmentModal = false;
        $this->selectedAppointment = null;
    }

    public function deleteAppointment(int $appointmentId): void
    {
        $appointment = Appointment::findOrFail($appointmentId);

        if ($appointment->user_id === auth()->id()) {
            $appointment->delete();
            $this->closeAppointmentModal();
            session()->flash('success', 'Appointment deleted successfully.');
        }
    }

    protected function getAppointmentsForCurrentView()
    {
        // Return empty collection if no calendars are visible
        if (empty($this->visibleCalendars)) {
            return collect();
        }

        [$startDate, $endDate] = $this->getDateRange();

        // Optimize query with select and eager loading
        return Appointment::query()
            ->select([
                'id',
                'calendar_id',
                'user_id',
                'title',
                'description',
                'location',
                'start_datetime',
                'end_datetime',
                'is_all_day',
                'color',
                'status',
            ])
            ->whereIn('calendar_id', $this->visibleCalendars)
            ->where('user_id', auth()->id())
            ->betweenDates($startDate, $endDate)
            ->with(['calendar:id,name,color'])
            ->orderBy('start_datetime', 'asc')
            ->get();
    }

    protected function getDateRange(): array
    {
        return match ($this->viewType) {
            'month' => [
                $this->currentDate->copy()->startOfMonth()->startOfWeek(),
                $this->currentDate->copy()->endOfMonth()->endOfWeek(),
            ],
            'week' => [
                $this->currentDate->copy()->startOfWeek(),
                $this->currentDate->copy()->endOfWeek(),
            ],
            'day' => [
                $this->currentDate->copy()->startOfDay(),
                $this->currentDate->copy()->endOfDay(),
            ],
            'list' => [
                $this->currentDate->copy()->startOfWeek(),
                $this->currentDate->copy()->addWeeks(4)->endOfWeek(),
            ],
        };
    }

    public function getFormattedDateRangeProperty(): string
    {
        return match ($this->viewType) {
            'month' => $this->currentDate->format('F Y'),
            'week' => $this->currentDate->copy()->startOfWeek()->format('M d').
                      ' - '.
                      $this->currentDate->copy()->endOfWeek()->format('M d, Y'),
            'day' => $this->currentDate->format('l, F d, Y'),
            'list' => 'Upcoming Events',
        };
    }
}
