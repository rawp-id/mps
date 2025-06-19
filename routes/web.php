<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScheduleController;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('schedules/{schedule}/delay', [ScheduleController::class, 'addDelay'])->name('schedules.delay');
Route::post('schedules/{schedule}/delay', [ScheduleController::class, 'addDelay'])->name('schedules.delay');

Route::get('test', [ScheduleController::class, 'delaySchedule'])->name('schedules.test');

Route::resource('schedules', ScheduleController::class);
