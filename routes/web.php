<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\CalendarController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Calendar Dashboard Route
Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/calendar', 'calendar.dashboard')->name('calendar.dashboard');
    Route::view('/import-export', 'import-export')->name('import-export');
});

// Calendar Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('calendars', CalendarController::class);

    Route::post('/calendars/{calendar}/toggle-visibility', [CalendarController::class, 'toggleVisibility'])
        ->name('calendars.toggle-visibility');

    Route::post('/calendars/{calendar}/set-default', [CalendarController::class, 'setDefault'])
        ->name('calendars.set-default');
});

// Appointment Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('appointments', AppointmentController::class);

    Route::post('/appointments/{appointment}/complete', [AppointmentController::class, 'complete'])
        ->name('appointments.complete');

    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])
        ->name('appointments.cancel');
});

require __DIR__.'/auth.php';
