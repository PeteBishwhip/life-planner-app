<div class="overflow-hidden">
    <!-- Day Headers - Responsive text sizes -->
    <div class="mb-1 grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700 sm:mb-2">
        @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
            <div class="bg-white px-1 py-2 text-center text-xs font-semibold text-gray-700 dark:bg-gray-800 dark:text-gray-300 sm:px-2 sm:py-3 sm:text-sm">
                <span class="hidden sm:inline">{{ $day }}</span>
                <span class="sm:hidden">{{ substr($day, 0, 1) }}</span>
            </div>
        @endforeach
    </div>

    <!-- Calendar Grid - Smaller cells on mobile -->
    <div class="grid grid-cols-7 gap-px bg-gray-200 dark:bg-gray-700">
        @foreach ($calendar as $week)
            @foreach ($week as $day)
                <div
                    class="min-h-[60px] cursor-pointer bg-white p-1 transition-colors hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700 sm:min-h-[80px] sm:p-1.5 md:min-h-[100px] md:p-2 {{ !$day['isCurrentMonth'] ? 'bg-gray-50 dark:bg-gray-900' : '' }} {{ $day['isToday'] ? 'ring-2 ring-indigo-500 ring-inset' : '' }}"
                    wire:click="dateClicked('{{ $day['date']->toDateString() }}')"
                >
                    <div class="mb-0.5 text-xs font-semibold sm:mb-1 sm:text-sm {{ $day['isCurrentMonth'] ? 'text-gray-900 dark:text-gray-100' : 'text-gray-400 dark:text-gray-600' }}">
                        {{ $day['date']->format('j') }}
                    </div>
                    <div class="space-y-0.5 sm:space-y-1">
                        @foreach ($day['appointments']->take(2) as $appointment)
                            <div
                                class="cursor-pointer truncate rounded px-0.5 py-0.5 text-[10px] text-white sm:px-1 sm:text-xs"
                                style="background-color: {{ $appointment->color }}"
                                wire:click.stop="appointmentClicked({{ $appointment->id }})"
                                title="{{ $appointment->title }}"
                            >
                                {{ $appointment->title }}
                            </div>
                        @endforeach
                        @if ($day['appointments']->count() > 2)
                            <div class="text-[10px] text-gray-500 dark:text-gray-400 sm:text-xs">
                                +{{ $day['appointments']->count() - 2 }} more
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        @endforeach
    </div>
</div>
