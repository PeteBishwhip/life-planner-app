<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Calendar Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-6 sm:py-12">
        <div class="mx-auto max-w-7xl px-3 sm:px-6 lg:px-8">
            <livewire:calendar-dashboard />
        </div>
    </div>

    <!-- Appointment Manager Modal -->
    <livewire:appointment-manager />

    <!-- Floating Action Button (FAB) - Mobile only -->
    <button
        onclick="Livewire.dispatch('open-appointment-modal')"
        class="fixed bottom-6 right-6 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-indigo-600 text-white shadow-lg transition-all hover:bg-indigo-700 hover:shadow-xl active:scale-95 sm:h-16 sm:w-16 lg:hidden"
        aria-label="Create new appointment"
    >
        <svg class="h-6 w-6 sm:h-7 sm:w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
        </svg>
    </button>
</x-app-layout>
