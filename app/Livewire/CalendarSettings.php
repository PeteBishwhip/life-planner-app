<?php

namespace App\Livewire;

use App\Models\Calendar;
use Livewire\Component;

class CalendarSettings extends Component
{
    public ?int $calendarId = null;
    public string $name = '';
    public string $type = 'personal';
    public string $color = '#3B82F6';
    public bool $is_visible = true;
    public bool $is_default = false;
    public string $description = '';

    public bool $isOpen = false;
    public bool $isEditing = false;

    protected function rules(): array
    {
        return Calendar::rules();
    }

    public function render()
    {
        $calendars = auth()->user()->calendars()->orderBy('is_default', 'desc')->get();

        return view('livewire.calendar-settings', [
            'calendars' => $calendars,
        ]);
    }

    public function open(?int $calendarId = null): void
    {
        $this->resetForm();

        if ($calendarId) {
            $this->loadCalendar($calendarId);
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

        if ($this->isEditing && $this->calendarId) {
            $calendar = Calendar::findOrFail($this->calendarId);

            if ($calendar->user_id !== auth()->id()) {
                session()->flash('error', 'Unauthorized action.');
                return;
            }

            $calendar->update($validated);
            session()->flash('success', 'Calendar updated successfully.');
        } else {
            Calendar::create($validated);
            session()->flash('success', 'Calendar created successfully.');
        }

        $this->close();
        $this->dispatch('calendar-saved');
    }

    public function delete(int $calendarId): void
    {
        $calendar = Calendar::findOrFail($calendarId);

        if ($calendar->user_id !== auth()->id()) {
            session()->flash('error', 'Unauthorized action.');
            return;
        }

        if ($calendar->is_default) {
            session()->flash('error', 'Cannot delete the default calendar.');
            return;
        }

        $calendar->delete();
        session()->flash('success', 'Calendar deleted successfully.');

        $this->dispatch('calendar-deleted');
    }

    public function toggleVisibility(int $calendarId): void
    {
        $calendar = Calendar::findOrFail($calendarId);

        if ($calendar->user_id !== auth()->id()) {
            return;
        }

        $calendar->update(['is_visible' => !$calendar->is_visible]);

        $this->dispatch('calendar-visibility-toggled');
    }

    public function setAsDefault(int $calendarId): void
    {
        $calendar = Calendar::findOrFail($calendarId);

        if ($calendar->user_id !== auth()->id()) {
            return;
        }

        $calendar->update(['is_default' => true]);

        session()->flash('success', 'Default calendar updated.');
        $this->dispatch('calendar-default-changed');
    }

    public function onTypeChanged(): void
    {
        // Update color based on type if it hasn't been customized
        $this->color = match($this->type) {
            'personal' => '#3B82F6', // Blue
            'business' => '#10B981', // Green
            'custom' => '#8B5CF6',   // Purple
            default => '#6B7280',     // Gray
        };
    }

    protected function loadCalendar(int $calendarId): void
    {
        $calendar = Calendar::findOrFail($calendarId);

        if ($calendar->user_id !== auth()->id()) {
            return;
        }

        $this->isEditing = true;
        $this->calendarId = $calendar->id;
        $this->name = $calendar->name;
        $this->type = $calendar->type;
        $this->color = $calendar->color;
        $this->is_visible = $calendar->is_visible;
        $this->is_default = $calendar->is_default;
        $this->description = $calendar->description ?? '';
    }

    protected function resetForm(): void
    {
        $this->reset([
            'calendarId',
            'name',
            'description',
            'isEditing',
        ]);

        $this->type = 'personal';
        $this->color = '#3B82F6';
        $this->is_visible = true;
        $this->is_default = false;
    }
}
