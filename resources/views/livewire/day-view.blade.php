<div class="space-y-4">
    <!-- All-Day Events -->
    @if ($allDayAppointments->isNotEmpty())
        <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900">
            <h3 class="mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">All Day</h3>
            <div class="space-y-2">
                @foreach ($allDayAppointments as $appointment)
                    <div
                        class="cursor-pointer rounded px-3 py-2 text-white"
                        style="background-color: {{ $appointment->color }}"
                        wire:click="appointmentClicked({{ $appointment->id }})"
                    >
                        {{ $appointment->title }}
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Hourly Slots -->
    <div class="space-y-px overflow-hidden rounded-lg bg-gray-200 dark:bg-gray-700">
        @foreach ($hourlySlots as $slot)
            <div class="flex bg-white dark:bg-gray-800">
                <div class="w-20 flex-shrink-0 px-4 py-3 text-right text-sm text-gray-500 dark:text-gray-400">
                    {{ $slot['time'] }}
                </div>
                <div
                    class="min-h-[80px] flex-1 p-2 hover:bg-gray-50 dark:hover:bg-gray-700"
                    wire:click="timeSlotClicked({{ $slot['hour'] }})"
                >
                    @foreach ($slot['appointments'] as $appointment)
                        <div
                            class="mb-2 cursor-pointer rounded px-3 py-2 text-white"
                            style="background-color: {{ $appointment->color }}"
                            wire:click.stop="appointmentClicked({{ $appointment->id }})"
                        >
                            <div class="font-semibold">{{ $appointment->title }}</div>
                            <div class="text-xs opacity-90">
                                {{ $appointment->start_datetime->format('g:i A') }} - {{ $appointment->end_datetime->format('g:i A') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
