<?php

namespace App\Livewire;

use App\Services\SearchService;
use Livewire\Component;
use Livewire\WithPagination;

class SearchAppointments extends Component
{
    use WithPagination;

    public string $search = '';

    public ?string $calendar_id = null;

    public ?string $status = null;

    public ?string $location = null;

    public ?string $color = null;

    public ?string $start_date = null;

    public ?string $end_date = null;

    public ?string $quick_filter = null;

    protected SearchService $searchService;

    public function boot(SearchService $searchService): void
    {
        $this->searchService = $searchService;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCalendarId(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedQuickFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->reset([
            'search',
            'calendar_id',
            'status',
            'location',
            'color',
            'start_date',
            'end_date',
            'quick_filter',
        ]);
        $this->resetPage();
    }

    public function render()
    {
        $filters = [
            'search' => $this->search,
            'calendar_id' => $this->calendar_id,
            'status' => $this->status,
            'location' => $this->location,
            'color' => $this->color,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'quick_filter' => $this->quick_filter,
        ];

        $appointments = $this->searchService
            ->search(auth()->id(), $filters)
            ->orderBy('start_datetime', 'desc')
            ->paginate(15);

        $calendars = auth()->user()->calendars()->get();

        return view('livewire.search-appointments', [
            'appointments' => $appointments,
            'calendars' => $calendars,
        ]);
    }
}
