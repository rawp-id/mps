<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScheduleController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('schedules/{schedule}/delay', [ScheduleController::class, 'updateScheduleWithDelay'])->name('schedules.delay');

Route::resource('schedules', ScheduleController::class);
