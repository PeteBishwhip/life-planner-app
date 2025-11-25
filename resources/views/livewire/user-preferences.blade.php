<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Settings & Preferences') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Success Message -->
            @if (session()->has('message'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative dark:bg-green-900 dark:border-green-700 dark:text-green-300" role="alert">
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            @endif

            <!-- General Settings -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">General Settings</h3>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <!-- Timezone -->
                        <div>
                            <label for="timezone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Timezone</label>
                            <select wire:model="timezone" id="timezone" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                                @foreach($timezoneOptions as $tz)
                                    <option value="{{ $tz }}">{{ $tz }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Your local timezone for displaying dates and times</p>
                        </div>

                        <!-- Date Format -->
                        <div>
                            <label for="date_format" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date Format</label>
                            <select wire:model="date_format" id="date_format" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                                @foreach($dateFormatOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Time Format -->
                        <div>
                            <label for="time_format" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Time Format</label>
                            <select wire:model="time_format" id="time_format" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                                @foreach($timeFormatOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Theme -->
                        <div>
                            <label for="theme" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Theme</label>
                            <select wire:model="theme" id="theme" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                                @foreach($themeOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Calendar Settings -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Calendar Settings</h3>

                    <div class="grid gap-6 sm:grid-cols-2">
                        <!-- Default View -->
                        <div>
                            <label for="default_view" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default View</label>
                            <select wire:model="default_view" id="default_view" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                                @foreach($viewOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Initial view when opening calendar</p>
                        </div>

                        <!-- Week Start Day -->
                        <div>
                            <label for="week_start_day" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Week Starts On</label>
                            <select wire:model="week_start_day" id="week_start_day" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                                @foreach($weekStartDayOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Default Appointment Duration -->
                        <div>
                            <label for="default_appointment_duration" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Default Appointment Duration</label>
                            <select wire:model="default_appointment_duration" id="default_appointment_duration" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                                @foreach($durationOptions as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Default length for new appointments</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notification Settings -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Notification Settings</h3>

                    <div class="space-y-4">
                        <!-- Email Notifications -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input wire:model="email_notifications_enabled" id="email_notifications_enabled" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                            </div>
                            <div class="ml-3 text-sm">
                                <label for="email_notifications_enabled" class="font-medium text-gray-700 dark:text-gray-300">Email Notifications</label>
                                <p class="text-gray-500 dark:text-gray-400">Receive email reminders for upcoming appointments</p>
                            </div>
                        </div>

                        <!-- Browser Notifications -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input wire:model="browser_notifications_enabled" id="browser_notifications_enabled" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                            </div>
                            <div class="ml-3 text-sm flex-1">
                                <label for="browser_notifications_enabled" class="font-medium text-gray-700 dark:text-gray-300">Browser Notifications</label>
                                <p class="text-gray-500 dark:text-gray-400">Show browser notifications for reminders</p>

                                <div class="mt-2" x-data="{
                                    permission: '',
                                    checkPermission() {
                                        if ('Notification' in window) {
                                            this.permission = Notification.permission;
                                        } else {
                                            this.permission = 'unsupported';
                                        }
                                    },
                                    async requestPermission() {
                                        const granted = await window.requestNotificationPermission();
                                        this.checkPermission();
                                        if (granted) {
                                            $wire.set('browser_notifications_enabled', true);
                                        }
                                    },
                                    testNotification() {
                                        window.showNotification('Test Notification', {
                                            body: 'This is a test notification from Life Planner!',
                                            tag: 'test-notification'
                                        });
                                    }
                                }" x-init="checkPermission()">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs" :class="{
                                            'text-green-600 dark:text-green-400': permission === 'granted',
                                            'text-yellow-600 dark:text-yellow-400': permission === 'default',
                                            'text-red-600 dark:text-red-400': permission === 'denied',
                                            'text-gray-600 dark:text-gray-400': permission === 'unsupported'
                                        }">
                                            <span x-show="permission === 'granted'">✓ Permission granted</span>
                                            <span x-show="permission === 'default'">⚠ Permission not requested</span>
                                            <span x-show="permission === 'denied'">✗ Permission denied</span>
                                            <span x-show="permission === 'unsupported'">Browser does not support notifications</span>
                                        </span>
                                    </div>

                                    <div class="mt-2 flex gap-2" x-show="permission !== 'unsupported'">
                                        <button @click="requestPermission()" type="button" x-show="permission !== 'granted'" class="rounded-md border border-gray-300 bg-white px-3 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                            Request Permission
                                        </button>

                                        <button @click="testNotification()" type="button" x-show="permission === 'granted'" class="rounded-md border border-gray-300 bg-white px-3 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600">
                                            Test Notification
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Daily Digest -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input wire:model="daily_digest_enabled" id="daily_digest_enabled" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600">
                            </div>
                            <div class="ml-3 text-sm flex-1">
                                <label for="daily_digest_enabled" class="font-medium text-gray-700 dark:text-gray-300">Daily Digest Email</label>
                                <p class="text-gray-500 dark:text-gray-400">Receive a daily summary of your schedule</p>

                                @if($daily_digest_enabled)
                                    <div class="mt-2">
                                        <label for="daily_digest_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Send Time</label>
                                        <input wire:model="daily_digest_time" type="time" id="daily_digest_time" class="mt-1 block w-40 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm">
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-between gap-4">
                <button wire:click="resetToDefaults" type="button" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600" onclick="return confirm('Are you sure you want to reset all preferences to defaults?')">
                    Reset to Defaults
                </button>

                <button wire:click="save" type="button" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    Save Preferences
                </button>
            </div>
        </div>
    </div>
</div>
