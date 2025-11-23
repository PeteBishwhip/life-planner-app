<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Life Planner - Smart Calendar Management</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased font-sans">
        <div class="bg-white dark:bg-gray-900">
            <!-- Navigation -->
            <nav class="border-b border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 items-center justify-between">
                        <div class="flex items-center gap-2">
                            <svg class="h-8 w-8 text-indigo-600 dark:text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-xl font-bold text-gray-900 dark:text-white">Life Planner</span>
                        </div>
                        @if (Route::has('login'))
                            <livewire:welcome.navigation />
                        @endif
                    </div>
                </div>
            </nav>

            <!-- Hero Section -->
            <div class="relative overflow-hidden">
                <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8 lg:py-32">
                    <div class="text-center">
                        <h1 class="text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-5xl md:text-6xl">
                            <span class="block">Your Life, Perfectly</span>
                            <span class="block text-indigo-600 dark:text-indigo-500">Organized</span>
                        </h1>
                        <p class="mx-auto mt-6 max-w-2xl text-lg text-gray-600 dark:text-gray-400">
                            A modern, intelligent calendar app that helps you manage multiple calendars with smart conflict detection, recurring appointments, and seamless integrations.
                        </p>
                        <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                            @auth
                                <a href="{{ url('/calendar') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                    Go to Calendar
                                    <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-6 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                    Get Started Free
                                    <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </a>
                                <a href="{{ route('login') }}" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-6 py-3 text-base font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                                    Sign In
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>

            <!-- Features Grid -->
            <div class="bg-gray-50 py-16 dark:bg-gray-800 sm:py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                            Everything you need to stay organized
                        </h2>
                        <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                            Powerful features designed to make calendar management effortless
                        </p>
                    </div>

                    <div class="mt-16 grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                        <!-- Feature 1 -->
                        <div class="rounded-lg bg-white p-6 shadow-sm transition hover:shadow-md dark:bg-gray-900">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-600">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Smart Conflict Detection</h3>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">
                                Automatically detects and prevents scheduling conflicts across all your calendars. Business appointments block personal time and vice versa.
                            </p>
                        </div>

                        <!-- Feature 2 -->
                        <div class="rounded-lg bg-white p-6 shadow-sm transition hover:shadow-md dark:bg-gray-900">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-600">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Multiple Calendars</h3>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">
                                Create and manage personal, business, and custom calendars. Color-code and toggle visibility with ease.
                            </p>
                        </div>

                        <!-- Feature 3 -->
                        <div class="rounded-lg bg-white p-6 shadow-sm transition hover:shadow-md dark:bg-gray-900">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-600">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Recurring Appointments</h3>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">
                                Set up daily, weekly, monthly, or yearly recurring events with custom intervals and end dates.
                            </p>
                        </div>

                        <!-- Feature 4 -->
                        <div class="rounded-lg bg-white p-6 shadow-sm transition hover:shadow-md dark:bg-gray-900">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-600">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Multiple Views</h3>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">
                                Switch between month, week, day, and list views to see your schedule exactly how you want it.
                            </p>
                        </div>

                        <!-- Feature 5 -->
                        <div class="rounded-lg bg-white p-6 shadow-sm transition hover:shadow-md dark:bg-gray-900">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-600">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Smart Reminders</h3>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">
                                Never miss an appointment with email and browser notifications. Get daily digest emails of your schedule.
                            </p>
                        </div>

                        <!-- Feature 6 -->
                        <div class="rounded-lg bg-white p-6 shadow-sm transition hover:shadow-md dark:bg-gray-900">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-600">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">Import & Export</h3>
                            <p class="mt-2 text-gray-600 dark:text-gray-400">
                                Seamlessly import from Google Calendar and Outlook. Export to ICS, CSV, or PDF formats.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Features Section -->
            <div class="bg-white py-16 dark:bg-gray-900 sm:py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="grid gap-16 lg:grid-cols-2 lg:gap-24">
                        <!-- Natural Language -->
                        <div class="flex flex-col justify-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-600">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">Quick Add with Natural Language</h3>
                            <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                                Create appointments in seconds using natural language. Just type "Team meeting tomorrow at 2pm" and we'll handle the rest.
                            </p>
                        </div>

                        <!-- Keyboard Shortcuts -->
                        <div class="flex flex-col justify-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-indigo-600">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                                </svg>
                            </div>
                            <h3 class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">Powerful Keyboard Shortcuts</h3>
                            <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                                Navigate your calendar at lightning speed with keyboard shortcuts. Create, search, and navigate without touching your mouse.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile-First Section -->
            <div class="bg-indigo-600 py-16 dark:bg-indigo-700 sm:py-24">
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <h2 class="mt-4 text-3xl font-bold text-white sm:text-4xl">
                            Works Beautifully Everywhere
                        </h2>
                        <p class="mx-auto mt-4 max-w-2xl text-lg text-indigo-100">
                            Life Planner is built mobile-first with a responsive design that works perfectly on phones, tablets, and desktops. Your calendar, always in your pocket.
                        </p>
                    </div>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="bg-white dark:bg-gray-900">
                <div class="mx-auto max-w-7xl px-4 py-16 sm:px-6 sm:py-24 lg:px-8">
                    <div class="text-center">
                        <h2 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                            Ready to organize your life?
                        </h2>
                        <p class="mx-auto mt-4 max-w-2xl text-lg text-gray-600 dark:text-gray-400">
                            Join thousands of users who have transformed the way they manage their time.
                        </p>
                        <div class="mt-10">
                            @guest
                                <a href="{{ route('register') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-8 py-4 text-lg font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                    Get Started Free
                                    <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </a>
                            @else
                                <a href="{{ url('/calendar') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-8 py-4 text-lg font-semibold text-white shadow-sm transition hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                                    Go to Your Calendar
                                    <svg class="ml-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                    </svg>
                                </a>
                            @endguest
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="border-t border-gray-200 bg-gray-50 dark:border-gray-800 dark:bg-gray-800">
                <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
                    <div class="flex flex-col items-center justify-between gap-4 sm:flex-row">
                        <div class="flex items-center gap-2">
                            <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">Life Planner</span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            &copy; {{ date('Y') }} Life Planner. All rights reserved.
                        </p>
                    </div>
                </div>
            </footer>
        </div>
    </body>
</html>
