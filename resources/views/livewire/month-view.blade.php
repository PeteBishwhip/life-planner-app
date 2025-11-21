<div class="overflow-hidden">
    <!-- Day Headers -->
    <div class="mb-2 grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700">
        @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
            <div class="bg-white px-2 py-3 text-center text-sm font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                {{ $day }}
            </div>
        @endforeach
    </div>

    <!-- Calendar Grid -->
    <div class="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700">
        @foreach ($calendar as $week)
            @foreach ($week as $day)
                <div
                    class="min-h-[120px] bg-white p-2 dark:bg-gray-800 {{ !$day['isCurrentMonth'] ? 'bg-gray-50 dark:bg-gray-900' : '' }} {{ $day['isToday'] ? 'ring-2 ring-indigo-500' : '' }}"
                    wire:click="dateClicked('{{ $day['date']->toDateString() }}')"
                >
                    <div class="mb-1 text-sm font-semibold {{ $day['isCurrentMonth'] ? 'text-gray-900 dark:text-gray-100' : 'text-gray-400 dark:text-gray-600' }}">
                        {{ $day['date']->format('j') }}
                    </div>
                    <div class="space-y-1">
                        @foreach ($day['appointments']->take(3) as $appointment)
                            <div
                                class="cursor-pointer truncate rounded px-1 py-0.5 text-xs text-white"
                                style="background-color: {{ $appointment->color }}"
                                wire:click.stop="appointmentClicked({{ $appointment->id }})"
                            >
                                {{ $appointment->title }}
                            </div>
                        @endforeach
                        @if ($day['appointments']->count() > 3)
                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                +{{ $day['appointments']->count() - 3 }} more
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        @endforeach
    </div>
</div>
