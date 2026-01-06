<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\CommitteeController;
use App\Http\Controllers\Web\PositionController;
use App\Http\Controllers\Web\DutyScheduleController;
use App\Http\Controllers\Web\JobResponsibilityController;
use App\Http\Controllers\Web\TaskAssignmentController;

// ==============================
// AUTHENTICATION ROUTES
// ==============================
Auth::routes(['verify' => true]);

// ==============================
// PUBLIC ROUTES
// ==============================
// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

// Welcome page (optional)
Route::get('/welcome', function () {
    return view('welcome');
})->name('welcome');

// ==============================
// PROTECTED ROUTES (Require Authentication)
// ==============================
Route::middleware(['auth'])->group(function () {

    // ==============================
    // DASHBOARD
    // ==============================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ==============================
    // COMMITTEES (PENGURUS) - Resource Routes
    // ==============================
    Route::resource('committees', CommitteeController::class);

    // Additional Committee Routes
    Route::get('committees/{committee}/duties', [CommitteeController::class, 'duties'])
        ->name('committees.duties');
    Route::get('committees/{committee}/tasks', [CommitteeController::class, 'tasks'])
        ->name('committees.tasks');

    // ==============================
    // POSITIONS (JABATAN) - Resource Routes
    // ==============================
    Route::resource('positions', PositionController::class);

    // Additional Position Routes
    Route::get('positions/{position}/committees', [PositionController::class, 'committees'])
        ->name('positions.committees');
    Route::get('positions/{position}/responsibilities', [PositionController::class, 'responsibilities'])
        ->name('positions.responsibilities');
    Route::get('positions/tree/view', [PositionController::class, 'tree'])
        ->name('positions.tree');
    Route::patch('positions/order/update', [PositionController::class, 'updateOrder'])
        ->name('positions.update-order');

    // ==============================
    // DUTY SCHEDULES (JADWAL PIKET) - Resource Routes
    // ==============================
    Route::resource('duty-schedules', DutyScheduleController::class);

    // Additional Duty Schedule Routes
    Route::get('duty-schedules/today/list', [DutyScheduleController::class, 'today'])
        ->name('duty-schedules.today');
    Route::get('duty-schedules/weekly/list', [DutyScheduleController::class, 'weekly'])
        ->name('duty-schedules.weekly');
    Route::get('duty-schedules/upcoming/list', [DutyScheduleController::class, 'upcoming'])
        ->name('duty-schedules.upcoming');
    Route::patch('duty-schedules/{duty_schedule}/status/update', [DutyScheduleController::class, 'updateStatus'])
        ->name('duty-schedules.update-status');

    // ==============================
    // JOB RESPONSIBILITIES (TUGAS) - Resource Routes
    // ==============================
    Route::resource('job-responsibilities', JobResponsibilityController::class);

    // Additional Job Responsibility Routes
    Route::get('job-responsibilities/by-position/{position}', [JobResponsibilityController::class, 'byPosition'])
        ->name('job-responsibilities.by-position');
    Route::get('job-responsibilities/statistics/show', [JobResponsibilityController::class, 'statistics'])
        ->name('job-responsibilities.statistics');

    // ==============================
    // TASK ASSIGNMENTS (PENUGASAN) - Resource Routes
    // ==============================
    Route::resource('task-assignments', TaskAssignmentController::class);

    // Additional Task Assignment Routes
    Route::get('task-assignments/overdue/list', [TaskAssignmentController::class, 'overdue'])
        ->name('task-assignments.overdue');
    Route::get('task-assignments/statistics/show', [TaskAssignmentController::class, 'statistics'])
        ->name('task-assignments.statistics');
    Route::patch('task-assignments/{task_assignment}/progress/update', [TaskAssignmentController::class, 'updateProgress'])
        ->name('task-assignments.update-progress');
    Route::post('task-assignments/{task_assignment}/approve', [TaskAssignmentController::class, 'approve'])
        ->name('task-assignments.approve');
});
