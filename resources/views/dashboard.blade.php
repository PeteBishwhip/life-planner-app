<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- Welcome Card -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-2xl font-bold">Welcome back, {{ auth()->user()->name }}!</h3>
                    <p class="mt-2 text-gray-600 dark:text-gray-400">Here's what's happening with your calendar today.</p>
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
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Today</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $todayAppointments }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Appointments -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Upcoming</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $upcomingAppointments }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Calendars -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Calendars</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $calendarsCount }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Completed This Month -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm text-gray-600 dark:text-gray-400">This Month</p>
                                <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $completedThisMonth }}</p>
                            </div>
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

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Today's Schedule</h3>

                    @if($todayAppointmentsList->isEmpty())
                        <p class="text-gray-600 dark:text-gray-400">No appointments scheduled for today.</p>
                    @else
                        <div class="space-y-3">
                            @foreach($todayAppointmentsList as $appointment)
                                <div class="flex items-center border-l-4 pl-4 py-2" style="border-color: {{ $appointment->calendar->color }};">
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $appointment->title }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $appointment->start_datetime->format('g:i A') }} - {{ $appointment->end_datetime->format('g:i A') }}
                                            <span class="ml-2 text-xs">{{ $appointment->calendar->name }}</span>
                                        </p>
                                        @if($appointment->location)
                                            <p class="text-sm text-gray-500 dark:text-gray-500">📍 {{ $appointment->location }}</p>
                                        @endif
                                    </div>
                                    <a href="{{ route('appointments.edit', $appointment) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Quick Actions</h3>
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <a href="{{ route('appointments.create') }}" class="flex items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-4 text-center hover:border-indigo-500 dark:hover:border-indigo-400 transition">
                            <svg class="h-6 w-6 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">New Appointment</span>
                        </a>

                        <a href="{{ route('calendar.dashboard') }}" class="flex items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-4 text-center hover:border-indigo-500 dark:hover:border-indigo-400 transition">
                            <svg class="h-6 w-6 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">View Calendar</span>
                        </a>

                        <a href="{{ route('import-export') }}" class="flex items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-4 text-center hover:border-indigo-500 dark:hover:border-indigo-400 transition">
                            <svg class="h-6 w-6 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                            </svg>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Import/Export</span>
                        </a>

                        <a href="{{ route('calendars.create') }}" class="flex items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 p-4 text-center hover:border-indigo-500 dark:hover:border-indigo-400 transition">
                            <svg class="h-6 w-6 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">New Calendar</span>
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
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Coming Up</h3>
                            <a href="{{ route('calendar.dashboard') }}" class="text-sm text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">View all</a>
                        </div>

                        <div class="space-y-3">
                            @foreach($upcomingAppointmentsList as $appointment)
                                <div class="flex items-center border-l-4 pl-4 py-2" style="border-color: {{ $appointment->calendar->color }};">
                                    <div class="flex-1">
                                        <p class="font-semibold text-gray-900 dark:text-gray-100">{{ $appointment->title }}</p>
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $appointment->start_datetime->format('D, M j \a\t g:i A') }}
                                            <span class="ml-2 text-xs">{{ $appointment->calendar->name }}</span>
                                        </p>
                                    </div>
                                    <a href="{{ route('appointments.edit', $appointment) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
