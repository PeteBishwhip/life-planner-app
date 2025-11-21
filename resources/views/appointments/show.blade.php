<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Appointment Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-4">
                        <a href="{{ route('appointments.index') }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                            ← Back to Appointments
                        </a>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <h3 class="text-2xl font-bold">{{ $appointment->title }}</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $appointment->calendar->name }}</p>
                        </div>

                        <div>
                            <label class="text-sm font-semibold text-gray-600 dark:text-gray-400">Time</label>
                            <p>{{ $appointment->start_datetime->format('M d, Y g:i A') }} - {{ $appointment->end_datetime->format('g:i A') }}</p>
                        </div>

                        @if ($appointment->location)
                            <div>
                                <label class="text-sm font-semibold text-gray-600 dark:text-gray-400">Location</label>
                                <p>{{ $appointment->location }}</p>
                            </div>
                        @endif

                        @if ($appointment->description)
                            <div>
                                <label class="text-sm font-semibold text-gray-600 dark:text-gray-400">Description</label>
                                <p>{{ $appointment->description }}</p>
                            </div>
                        @endif

                        <div>
                            <label class="text-sm font-semibold text-gray-600 dark:text-gray-400">Status</label>
                            <p>{{ ucfirst($appointment->status) }}</p>
                        </div>

                        <div class="flex gap-2">
                            <a href="{{ route('appointments.edit', $appointment) }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                                Edit
                            </a>
                            <form method="POST" action="{{ route('appointments.destroy', $appointment) }}" onsubmit="return confirm('Are you sure?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
