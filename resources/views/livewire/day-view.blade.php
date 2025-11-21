<div class="space-y-3 sm:space-y-4">
    <!-- All-Day Events - Touch-optimized -->
    @if ($allDayAppointments->isNotEmpty())
        <div class="rounded-lg bg-gray-50 p-3 dark:bg-gray-900 sm:p-4">
            <h3 class="mb-2 text-xs font-semibold text-gray-700 dark:text-gray-300 sm:text-sm">All Day</h3>
            <div class="space-y-1.5 sm:space-y-2">
                @foreach ($allDayAppointments as $appointment)
                    <div
                        class="min-h-[44px] cursor-pointer rounded px-2 py-2 text-sm text-white sm:px-3 sm:text-base"
                        style="background-color: {{ $appointment->color }}"
                        wire:click="appointmentClicked({{ $appointment->id }})"
                    >
                        {{ $appointment->title }}
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Hourly Slots - Mobile-optimized -->
    <div class="space-y-px overflow-hidden rounded-lg bg-gray-200 dark:bg-gray-700">
        @foreach ($hourlySlots as $slot)
            <div class="flex bg-white dark:bg-gray-800">
                <div class="w-12 flex-shrink-0 px-1 py-2 text-right text-[10px] text-gray-500 dark:text-gray-400 sm:w-20 sm:px-4 sm:py-3 sm:text-sm">
                    {{ $slot['time'] }}
                </div>
                <div
                    class="min-h-[60px] flex-1 cursor-pointer p-1.5 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700 sm:min-h-[80px] sm:p-2"
                    wire:click="timeSlotClicked({{ $slot['hour'] }})"
                >
                    @foreach ($slot['appointments'] as $appointment)
                        <div
                            class="mb-1.5 cursor-pointer rounded px-2 py-1.5 text-white sm:mb-2 sm:px-3 sm:py-2"
                            style="background-color: {{ $appointment->color }}"
                            wire:click.stop="appointmentClicked({{ $appointment->id }})"
                        >
                            <div class="text-sm font-semibold sm:text-base">{{ $appointment->title }}</div>
                            <div class="text-[10px] opacity-90 sm:text-xs">
                                {{ $appointment->start_datetime->format('g:i A') }} - {{ $appointment->end_datetime->format('g:i A') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
