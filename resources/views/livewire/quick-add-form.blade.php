<div x-data="{ open: false }" x-on:keyboard-shortcut.window="if ($event.detail.action === 'quick-add') open = true">
    <!-- Quick Add Button -->
    <button @click="open = true" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
        <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        Quick Add
    </button>

    <!-- Modal -->
    <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center" style="display: none;">
        <div class="absolute inset-0 bg-gray-900/50" @click="open = false"></div>

        <div class="relative w-full max-w-lg mx-4 rounded-lg bg-white shadow-lg dark:bg-gray-800">
            <!-- Header -->
            <div class="flex items-center justify-between border-b px-5 py-4 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Quick Add Appointment</h3>
                <button class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" @click="open = false">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <div class="px-5 py-4">
                <div class="space-y-4">
                    <!-- Natural Language Input -->
                    <div>
                        <label for="quick-add-input" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Describe your appointment
                        </label>
                        <input
                            wire:model.live.debounce.300ms="input"
                            type="text"
                            id="quick-add-input"
                            placeholder="e.g., Team meeting tomorrow at 2pm"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm"
                            @keydown.enter="$wire.createAppointment(); open = false"
                        />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            Use natural language like "Dentist appointment Friday at 10am for 1 hour"
                        </p>
                    </div>

                    <!-- Preview -->
                    @if($showPreview && $parsedData)
                        <div class="rounded-md bg-indigo-50 p-4 dark:bg-indigo-900/20">
                            <h4 class="text-sm font-medium text-indigo-900 dark:text-indigo-300 mb-2">Preview:</h4>
                            <div class="space-y-1 text-sm text-indigo-700 dark:text-indigo-400">
                                <p><strong>Title:</strong> {{ $parsedData['title'] }}</p>
                                <p><strong>Start:</strong> {{ \Carbon\Carbon::parse($parsedData['start_datetime'])->format('D, M j, Y \a\t g:i A') }}</p>
                                <p><strong>End:</strong> {{ \Carbon\Carbon::parse($parsedData['end_datetime'])->format('g:i A') }}</p>
                                @if($parsedData['location'] ?? null)
                                    <p><strong>Location:</strong> {{ $parsedData['location'] }}</p>
                                @endif
                                @if($parsedData['is_all_day'])
                                    <p class="text-indigo-600 dark:text-indigo-400">All Day Event</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Examples -->
                    <div class="rounded-md bg-gray-50 p-3 dark:bg-gray-700">
                        <h4 class="text-xs font-semibold text-gray-700 dark:text-gray-300 mb-2">Examples:</h4>
                        <ul class="space-y-1 text-xs text-gray-600 dark:text-gray-400">
                            @foreach($examples as $example)
                                <li>• {{ $example }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end gap-3 border-t px-5 py-3 dark:border-gray-700">
                <button
                    type="button"
                    @click="open = false; $wire.clearInput()"
                    class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600"
                >
                    Cancel
                </button>
                <button
                    wire:click="createAppointment"
                    @click="open = false"
                    :disabled="!@js($showPreview)"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Create Appointment
                </button>
            </div>
        </div>
    </div>
</div>
