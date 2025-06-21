<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScheduleController;

Route::get('/', function () {
    return view('welcome');
});

// Route::get('schedules/{schedule}/delay', [ScheduleController::class, 'addDelay'])->name('schedules.delay');
Route::post('schedules/{schedule}/delay', [ScheduleController::class, 'addDelay'])->name('schedules.delay');

Route::get('add', [ScheduleController::class, 'delaySchedule'])->name('schedules.add');
Route::get('min', [ScheduleController::class, 'advanceSchedule'])->name('schedules.min');

Route::get('gantt', [ScheduleController::class, 'gantt'])->name('schedules.gantt');

Route::get('schedules/{productId}/product', [ScheduleController::class, 'showByProduct'])->name('schedules.show.product');

Route::resource('schedules', ScheduleController::class);
