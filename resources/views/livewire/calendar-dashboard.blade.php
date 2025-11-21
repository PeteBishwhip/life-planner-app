<div class="space-y-6">
    <!-- View Controls -->
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <button wire:click="changeView('month')" class="rounded-md px-4 py-2 text-sm font-semibold {{ $viewType === 'month' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                Month
            </button>
            <button wire:click="changeView('week')" class="rounded-md px-4 py-2 text-sm font-semibold {{ $viewType === 'week' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                Week
            </button>
            <button wire:click="changeView('day')" class="rounded-md px-4 py-2 text-sm font-semibold {{ $viewType === 'day' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                Day
            </button>
            <button wire:click="changeView('list')" class="rounded-md px-4 py-2 text-sm font-semibold {{ $viewType === 'list' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                List
            </button>
        </div>

        <div class="flex items-center gap-4">
            <button wire:click="previous" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                ← Previous
            </button>
            <button wire:click="today" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Today
            </button>
            <button wire:click="next" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
                Next →
            </button>
        </div>
    </div>

    <!-- Current Date Range -->
    <div class="text-center">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $this->formattedDateRange }}</h2>
    </div>

    <!-- Calendar Filters -->
    <div class="rounded-lg bg-white p-4 shadow dark:bg-gray-800">
        <h3 class="mb-3 font-semibold text-gray-900 dark:text-gray-100">Calendars</h3>
        <div class="flex flex-wrap gap-3">
            @foreach ($calendars as $calendar)
                <button
                    wire:click="toggleCalendar({{ $calendar->id }})"
                    class="flex items-center gap-2 rounded-md px-3 py-2 text-sm {{ in_array($calendar->id, $visibleCalendars) ? 'bg-gray-100 dark:bg-gray-700' : 'bg-white dark:bg-gray-800' }} border border-gray-300 dark:border-gray-600"
                >
                    <div class="h-4 w-4 rounded" style="background-color: {{ $calendar->color }}"></div>
                    <span class="text-gray-900 dark:text-gray-100">{{ $calendar->name }}</span>
                </button>
            @endforeach
        </div>
    </div>

    <!-- Calendar View Content -->
    <div class="rounded-lg bg-white p-6 shadow dark:bg-gray-800">
        @if ($viewType === 'month')
            <livewire:month-view :currentDate="$currentDate" :appointments="$appointments" :visibleCalendars="$visibleCalendars" :key="'month-' . $currentDate->timestamp" />
        @elseif ($viewType === 'week')
            <livewire:week-view :currentDate="$currentDate" :appointments="$appointments" :visibleCalendars="$visibleCalendars" :key="'week-' . $currentDate->timestamp" />
        @elseif ($viewType === 'day')
            <livewire:day-view :currentDate="$currentDate" :appointments="$appointments" :visibleCalendars="$visibleCalendars" :key="'day-' . $currentDate->timestamp" />
        @else
            <div class="space-y-4">
                @forelse ($appointments as $appointment)
                    <div class="flex items-center justify-between rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                        <div class="flex items-center gap-3">
                            <div class="h-3 w-3 rounded-full" style="background-color: {{ $appointment->color }}"></div>
                            <div>
                                <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $appointment->title }}</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $appointment->start_datetime->format('M d, Y g:i A') }}
                                </p>
                            </div>
                        </div>
                        <button wire:click="selectAppointment({{ $appointment->id }})" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                            View
                        </button>
                    </div>
                @empty
                    <p class="text-center text-gray-500 dark:text-gray-400">No appointments found.</p>
                @endforelse
            </div>
        @endif
    </div>
</div>
