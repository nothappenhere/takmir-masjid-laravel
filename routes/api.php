<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CommitteeController as ApiCommitteeController;
use App\Http\Controllers\Api\PositionController as ApiPositionController;
use App\Http\Controllers\Api\PositionHistoryController as ApiPositionHistoryController;
use App\Http\Controllers\Api\JobResponsibilityController as ApiJobResponsibilityController;
use App\Http\Controllers\Api\DutyScheduleController as ApiDutyScheduleController;
use App\Http\Controllers\Api\TaskAssignmentController as ApiTaskAssignmentController;
use App\Http\Controllers\Api\OrganizationalStructureController as ApiOrganizationalStructureController;
use Illuminate\Support\Facades\DB;

// API Routes dengan name prefix 'api.' untuk menghindari conflict
Route::name('api.')->group(function () {

    // API Root Check
    Route::get('/', function () {
        return response()->json([
            'message' => 'Takmir Masjid Management API',
            'version' => '1.0.0',
            'description' => 'API untuk manajemen takmir/pengurus masjid',
            'endpoints' => [
                'committees' => '/api/committees',
                'positions' => '/api/positions',
                'duty-schedules' => '/api/duty-schedules',
                'job-responsibilities' => '/api/job-responsibilities',
                'task-assignments' => '/api/task-assignments',
                'position-histories' => '/api/position-histories',
                'organizational-structure' => '/api/organizational-structure',
            ],
        ]);
    })->name('root');

    // ==============================
    // COMMITTEES (PENGURUS)
    // ==============================
    Route::apiResource('committees', ApiCommitteeController::class);
    Route::get('committees/{committee}/statistics', [ApiCommitteeController::class, 'statistics'])
        ->name('committees.statistics');

    // ==============================
    // POSITIONS (JABATAN)
    // ==============================
    Route::apiResource('positions', ApiPositionController::class);
    Route::get('positions/{position}/committees', [ApiPositionController::class, 'committees'])
        ->name('positions.committees');
    Route::get('positions/{position}/responsibilities', [ApiPositionController::class, 'responsibilities'])
        ->name('positions.responsibilities');
    Route::get('positions/hierarchy/tree', [ApiPositionController::class, 'hierarchy'])
        ->name('positions.hierarchy');

    // ==============================
    // POSITION HISTORIES (RIWAYAT JABATAN)
    // ==============================
    Route::apiResource('position-histories', ApiPositionHistoryController::class);

    // ==============================
    // JOB RESPONSIBILITIES (TUGAS & TANGGUNG JAWAB)
    // ==============================
    Route::apiResource('job-responsibilities', ApiJobResponsibilityController::class);

    // ==============================
    // DUTY SCHEDULES (JADWAL PIKET/TUGAS)
    // ==============================
    Route::apiResource('duty-schedules', ApiDutyScheduleController::class);
    Route::get('duty-schedules/today', [ApiDutyScheduleController::class, 'today'])
        ->name('duty-schedules.today');
    Route::get('duty-schedules/weekly', [ApiDutyScheduleController::class, 'weekly'])
        ->name('duty-schedules.weekly');
    Route::patch('duty-schedules/{duty_schedule}/status', [ApiDutyScheduleController::class, 'updateStatus'])
        ->name('duty-schedules.update-status');

    // ==============================
    // TASK ASSIGNMENTS (PENUGASAN)
    // ==============================
    Route::apiResource('task-assignments', ApiTaskAssignmentController::class);
    Route::patch('task-assignments/{task_assignment}/progress', [ApiTaskAssignmentController::class, 'updateProgress'])
        ->name('task-assignments.update-progress');
    Route::post('task-assignments/{task_assignment}/approve', [ApiTaskAssignmentController::class, 'approve'])
        ->name('task-assignments.approve');

    // ==============================
    // ORGANIZATIONAL STRUCTURE (STRUKTUR ORGANISASI)
    // ==============================
    Route::get('organizational-structure', [ApiOrganizationalStructureController::class, 'index'])
        ->name('organizational-structure.index');
    Route::patch('organizational-structure/order', [ApiOrganizationalStructureController::class, 'updateOrder'])
        ->name('organizational-structure.update-order');

    // ==============================
    // HEALTH CHECK
    // ==============================
    Route::get('/health', function () {
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'database' => DB::connection()->getPdo() ? 'connected' : 'disconnected',
        ]);
    })->name('health');
});
