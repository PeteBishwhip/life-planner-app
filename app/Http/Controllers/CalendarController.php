<?php

namespace App\Http\Controllers;

use App\Models\Calendar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarController extends Controller
{
    /**
     * Display a listing of the user's calendars.
     */
    public function index(): View
    {
        $calendars = auth()->user()
            ->calendars()
            ->with(['appointments' => function ($query) {
                $query->upcoming()->limit(5);
            }])
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('calendars.index', compact('calendars'));
    }

    /**
     * Show the form for creating a new calendar.
     */
    public function create(): View
    {
        return view('calendars.create');
    }

    /**
     * Store a newly created calendar in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate(Calendar::rules());

        $calendar = auth()->user()->calendars()->create($validated);

        return redirect()
            ->route('calendars.index')
            ->with('success', 'Calendar created successfully.');
    }

    /**
     * Display the specified calendar.
     */
    public function show(Calendar $calendar): View
    {
        $this->authorize('view', $calendar);

        $calendar->load(['appointments' => function ($query) {
            $query->scheduled()->orderBy('start_datetime', 'asc');
        }]);

        return view('calendars.show', compact('calendar'));
    }

    /**
     * Show the form for editing the specified calendar.
     */
    public function edit(Calendar $calendar): View
    {
        $this->authorize('update', $calendar);

        return view('calendars.edit', compact('calendar'));
    }

    /**
     * Update the specified calendar in storage.
     */
    public function update(Request $request, Calendar $calendar): RedirectResponse
    {
        $this->authorize('update', $calendar);

        $validated = $request->validate(Calendar::rules(true));

        $calendar->update($validated);

        return redirect()
            ->route('calendars.index')
            ->with('success', 'Calendar updated successfully.');
    }

    /**
     * Remove the specified calendar from storage.
     */
    public function destroy(Calendar $calendar): RedirectResponse
    {
        $this->authorize('delete', $calendar);

        // Prevent deletion of the default calendar
        if ($calendar->is_default) {
            return redirect()
                ->route('calendars.index')
                ->with('error', 'Cannot delete the default calendar.');
        }

        // Optionally, you might want to handle appointments before deleting
        // For now, we'll rely on database cascade or manually delete
        $calendar->delete();

        return redirect()
            ->route('calendars.index')
            ->with('success', 'Calendar deleted successfully.');
    }

    /**
     * Toggle calendar visibility.
     */
    public function toggleVisibility(Calendar $calendar): RedirectResponse
    {
        $this->authorize('update', $calendar);

        $calendar->update(['is_visible' => !$calendar->is_visible]);

        return redirect()
            ->back()
            ->with('success', 'Calendar visibility updated.');
    }

    /**
     * Set a calendar as the default calendar.
     */
    public function setDefault(Calendar $calendar): RedirectResponse
    {
        $this->authorize('update', $calendar);

        $calendar->update(['is_default' => true]);

        return redirect()
            ->back()
            ->with('success', 'Default calendar updated.');
    }
}
