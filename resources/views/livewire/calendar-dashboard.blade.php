<div class="space-y-4 md:space-y-6">
    <!-- View Controls -->
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <!-- View Type Buttons - More compact on mobile -->
        <div class="flex items-center gap-1 sm:gap-2">
            <button wire:click="changeView('month')" class="min-h-[44px] flex-1 rounded-md px-3 py-2 text-xs font-semibold sm:flex-none sm:px-4 sm:text-sm {{ $viewType === 'month' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} transition-colors">
                Month
            </button>
            <button wire:click="changeView('week')" class="min-h-[44px] flex-1 rounded-md px-3 py-2 text-xs font-semibold sm:flex-none sm:px-4 sm:text-sm {{ $viewType === 'week' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} transition-colors">
                Week
            </button>
            <button wire:click="changeView('day')" class="min-h-[44px] flex-1 rounded-md px-3 py-2 text-xs font-semibold sm:flex-none sm:px-4 sm:text-sm {{ $viewType === 'day' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} transition-colors">
                Day
            </button>
            <button wire:click="changeView('list')" class="min-h-[44px] flex-1 rounded-md px-3 py-2 text-xs font-semibold sm:flex-none sm:px-4 sm:text-sm {{ $viewType === 'list' ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }} transition-colors">
                List
            </button>
        </div>

        <!-- Navigation Buttons - Touch-friendly -->
        <div class="flex items-center gap-2">
            <button wire:click="previous" class="min-h-[44px] flex-1 rounded-md bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 sm:flex-none sm:px-4 sm:text-sm transition-colors">
                <span class="sm:hidden">←</span>
                <span class="hidden sm:inline">← Previous</span>
            </button>
            <button wire:click="today" class="min-h-[44px] flex-1 rounded-md bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 sm:flex-none sm:px-4 sm:text-sm transition-colors">
                Today
            </button>
            <button wire:click="next" class="min-h-[44px] flex-1 rounded-md bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 sm:flex-none sm:px-4 sm:text-sm transition-colors">
                <span class="sm:hidden">→</span>
                <span class="hidden sm:inline">Next →</span>
            </button>
        </div>
    </div>

    <!-- Current Date Range -->
    <div class="text-center">
        <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100 sm:text-xl md:text-2xl">{{ $this->formattedDateRange }}</h2>
    </div>

    <!-- Calendar Filters - Mobile-optimized with collapsible on small screens -->
    <div class="rounded-lg bg-white p-3 shadow dark:bg-gray-800 md:p-4" x-data="{ filtersOpen: true }">
        <button @click="filtersOpen = !filtersOpen" class="flex w-full items-center justify-between md:pointer-events-none">
            <h3 class="font-semibold text-gray-900 dark:text-gray-100">Calendars</h3>
            <svg x-show="!filtersOpen" class="h-5 w-5 text-gray-500 md:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
            <svg x-show="filtersOpen" class="h-5 w-5 text-gray-500 md:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
            </svg>
        </button>
        <div x-show="filtersOpen" x-collapse class="mt-3">
            <div class="flex flex-wrap gap-2">
                @foreach ($calendars as $calendar)
                    <button
                        wire:click="toggleCalendar({{ $calendar->id }})"
                        class="flex min-h-[44px] items-center gap-2 rounded-md border border-gray-300 px-3 py-2 text-sm transition-colors {{ in_array($calendar->id, $visibleCalendars) ? 'bg-gray-100 dark:bg-gray-700' : 'bg-white dark:bg-gray-800' }} dark:border-gray-600"
                    >
                        <div class="h-4 w-4 flex-shrink-0 rounded" style="background-color: {{ $calendar->color }}"></div>
                        <span class="text-gray-900 dark:text-gray-100">{{ $calendar->name }}</span>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Calendar View Content -->
    <div class="rounded-lg bg-white p-3 shadow dark:bg-gray-800 sm:p-4 md:p-6">
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
