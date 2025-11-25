<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Search Appointments') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Search & Filters Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 space-y-4">
                    <!-- Search Input -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Search</label>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            id="search"
                            placeholder="Search by title, description, or location..."
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                        />
                    </div>

                    <!-- Quick Filters -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Quick Filters</label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                wire:click="$set('quick_filter', 'today')"
                                class="rounded-md px-3 py-1 text-sm font-semibold {{ $quick_filter === 'today' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }} transition"
                            >
                                Today
                            </button>
                            <button
                                wire:click="$set('quick_filter', 'this_week')"
                                class="rounded-md px-3 py-1 text-sm font-semibold {{ $quick_filter === 'this_week' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }} transition"
                            >
                                This Week
                            </button>
                            <button
                                wire:click="$set('quick_filter', 'this_month')"
                                class="rounded-md px-3 py-1 text-sm font-semibold {{ $quick_filter === 'this_month' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }} transition"
                            >
                                This Month
                            </button>
                            <button
                                wire:click="$set('quick_filter', 'upcoming')"
                                class="rounded-md px-3 py-1 text-sm font-semibold {{ $quick_filter === 'upcoming' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }} transition"
                            >
                                Upcoming
                            </button>
                        </div>
                    </div>

                    <!-- Advanced Filters -->
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <!-- Calendar Filter -->
                        <div>
                            <label for="calendar_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Calendar</label>
                            <select
                                wire:model.live="calendar_id"
                                id="calendar_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                            >
                                <option value="">All Calendars</option>
                                @foreach($calendars as $calendar)
                                    <option value="{{ $calendar->id }}">{{ $calendar->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select
                                wire:model.live="status"
                                id="status"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                            >
                                <option value="">All Statuses</option>
                                <option value="scheduled">Scheduled</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div class="sm:col-span-2 lg:col-span-1">
                            <button
                                wire:click="clearFilters"
                                class="mt-6 w-full rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                            >
                                Clear All Filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
                        Results ({{ $appointments->total() }})
                    </h3>

                    @if($appointments->isEmpty())
                        <div class="text-center py-12">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No appointments found</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Try adjusting your search or filters.</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($appointments as $appointment)
                                <div class="flex items-center border-l-4 border rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-700 transition" style="border-left-color: {{ $appointment->calendar->color }};">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <h4 class="font-semibold text-gray-900 dark:text-gray-100">{{ $appointment->title }}</h4>
                                            <span class="inline-flex items-center rounded-full px-2 py-1 text-xs font-medium
                                                {{ $appointment->status === 'scheduled' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300' : '' }}
                                                {{ $appointment->status === 'completed' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : '' }}
                                                {{ $appointment->status === 'cancelled' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' : '' }}
                                            ">
                                                {{ ucfirst($appointment->status) }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            {{ $appointment->start_datetime->format('D, M j, Y \a\t g:i A') }}
                                            @if(!$appointment->is_all_day)
                                                - {{ $appointment->end_datetime->format('g:i A') }}
                                            @endif
                                        </p>
                                        <div class="flex items-center gap-4 mt-2 text-xs text-gray-500 dark:text-gray-500">
                                            <span>📅 {{ $appointment->calendar->name }}</span>
                                            @if($appointment->location)
                                                <span>📍 {{ $appointment->location }}</span>
                                            @endif
                                        </div>
                                        @if($appointment->description)
                                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ Str::limit($appointment->description, 100) }}</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('appointments.edit', $appointment) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $appointments->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
