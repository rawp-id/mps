<?php

use App\Http\Controllers\PlanGeneratorController;
use App\Http\Controllers\PlanProductCoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\PlanSimulateController;

Route::get('/', function () {
    return view('welcome');
});


// Route::get('schedules/{schedule}/delay', [ScheduleController::class, 'addDelay'])->name('schedules.delay');
Route::post('schedules/{schedule}/delay', [ScheduleController::class, 'addDelay'])->name('schedules.delay');

Route::post('schedule/{id}/add', [ScheduleController::class, 'delaySchedule'])->name('schedules.add');
Route::post('schedule/{id}/min', [ScheduleController::class, 'advanceSchedule'])->name('schedules.min');

Route::get('gantt', [ScheduleController::class, 'gantt'])->name('schedules.gantt');
Route::get('gantt/machines/{id}', [ScheduleController::class, 'ganttByMachine'])->name('schedules.gantt.machine');
Route::get('gantt/processes/{id}', [ScheduleController::class, 'ganttByProcess'])->name('schedules.gantt.process');

Route::post('schedules/{id}/updateDependencySafety', [ScheduleController::class, 'updateDependencySafe'])->name('schedules.updateDependencySafety');

Route::get('schedules/{coProductId}/coProduct', [ScheduleController::class, 'showByProduct'])->name('schedules.show.coProduct');

Route::resource('schedules/calender', ScheduleController::class);

Route::get('/plan-simulate', [PlanSimulateController::class, 'index'])->name('plan-simulate.index');
Route::get('/plan-simulate/create', [PlanSimulateController::class, 'create'])->name('plan-simulate.create');
Route::post('/plan-simulate', [PlanSimulateController::class, 'store'])->name('plan-simulate.store');
Route::get('/plan-simulate/{plan}', [PlanSimulateController::class, 'show'])->name('plan-simulate.show');
Route::delete('/plan-simulate/{plan}', [PlanSimulateController::class, 'destroy'])->name('plan-simulate.destroy');
Route::get('/plan-simulate/{plan}/edit', [PlanSimulateController::class, 'edit'])->name('plan-simulate.edit');
Route::post('/plan-simulate/{plan}/add/co', [PlanSimulateController::class, 'addCoToPlan'])->name('plan-simulate.addCoToPlan');
Route::delete('/plan-simulate/co/{id}', [PlanSimulateController::class, 'destroyCoFromPlan'])->name('plan-simulate.destroyCoFromPlan');
// Route::post('/plan-simulate/generate', [PlanGeneratorController::class, 'generate'])->name('plan-simulate.generate');
Route::post('/plan-simulate/{plan}/generate', [PlanSimulateController::class, 'generatePlan'])->name('plan-simulate.generate');

// Import routes
Route::get('products/import', [ProductController::class, 'importForm'])->name('products.import');
Route::post('products/import-preview', [ProductController::class, 'importPreview'])->name('products.import.preview');
Route::post('products/import-store', [ProductController::class, 'importStore'])->name('products.import.store');
Route::get('/products/process/{id}', [ProductController::class, 'process'])->name('products.process');
Route::post('/products/process/{id}', [ProductController::class, 'inputProcessProduct'])->name('products.process.input');
Route::post('/products/process-component/{id}', [ProductController::class, 'inputProcessComponentProduct'])->name('products.processComponent.input');
Route::delete('/products/process/{process_id}', [ProductController::class, 'deleteProcess'])->name('products.process.delete');

route::get('reset', function () {
    \App\Models\Schedule::truncate();
    \App\Models\SimulateSchedule::truncate();
    \App\Models\Plan::truncate();
    \App\Models\Product::truncate();
    // \App\Models\Process::truncate();
    // \App\Models\Machine::truncate();

    return redirect()->back()->with('success', 'Data has been reset successfully.');
})->name('reset');

Route::get('generate/plan' , [ProductController::class, 'generatePlans'])->name('plan.generate');

Route::patch('reports/{id}/update-process-status', [\App\Http\Controllers\ReportController::class, 'updateProcessStatus'])->name('reports.updateStatus');
Route::patch('reports/{id}/update-is-completed', [\App\Http\Controllers\ReportController::class, 'updateIsCompleted'])->name('reports.updateIsCompleted');

Route::get('apply-schedule/{plan}', [PlanSimulateController::class, 'applyToSchedule'])->name('apply.schedule');

Route::patch('plan-product-co/{planProductCo}/update-lock-status/{is_locked}', [PlanProductCoController::class, 'updateLockStatus'])->name('plan-product-co.updateLockStatus');

Route::resource('products', ProductController::class);
Route::resource('machines', \App\Http\Controllers\MachineController::class);
Route::resource('processes', \App\Http\Controllers\ProcessController::class);
Route::resource('operations', \App\Http\Controllers\OperationsController::class);
Route::resource('reports', \App\Http\Controllers\ReportController::class);
Route::resource('co', \App\Http\Controllers\CoController::class);
Route::resource('groups', \App\Http\Controllers\GroupController::class);
Route::resource('shifts', \App\Http\Controllers\ShiftController::class);
Route::resource('overtimes', \App\Http\Controllers\OvertimeController::class);
Route::resource('downtimes', \App\Http\Controllers\DowntimeController::class);
Route::resource('calender-days', \App\Http\Controllers\CalenderDayController::class);
Route::resource('components', \App\Http\Controllers\ComponentController::class);
Route::resource('boms', \App\Http\Controllers\BOMController::class);

