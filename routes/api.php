<?php

use App\Http\Controllers\AuditController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VideoChatController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
Route::post('/login', [UserController::class, 'check_login']);

Route::group(['middleware' => 'auth:api'], function () {
    // Employee
    Route::get('user-details', [UserController::class, 'user_details']);
    Route::post('logout', [UserController::class, 'logout']);
    Route::get('all-team', [UserController::class, 'all_team']);
    Route::post('employee-create', [UserController::class, 'employee_create']);
    Route::get('all-employee', [UserController::class, 'all_employee']);
    Route::post('employee-delete', [UserController::class, 'employee_delete']);
    Route::post('employee-update', [UserController::class, 'employee_update']);
    Route::post('change-employee-availability', [UserController::class, 'change_availability']);
    Route::post('change-employee-attendance', [UserController::class, 'change_attendance']);


    // Task
    Route::group(['prefix' => 'task'], function () {
        Route::get('/', [TaskController::class, 'all_task']);
        Route::post('/create', [TaskController::class, 'create']);
        Route::get('/all-task', [TaskController::class, 'index']);
        Route::get('/all-task-excel-export', [TaskController::class, 'all_task_excel_export']);
        Route::post('/update-task-status', [TaskController::class, 'update_task_status']);
    });

    // Audit
    Route::group(['prefix' => 'audit'], function () {
        Route::get('/task-logs', [AuditController::class, 'task_log']);
        Route::get('/attendance-logs', [AuditController::class, 'attendance_log']);
    });

    // Role Permission
    Route::group(['prefix' => 'role-permission'], function () {
        Route::get('/', [RolePermissionController::class, 'index']);
        Route::get('/create', [RolePermissionController::class, 'create']);
        Route::post('/store', [RolePermissionController::class, 'store']);
        Route::post('/delete', [RolePermissionController::class, 'delete']);
        Route::get('/edit/{id}', [RolePermissionController::class, 'edit']);
        Route::post('/update', [RolePermissionController::class, 'update']);
    });
    // Dashboard
    Route::group(['prefix' => 'dashboard'], function () {
        Route::get('/', [DashboardController::class, 'index']);
    });

    // Video chatting
    Route::post('/video/call-user', [VideoChatController::class, 'callUser']);
    Route::post('/video/accept-call', [VideoChatController::class, 'acceptCall']);
    Route::get('/video/details', [VideoChatController::class, 'details']);
})->middleware('auth:api');
