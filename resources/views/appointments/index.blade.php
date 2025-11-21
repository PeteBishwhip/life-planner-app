<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                {{ __('Appointments') }}
            </h2>
            <a href="{{ route('appointments.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                {{ __('New Appointment') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            <!-- Filters -->
            <div class="mb-4 overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                <div class="p-6">
                    <form method="GET" action="{{ route('appointments.index') }}" class="flex flex-wrap gap-4">
                        <div>
                            <label for="calendar_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Calendar</label>
                            <select name="calendar_id" id="calendar_id" class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="">All Calendars</option>
                                @foreach ($calendars as $calendar)
                                    <option value="{{ $calendar->id }}" {{ request('calendar_id') == $calendar->id ? 'selected' : '' }}>
                                        {{ $calendar->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                            <select name="status" id="status" class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
                                <option value="">All Statuses</option>
                                <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>

                        <div class="flex items-end">
                            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Appointments List -->
            <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if ($appointments->isEmpty())
                        <p class="text-center text-gray-500 dark:text-gray-400">No appointments found.</p>
                    @else
                        <div class="space-y-4">
                            @foreach ($appointments as $appointment)
                                <div class="flex items-center justify-between rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3">
                                            <div class="h-3 w-3 rounded-full" style="background-color: {{ $appointment->color }}"></div>
                                            <h3 class="text-lg font-semibold">{{ $appointment->title }}</h3>
                                            <span class="rounded-full px-2 py-1 text-xs font-semibold
                                                {{ $appointment->status === 'scheduled' ? 'bg-blue-100 text-blue-800' : '' }}
                                                {{ $appointment->status === 'completed' ? 'bg-green-100 text-green-800' : '' }}
                                                {{ $appointment->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}">
                                                {{ ucfirst($appointment->status) }}
                                            </span>
                                        </div>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {{ $appointment->start_datetime->format('M d, Y g:i A') }} - {{ $appointment->end_datetime->format('g:i A') }}
                                        </p>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $appointment->calendar->name }}</p>
                                    </div>
                                    <div class="flex gap-2">
                                        <a href="{{ route('appointments.show', $appointment) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">View</a>
                                        <a href="{{ route('appointments.edit', $appointment) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">Edit</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-6">
                            {{ $appointments->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
