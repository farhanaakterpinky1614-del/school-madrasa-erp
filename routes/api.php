<?php

/**
 * Phase 16 — Sanctum API (tenant-scoped).
 *
 * Auth: Bearer personal access token (Sanctum).
 * Tenant: bound from authenticated user's tenant_id (shared-DB row scope).
 * Plan: requires `api_access` feature (Professional+).
 *
 * Routes (prefix /api/v1):
 *   GET    /students                 List students
 *   POST   /students                 Create student (quota-enforced)
 *   GET    /students/{student}       Show student
 *   PATCH  /students/{student}       Update student
 *   GET    /attendance               List daily attendance
 *   GET    /attendance/summary       Counts by status for a date
 *   POST   /attendance               Mark daily attendance (fires webhook)
 *   GET    /fees/summary             Invoice status rollup + payments today
 *   GET    /fees/invoices            List fee invoices
 *   POST   /fees/invoices/{invoice}/payments  Record payment (fires webhook)
 *   GET    /tokens                   List API tokens (for current user)
 *   POST   /tokens                   Create token
 *   DELETE /tokens/{tokenId}         Revoke token
 */

use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\FeeController;
use App\Http\Controllers\Api\V1\StudentController;
use App\Http\Controllers\Api\V1\TokenController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware(['auth:sanctum', 'api.tenant', 'throttle:api'])->group(function () {
    Route::get('/students', [StudentController::class, 'index']);
    Route::post('/students', [StudentController::class, 'store']);
    Route::get('/students/{student}', [StudentController::class, 'show']);
    Route::patch('/students/{student}', [StudentController::class, 'update']);

    Route::get('/attendance', [AttendanceController::class, 'index']);
    Route::get('/attendance/summary', [AttendanceController::class, 'summary']);
    Route::post('/attendance', [AttendanceController::class, 'store']);

    Route::get('/fees/summary', [FeeController::class, 'summary']);
    Route::get('/fees/invoices', [FeeController::class, 'invoices']);
    Route::post('/fees/invoices/{invoice}/payments', [FeeController::class, 'recordPayment']);

    Route::get('/tokens', [TokenController::class, 'index']);
    Route::post('/tokens', [TokenController::class, 'store']);
    Route::delete('/tokens/{tokenId}', [TokenController::class, 'destroy']);
});
