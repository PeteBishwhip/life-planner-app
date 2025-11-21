<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ $calendar->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="mb-4">
                <a href="{{ route('calendars.index') }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                    ← Back to Calendars
                </a>
            </div>

            <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6 flex items-center gap-4">
                        <div class="h-12 w-12 rounded" style="background-color: {{ $calendar->color }}"></div>
                        <div>
                            <h3 class="text-2xl font-bold">{{ $calendar->name }}</h3>
                            <p class="text-sm text-gray-500 dark:text-gray-400">{{ ucfirst($calendar->type) }} Calendar</p>
                        </div>
                    </div>

                    @if ($calendar->description)
                        <div class="mb-6">
                            <h4 class="font-semibold text-gray-600 dark:text-gray-400">Description</h4>
                            <p>{{ $calendar->description }}</p>
                        </div>
                    @endif

                    <div class="mb-6">
                        <h4 class="mb-3 font-semibold text-gray-600 dark:text-gray-400">Appointments</h4>
                        @if ($calendar->appointments->isEmpty())
                            <p class="text-gray-500 dark:text-gray-400">No appointments scheduled.</p>
                        @else
                            <div class="space-y-3">
                                @foreach ($calendar->appointments as $appointment)
                                    <div class="flex items-center justify-between rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                                        <div>
                                            <h5 class="font-semibold">{{ $appointment->title }}</h5>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $appointment->start_datetime->format('M d, Y g:i A') }}
                                            </p>
                                        </div>
                                        <a href="{{ route('appointments.show', $appointment) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                                            View
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="flex gap-2">
                        <a href="{{ route('calendars.edit', $calendar) }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            Edit Calendar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
