<div>
    @if($isOpen)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="close"></div>

                <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle">
                    <form wire:submit="save">
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
                                {{ $isEditing ? 'Edit Appointment' : 'Create Appointment' }}
                            </h3>

                            <!-- Calendar Selection -->
                            <div class="mb-4">
                                <label for="calendar_id" class="block text-sm font-medium text-gray-700">Calendar</label>
                                <select wire:model.live="calendar_id" id="calendar_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    @foreach($calendars as $calendar)
                                        <option value="{{ $calendar->id }}">{{ $calendar->name }}</option>
                                    @endforeach
                                </select>
                                @error('calendar_id') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <!-- Title -->
                            <div class="mb-4">
                                <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                                <input type="text" wire:model="title" id="title" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('title') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <!-- Description -->
                            <div class="mb-4">
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea wire:model="description" id="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                @error('description') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <!-- Location -->
                            <div class="mb-4">
                                <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                                <input type="text" wire:model="location" id="location" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('location') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <!-- All Day Toggle -->
                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model.live="is_all_day" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700">All day event</span>
                                </label>
                            </div>

                            <!-- Start Date/Time -->
                            <div class="mb-4">
                                <label for="start_datetime" class="block text-sm font-medium text-gray-700">Start</label>
                                <input type="{{ $is_all_day ? 'date' : 'datetime-local' }}" wire:model="start_datetime" id="start_datetime" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('start_datetime') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <!-- End Date/Time -->
                            <div class="mb-4">
                                <label for="end_datetime" class="block text-sm font-medium text-gray-700">End</label>
                                <input type="{{ $is_all_day ? 'date' : 'datetime-local' }}" wire:model="end_datetime" id="end_datetime" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @error('end_datetime') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                            </div>

                            <!-- Recurring Toggle -->
                            <div class="mb-4">
                                <label class="flex items-center">
                                    <input type="checkbox" wire:model.live="is_recurring" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                    <span class="ml-2 text-sm text-gray-700">Recurring event</span>
                                </label>
                            </div>

                            @if($is_recurring)
                                <!-- Recurrence Options -->
                                <div class="mb-4 border-l-4 border-indigo-500 pl-4">
                                    <div class="mb-2">
                                        <label for="recurrence_frequency" class="block text-sm font-medium text-gray-700">Frequency</label>
                                        <select wire:model="recurrence_frequency" id="recurrence_frequency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                            <option value="daily">Daily</option>
                                            <option value="weekly">Weekly</option>
                                            <option value="monthly">Monthly</option>
                                            <option value="yearly">Yearly</option>
                                        </select>
                                    </div>

                                    <div class="mb-2">
                                        <label for="recurrence_interval" class="block text-sm font-medium text-gray-700">Every</label>
                                        <input type="number" wire:model="recurrence_interval" id="recurrence_interval" min="1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>

                                    <div class="mb-2">
                                        <label for="recurrence_end_date" class="block text-sm font-medium text-gray-700">Until (optional)</label>
                                        <input type="date" wire:model="recurrence_end_date" id="recurrence_end_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    </div>
                                </div>
                            @endif

                            <!-- Reminders -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Reminders</label>
                                @foreach($available_reminders as $minutes => $label)
                                    <label class="flex items-center mb-1">
                                        <input type="checkbox" wire:model="reminder_minutes" value="{{ $minutes }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                                        <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <!-- Conflict Warning -->
                            @if($show_conflicts && count($conflicts) > 0)
                                <div class="mb-4 rounded-md bg-yellow-50 p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-yellow-800">Schedule Conflict</h3>
                                            <div class="mt-2 text-sm text-yellow-700">
                                                <p>This appointment conflicts with {{ count($conflicts) }} existing appointment(s).</p>
                                            </div>
                                            <div class="mt-4">
                                                <button type="button" wire:click="overrideConflicts" class="text-sm font-medium text-yellow-800 hover:text-yellow-700">
                                                    Schedule anyway →
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit" class="inline-flex w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 sm:ml-3 sm:w-auto">
                                {{ $isEditing ? 'Update' : 'Create' }}
                            </button>

                            @if($isEditing)
                                <button type="button" wire:click="delete" class="mt-3 inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:mt-0 sm:w-auto">
                                    Delete
                                </button>
                            @endif

                            <button type="button" wire:click="close" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
