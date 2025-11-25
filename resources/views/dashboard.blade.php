<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-8">
            <!-- Welcome Card with Gradient -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 p-8 shadow-2xl">
                <div class="absolute inset-0 bg-black/10 dark:bg-black/20"></div>
                <div class="relative">
                    <h3 class="text-3xl font-bold text-white">Welcome back, {{ auth()->user()->name }}!</h3>
                    <p class="mt-3 text-lg text-white/90">Here's what's happening with your calendar today.</p>
                </div>
            </div>

            <!-- Quick Stats Grid -->
            @php
                $today = now();
                $todayAppointments = auth()->user()->appointments()
                    ->whereDate('start_datetime', $today)
                    ->where('status', 'scheduled')
                    ->count();

                $upcomingAppointments = auth()->user()->appointments()
                    ->where('start_datetime', '>', $today)
                    ->where('status', 'scheduled')
                    ->count();

                $calendarsCount = auth()->user()->calendars()->count();

                $completedThisMonth = auth()->user()->appointments()
                    ->whereYear('start_datetime', $today->year)
                    ->whereMonth('start_datetime', $today->month)
                    ->where('status', 'completed')
                    ->count();
            @endphp

            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Today's Appointments -->
                <div class="group relative overflow-hidden rounded-xl bg-white dark:bg-gray-800 p-6 shadow-lg ring-1 ring-gray-900/5 dark:ring-gray-700/50 transition-all duration-300 hover:shadow-xl hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 opacity-20 blur-2xl transition-opacity group-hover:opacity-30"></div>
                    <div class="relative flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Today</p>
                            <p class="mt-2 text-4xl font-bold text-gray-900 dark:text-white">{{ $todayAppointments }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">appointments</p>
                        </div>
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-indigo-600 shadow-lg">
                            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Appointments -->
                <div class="group relative overflow-hidden rounded-xl bg-white dark:bg-gray-800 p-6 shadow-lg ring-1 ring-gray-900/5 dark:ring-gray-700/50 transition-all duration-300 hover:shadow-xl hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 opacity-20 blur-2xl transition-opacity group-hover:opacity-30"></div>
                    <div class="relative flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Upcoming</p>
                            <p class="mt-2 text-4xl font-bold text-gray-900 dark:text-white">{{ $upcomingAppointments }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">scheduled</p>
                        </div>
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-lg">
                            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Calendars -->
                <div class="group relative overflow-hidden rounded-xl bg-white dark:bg-gray-800 p-6 shadow-lg ring-1 ring-gray-900/5 dark:ring-gray-700/50 transition-all duration-300 hover:shadow-xl hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 opacity-20 blur-2xl transition-opacity group-hover:opacity-30"></div>
                    <div class="relative flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Calendars</p>
                            <p class="mt-2 text-4xl font-bold text-gray-900 dark:text-white">{{ $calendarsCount }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">active</p>
                        </div>
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-purple-500 to-purple-600 shadow-lg">
                            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Completed This Month -->
                <div class="group relative overflow-hidden rounded-xl bg-white dark:bg-gray-800 p-6 shadow-lg ring-1 ring-gray-900/5 dark:ring-gray-700/50 transition-all duration-300 hover:shadow-xl hover:scale-105">
                    <div class="absolute top-0 right-0 -mt-4 -mr-4 h-24 w-24 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 opacity-20 blur-2xl transition-opacity group-hover:opacity-30"></div>
                    <div class="relative flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">This Month</p>
                            <p class="mt-2 text-4xl font-bold text-gray-900 dark:text-white">{{ $completedThisMonth }}</p>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-500">completed</p>
                        </div>
                        <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-blue-600 shadow-lg">
                            <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Appointments -->
            @php
                $todayAppointmentsList = auth()->user()->appointments()
                    ->with('calendar')
                    ->whereDate('start_datetime', $today)
                    ->where('status', 'scheduled')
                    ->orderBy('start_datetime')
                    ->get();
            @endphp

            <div class="overflow-hidden rounded-2xl bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm shadow-xl ring-1 ring-gray-900/5 dark:ring-gray-700/50">
                <div class="border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 px-6 py-5">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Today's Schedule</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $today->format('l, F j, Y') }}</p>
                </div>

                <div class="p-6">
                    @if($todayAppointmentsList->isEmpty())
                        <div class="flex flex-col items-center justify-center py-12">
                            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                                <svg class="h-10 w-10 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <p class="mt-4 text-base font-medium text-gray-600 dark:text-gray-400">No appointments scheduled for today</p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-500">Enjoy your free day!</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($todayAppointmentsList as $appointment)
                                <div class="group relative overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/50 p-5 shadow-sm transition-all duration-200 hover:shadow-lg hover:border-indigo-300 dark:hover:border-indigo-600">
                                    <div class="absolute left-0 top-0 bottom-0 w-1.5 rounded-l-xl transition-all group-hover:w-2" style="background-color: {{ $appointment->calendar->color }};"></div>
                                    <div class="flex items-start justify-between pl-4">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-lg font-semibold text-gray-900 dark:text-white truncate">{{ $appointment->title }}</p>
                                            <div class="mt-2 flex flex-wrap items-center gap-4 text-sm">
                                                <span class="inline-flex items-center gap-1.5 text-gray-700 dark:text-gray-300">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ $appointment->start_datetime->format('g:i A') }} - {{ $appointment->end_datetime->format('g:i A') }}
                                                </span>
                                                <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700 px-3 py-1 text-xs font-medium text-gray-700 dark:text-gray-300">
                                                    {{ $appointment->calendar->name }}
                                                </span>
                                            </div>
                                            @if($appointment->location)
                                                <p class="mt-2 inline-flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    </svg>
                                                    {{ $appointment->location }}
                                                </p>
                                            @endif
                                        </div>
                                        <a href="{{ route('appointments.edit', $appointment) }}" class="ml-4 flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-400 transition-all hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-400">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="overflow-hidden rounded-2xl bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm shadow-xl ring-1 ring-gray-900/5 dark:ring-gray-700/50">
                <div class="border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 px-6 py-5">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Quick Actions</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Get started with common tasks</p>
                </div>
                <div class="p-6">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <a href="{{ route('appointments.create') }}" class="group relative overflow-hidden rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-950/50 dark:to-purple-950/50 p-6 text-center transition-all duration-300 hover:border-indigo-400 dark:hover:border-indigo-500 hover:shadow-lg hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/0 to-purple-500/0 transition-all duration-300 group-hover:from-indigo-500/10 group-hover:to-purple-500/10"></div>
                            <div class="relative flex flex-col items-center gap-3">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-500 shadow-lg">
                                    <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                </div>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">New Appointment</span>
                            </div>
                        </a>

                        <a href="{{ route('calendar.dashboard') }}" class="group relative overflow-hidden rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-gradient-to-br from-emerald-50 to-teal-50 dark:from-emerald-950/50 dark:to-teal-950/50 p-6 text-center transition-all duration-300 hover:border-emerald-400 dark:hover:border-emerald-500 hover:shadow-lg hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/0 to-teal-500/0 transition-all duration-300 group-hover:from-emerald-500/10 group-hover:to-teal-500/10"></div>
                            <div class="relative flex flex-col items-center gap-3">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 shadow-lg">
                                    <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">View Calendar</span>
                            </div>
                        </a>

                        <a href="{{ route('import-export') }}" class="group relative overflow-hidden rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-950/50 dark:to-orange-950/50 p-6 text-center transition-all duration-300 hover:border-amber-400 dark:hover:border-amber-500 hover:shadow-lg hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-br from-amber-500/0 to-orange-500/0 transition-all duration-300 group-hover:from-amber-500/10 group-hover:to-orange-500/10"></div>
                            <div class="relative flex flex-col items-center gap-3">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-amber-500 to-orange-500 shadow-lg">
                                    <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                                    </svg>
                                </div>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">Import/Export</span>
                            </div>
                        </a>

                        <a href="{{ route('calendars.create') }}" class="group relative overflow-hidden rounded-xl border-2 border-gray-200 dark:border-gray-700 bg-gradient-to-br from-pink-50 to-rose-50 dark:from-pink-950/50 dark:to-rose-950/50 p-6 text-center transition-all duration-300 hover:border-pink-400 dark:hover:border-pink-500 hover:shadow-lg hover:scale-105">
                            <div class="absolute inset-0 bg-gradient-to-br from-pink-500/0 to-rose-500/0 transition-all duration-300 group-hover:from-pink-500/10 group-hover:to-rose-500/10"></div>
                            <div class="relative flex flex-col items-center gap-3">
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-pink-500 to-rose-500 shadow-lg">
                                    <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                    </svg>
                                </div>
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">New Calendar</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Upcoming Appointments (Next 7 Days) -->
            @php
                $upcomingAppointmentsList = auth()->user()->appointments()
                    ->with('calendar')
                    ->where('start_datetime', '>', $today)
                    ->where('start_datetime', '<=', $today->copy()->addDays(7))
                    ->where('status', 'scheduled')
                    ->orderBy('start_datetime')
                    ->limit(5)
                    ->get();
            @endphp

            @if($upcomingAppointmentsList->isNotEmpty())
                <div class="overflow-hidden rounded-2xl bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm shadow-xl ring-1 ring-gray-900/5 dark:ring-gray-700/50">
                    <div class="border-b border-gray-200 dark:border-gray-700 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 px-6 py-5">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Coming Up</h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Next 7 days</p>
                            </div>
                            <a href="{{ route('calendar.dashboard') }}" class="inline-flex items-center gap-2 rounded-lg bg-white dark:bg-gray-700 px-4 py-2 text-sm font-medium text-indigo-600 dark:text-indigo-400 shadow-sm ring-1 ring-gray-300 dark:ring-gray-600 transition-all hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:shadow-md">
                                View all
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </a>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="space-y-4">
                            @foreach($upcomingAppointmentsList as $appointment)
                                <div class="group relative overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800/50 p-4 shadow-sm transition-all duration-200 hover:shadow-lg hover:border-indigo-300 dark:hover:border-indigo-600">
                                    <div class="absolute left-0 top-0 bottom-0 w-1.5 rounded-l-xl transition-all group-hover:w-2" style="background-color: {{ $appointment->calendar->color }};"></div>
                                    <div class="flex items-center justify-between pl-4">
                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold text-gray-900 dark:text-white truncate">{{ $appointment->title }}</p>
                                            <div class="mt-1.5 flex flex-wrap items-center gap-3 text-sm">
                                                <span class="inline-flex items-center gap-1.5 text-gray-700 dark:text-gray-300">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                    {{ $appointment->start_datetime->format('D, M j') }}
                                                </span>
                                                <span class="inline-flex items-center gap-1.5 text-gray-700 dark:text-gray-300">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    {{ $appointment->start_datetime->format('g:i A') }}
                                                </span>
                                                <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700 px-2.5 py-0.5 text-xs font-medium text-gray-700 dark:text-gray-300">
                                                    {{ $appointment->calendar->name }}
                                                </span>
                                            </div>
                                        </div>
                                        <a href="{{ route('appointments.edit', $appointment) }}" class="ml-4 flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-400 transition-all hover:bg-indigo-50 dark:hover:bg-indigo-900/30 hover:text-indigo-600 dark:hover:text-indigo-400">
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
