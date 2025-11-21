<div class="overflow-x-auto">
    <div class="grid min-w-[640px] grid-cols-8 gap-px bg-gray-200 dark:bg-gray-700">
        <!-- Time Column Header -->
        <div class="sticky left-0 z-10 bg-white dark:bg-gray-800"></div>

        <!-- Day Headers - More compact on mobile -->
        @foreach ($weekDays as $day)
            <div class="bg-white px-2 py-2 text-center dark:bg-gray-800 sm:px-4 sm:py-3">
                <div class="text-xs font-semibold text-gray-700 dark:text-gray-300 sm:text-sm">{{ $day['date']->format('D') }}</div>
                <div class="text-base font-bold sm:text-lg {{ $day['isToday'] ? 'text-indigo-600' : 'text-gray-900 dark:text-gray-100' }}">
                    {{ $day['date']->format('j') }}
                </div>
            </div>
        @endforeach

        <!-- Time Slots -->
        @for ($hour = 0; $hour < 24; $hour++)
            <!-- Time Label - Sticky on mobile for better navigation -->
            <div class="sticky left-0 z-10 bg-white px-1 py-2 text-right text-[10px] text-gray-500 dark:bg-gray-800 dark:text-gray-400 sm:px-2 sm:text-xs">
                {{ now()->setTime($hour, 0)->format('g A') }}
            </div>

            <!-- Day Columns - Touch-optimized -->
            @foreach ($weekDays as $dayIndex => $day)
                <div
                    class="min-h-[50px] cursor-pointer bg-white p-0.5 transition-colors hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700 sm:min-h-[60px] sm:p-1"
                    wire:click="timeSlotClicked('{{ $day['date']->toDateString() }}', {{ $hour }})"
                >
                    @foreach ($day['hourlySlots'][$hour]['appointments'] as $appointment)
                        <div
                            class="mb-0.5 cursor-pointer truncate rounded px-0.5 py-0.5 text-[10px] text-white sm:mb-1 sm:px-1 sm:text-xs"
                            style="background-color: {{ $appointment->color }}"
                            wire:click.stop="appointmentClicked({{ $appointment->id }})"
                            title="{{ $appointment->title }}"
                        >
                            {{ $appointment->title }}
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endfor
    </div>
</div>
