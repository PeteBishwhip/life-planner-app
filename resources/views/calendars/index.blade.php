<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Calendars') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 rounded-md bg-green-50 p-4">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 rounded-md bg-red-50 p-4">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            @endif

            <div class="overflow-hidden bg-white shadow-sm dark:bg-gray-800 sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-lg font-semibold">Your Calendars</h3>
                        <a href="{{ route('calendars.create') }}" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                            {{ __('New Calendar') }}
                        </a>
                    </div>

                    @if ($calendars->isEmpty())
                        <p class="text-center text-gray-500 dark:text-gray-400">No calendars found.</p>
                    @else
                        <div class="space-y-4">
                            @foreach ($calendars as $calendar)
                                <div class="flex items-center justify-between rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                                    <div class="flex items-center gap-4">
                                        <div class="h-8 w-8 rounded" style="background-color: {{ $calendar->color }}"></div>
                                        <div>
                                            <h4 class="font-semibold">{{ $calendar->name }}</h4>
                                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ ucfirst($calendar->type) }}
                                                @if ($calendar->is_default)
                                                    <span class="ml-2 rounded-full bg-blue-100 px-2 py-1 text-xs font-semibold text-blue-800">Default</span>
                                                @endif
                                                @if (!$calendar->is_visible)
                                                    <span class="ml-2 rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-800">Hidden</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <form method="POST" action="{{ route('calendars.toggle-visibility', $calendar) }}">
                                            @csrf
                                            <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                                                {{ $calendar->is_visible ? 'Hide' : 'Show' }}
                                            </button>
                                        </form>
                                        @if (!$calendar->is_default)
                                            <form method="POST" action="{{ route('calendars.set-default', $calendar) }}">
                                                @csrf
                                                <button type="submit" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">
                                                    Set Default
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('calendars.edit', $calendar) }}" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400">Edit</a>
                                        @if (!$calendar->is_default)
                                            <form method="POST" action="{{ route('calendars.destroy', $calendar) }}" onsubmit="return confirm('Are you sure you want to delete this calendar?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-sm text-red-600 hover:text-red-900 dark:text-red-400">Delete</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
