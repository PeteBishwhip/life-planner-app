<?php

namespace App\Livewire;

use App\Models\Appointment;
use App\Models\Calendar;
use App\Services\NaturalLanguageParserService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class QuickAddForm extends Component
{
    public string $input = '';

    public ?array $parsedData = null;

    public bool $showPreview = false;

    public ?int $defaultCalendarId = null;

    protected NaturalLanguageParserService $parser;

    public function boot(NaturalLanguageParserService $parser): void
    {
        $this->parser = $parser;
    }

    public function mount(): void
    {
        // Get user's default calendar
        $defaultCalendar = Calendar::query()
            ->where('user_id', Auth::id())
            ->where('is_default', true)
            ->first();

        if (! $defaultCalendar) {
            $defaultCalendar = Calendar::query()
                ->where('user_id', Auth::id())
                ->first();
        }

        $this->defaultCalendarId = $defaultCalendar?->id;
    }

    public function updatedInput(): void
    {
        if (strlen($this->input) > 3) {
            $this->parseInput();
        } else {
            $this->showPreview = false;
            $this->parsedData = null;
        }
    }

    public function parseInput(): void
    {
        try {
            $this->parsedData = $this->parser->parse($this->input);
            $this->showPreview = true;
        } catch (\Exception $e) {
            $this->showPreview = false;
            $this->parsedData = null;
        }
    }

    public function createAppointment(): void
    {
        if (! $this->parsedData || ! $this->defaultCalendarId) {
            return;
        }

        $appointment = Appointment::create([
            'user_id' => Auth::id(),
            'calendar_id' => $this->defaultCalendarId,
            'title' => $this->parsedData['title'],
            'start_datetime' => $this->parsedData['start_datetime'],
            'end_datetime' => $this->parsedData['end_datetime'],
            'is_all_day' => $this->parsedData['is_all_day'],
            'location' => $this->parsedData['location'],
            'status' => 'scheduled',
        ]);

        $this->dispatch('appointment-created', appointmentId: $appointment->id);
        $this->reset(['input', 'parsedData', 'showPreview']);

        session()->flash('message', 'Appointment created successfully!');
    }

    public function clearInput(): void
    {
        $this->reset(['input', 'parsedData', 'showPreview']);
    }

    public function render()
    {
        $examples = $this->parser->getSupportedFormats();

        return view('livewire.quick-add-form', [
            'examples' => $examples,
        ]);
    }
}
