<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    <nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}" wire:navigate>
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('calendar.dashboard')" :active="request()->routeIs('calendar.*')" wire:navigate>
                        {{ __('Calendar') }}
                    </x-nav-link>
                    <x-nav-link :href="route('search')" :active="request()->routeIs('search')" wire:navigate>
                        {{ __('Search') }}
                    </x-nav-link>
                    <x-nav-link :href="route('calendars.index')" :active="request()->routeIs('calendars.*')" wire:navigate>
                        {{ __('Calendars') }}
                    </x-nav-link>
                    <x-nav-link :href="route('appointments.index')" :active="request()->routeIs('appointments.*')" wire:navigate>
                        {{ __('Appointments') }}
                    </x-nav-link>
                    <x-nav-link :href="route('import-export')" :active="request()->routeIs('import-export')" wire:navigate>
                        {{ __('Import/Export') }}
                    </x-nav-link>
                    <button type="button" @click="Livewire.dispatch('open-shortcuts')" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                        {{ __('Help') }}
                    </button>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile')" wire:navigate>
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('settings')" wire:navigate>
                            {{ __('Settings') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <button wire:click="logout" class="w-full text-start">
                            <x-dropdown-link>
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </button>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('calendar.dashboard')" :active="request()->routeIs('calendar.*')" wire:navigate>
                {{ __('Calendar') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('search')" :active="request()->routeIs('search')" wire:navigate>
                {{ __('Search') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('calendars.index')" :active="request()->routeIs('calendars.*')" wire:navigate>
                {{ __('Calendars') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('appointments.index')" :active="request()->routeIs('appointments.*')" wire:navigate>
                {{ __('Appointments') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('import-export')" :active="request()->routeIs('import-export')" wire:navigate>
                {{ __('Import/Export') }}
            </x-responsive-nav-link>
            <button type="button" @click="Livewire.dispatch('open-shortcuts')" class="w-full text-left block px-4 py-2 text-sm text-gray-600 hover:text-gray-900">
                {{ __('Help') }}
            </button>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="font-medium text-sm text-gray-500">{{ auth()->user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <x-responsive-nav-link :href="route('settings')" wire:navigate>
                    {{ __('Settings') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <button wire:click="logout" class="w-full text-start">
                    <x-responsive-nav-link>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </button>
            </div>
        </div>
    </div>
    </nav>
<!-- Keyboard Shortcuts Modal (handled via Livewire event to keep consistent with other modals) -->
    <div x-data="{ open: false }"
     x-on:open-shortcuts.window="open = true"
     x-show="open"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-gray-900/50" @click="open = false"></div>
    <div class="relative w-full max-w-lg mx-4 rounded-lg bg-white shadow-lg">
        <div class="flex items-center justify-between border-b px-5 py-4">
            <h3 class="text-lg font-semibold text-gray-900">Keyboard Shortcuts</h3>
            <button class="text-gray-400 hover:text-gray-600" @click="open = false" aria-label="Close">
                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="px-5 py-4">
            <ul class="space-y-2 text-sm text-gray-700">
                <li><span class="font-medium">J / K</span> — Next / Previous day or week</li>
                <li><span class="font-medium">T</span> — Jump to Today</li>
                <li><span class="font-medium">M / W / D / L</span> — Switch to Month / Week / Day / List view</li>
                <li><span class="font-medium">/</span> — Focus search</li>
                <li><span class="font-medium">C</span> — Quick add appointment</li>
            </ul>
        </div>
        <div class="flex justify-end border-t px-5 py-3">
            <button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500" @click="open = false">Close</button>
        </div>
    </div>

    </div>
</div>
