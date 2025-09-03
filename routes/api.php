<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PlanGeneratorController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route::post('generate/plan' , [ProductController::class, 'generatePlans'])->name('plan.generate');
Route::post('python/generate', [PlanGeneratorController::class, 'generate'])->name('python.generate');
