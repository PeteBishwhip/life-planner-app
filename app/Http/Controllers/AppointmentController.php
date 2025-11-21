<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Calendar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the user's appointments.
     */
    public function index(Request $request): View
    {
        $query = auth()->user()
            ->appointments()
            ->with(['calendar'])
            ->orderBy('start_datetime', 'desc');

        // Apply filters
        if ($request->has('calendar_id')) {
            $query->where('calendar_id', $request->calendar_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from') && $request->has('date_to')) {
            $query->betweenDates($request->date_from, $request->date_to);
        }

        $appointments = $query->paginate(20);
        $calendars = auth()->user()->calendars;

        return view('appointments.index', compact('appointments', 'calendars'));
    }

    /**
     * Show the form for creating a new appointment.
     */
    public function create(Request $request): View
    {
        $calendars = auth()->user()->calendars()->visible()->get();
        $defaultCalendar = $calendars->firstWhere('is_default', true);

        $preselectedDate = $request->has('date') ? $request->date : now()->toDateString();
        $preselectedCalendar = $request->has('calendar_id')
            ? $request->calendar_id
            : ($defaultCalendar ? $defaultCalendar->id : null);

        return view('appointments.create', compact('calendars', 'preselectedDate', 'preselectedCalendar'));
    }

    /**
     * Store a newly created appointment in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(Appointment::rules());

        // Verify calendar belongs to user
        $calendar = Calendar::findOrFail($validated['calendar_id']);
        $this->authorize('view', $calendar);

        // Check for conflicts if needed
        if ($request->has('check_conflicts') && $request->check_conflicts) {
            $hasConflict = (new Appointment)->hasConflict(
                $validated['calendar_id'],
                $validated['start_datetime'],
                $validated['end_datetime']
            );

            if ($hasConflict) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('warning', 'There is a conflicting appointment during this time.');
            }
        }

        $appointment = auth()->user()->appointments()->create($validated);

        return redirect()
            ->route('calendar.dashboard')
            ->with('success', 'Appointment created successfully.');
    }

    /**
     * Display the specified appointment.
     */
    public function show(Appointment $appointment): View
    {
        $this->authorize('view', $appointment);

        $appointment->load(['calendar', 'reminders']);

        return view('appointments.show', compact('appointment'));
    }

    /**
     * Show the form for editing the specified appointment.
     */
    public function edit(Appointment $appointment): View
    {
        $this->authorize('update', $appointment);

        $calendars = auth()->user()->calendars()->visible()->get();

        return view('appointments.edit', compact('appointment', 'calendars'));
    }

    /**
     * Update the specified appointment in storage.
     */
    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('update', $appointment);

        $validated = $request->validate(Appointment::rules(true));

        // Verify calendar belongs to user if changed
        $calendar = Calendar::findOrFail($validated['calendar_id']);
        $this->authorize('view', $calendar);

        // Check for conflicts (excluding current appointment)
        if ($request->has('check_conflicts') && $request->check_conflicts) {
            $hasConflict = (new Appointment)->hasConflict(
                $validated['calendar_id'],
                $validated['start_datetime'],
                $validated['end_datetime'],
                $appointment->id
            );

            if ($hasConflict) {
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('warning', 'There is a conflicting appointment during this time.');
            }
        }

        $appointment->update($validated);

        return redirect()
            ->route('calendar.dashboard')
            ->with('success', 'Appointment updated successfully.');
    }

    /**
     * Remove the specified appointment from storage.
     */
    public function destroy(Appointment $appointment): RedirectResponse
    {
        $this->authorize('delete', $appointment);

        $appointment->delete();

        return redirect()
            ->route('calendar.dashboard')
            ->with('success', 'Appointment deleted successfully.');
    }

    /**
     * Mark the appointment as completed.
     */
    public function complete(Appointment $appointment): RedirectResponse
    {
        $this->authorize('update', $appointment);

        $appointment->update(['status' => 'completed']);

        return redirect()
            ->back()
            ->with('success', 'Appointment marked as completed.');
    }

    /**
     * Cancel the appointment.
     */
    public function cancel(Appointment $appointment): RedirectResponse
    {
        $this->authorize('update', $appointment);

        $appointment->update(['status' => 'cancelled']);

        return redirect()
            ->back()
            ->with('success', 'Appointment cancelled.');
    }
}
