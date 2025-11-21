<div class="overflow-x-auto">
    <div class="grid min-w-full grid-cols-8 gap-px bg-gray-200 dark:bg-gray-700">
        <!-- Time Column Header -->
        <div class="bg-white dark:bg-gray-800"></div>

        <!-- Day Headers -->
        @foreach ($weekDays as $day)
            <div class="bg-white px-4 py-3 text-center dark:bg-gray-800">
                <div class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $day['date']->format('D') }}</div>
                <div class="text-lg font-bold {{ $day['isToday'] ? 'text-indigo-600' : 'text-gray-900 dark:text-gray-100' }}">
                    {{ $day['date']->format('j') }}
                </div>
            </div>
        @endforeach

        <!-- Time Slots -->
        @for ($hour = 0; $hour < 24; $hour++)
            <!-- Time Label -->
            <div class="bg-white px-2 py-2 text-right text-xs text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                {{ now()->setTime($hour, 0)->format('g A') }}
            </div>

            <!-- Day Columns -->
            @foreach ($weekDays as $dayIndex => $day)
                <div
                    class="min-h-[60px] bg-white p-1 dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                    wire:click="timeSlotClicked('{{ $day['date']->toDateString() }}', {{ $hour }})"
                >
                    @foreach ($day['hourlySlots'][$hour]['appointments'] as $appointment)
                        <div
                            class="mb-1 cursor-pointer truncate rounded px-1 py-0.5 text-xs text-white"
                            style="background-color: {{ $appointment->color }}"
                            wire:click.stop="appointmentClicked({{ $appointment->id }})"
                        >
                            {{ $appointment->title }}
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endfor
    </div>
</div>
