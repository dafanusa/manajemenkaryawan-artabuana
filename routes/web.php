<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Authentication
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Export Employee Data (Place before resource routes)
    Route::get('/employees/export', [EmployeeController::class, 'export'])->name('employees.export');

    // Departments Management
    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index');
    Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store');
    Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])
        ->middleware('role:super_admin')
        ->name('departments.destroy');

    // Employees CRUD
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
    Route::get('/employees/{employee}/print', [EmployeeController::class, 'print'])->name('employees.print');
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::patch('/employees/{employee}/status', [EmployeeController::class, 'updateStatus'])->name('employees.updateStatus');

    // Delete Employee (Super Admin only check inside controller)
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])
        ->middleware('role:super_admin')
        ->name('employees.destroy');

    // Document download with auth checks
    Route::get('/documents/{document}/download', [EmployeeController::class, 'downloadDocument'])->name('documents.download');

    // Super Admin Routes
    Route::middleware('role:super_admin')->group(function () {
        // Salary History dedicated management
        Route::post('/employees/{employee}/salary-histories', [EmployeeController::class, 'storeSalaryHistory'])->name('employees.salary_histories.store');
        Route::delete('/salary-histories/{history}', [EmployeeController::class, 'destroySalaryHistory'])->name('employees.salary_histories.destroy');

        // User Management
        Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
        Route::post('/users', [UserManagementController::class, 'store'])->name('users.store');
        Route::put('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
        Route::patch('/users/{user}/status', [UserManagementController::class, 'toggleStatus'])->name('users.toggleStatus');
        Route::post('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.resetPassword');

        // Activity Logs
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity_logs.index');

        // Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
});

