<?php

use App\Http\Controllers\API\CompanyController;
use App\Http\Controllers\API\EmployeeController;
use App\Http\Controllers\API\ManagerController;
use Illuminate\Http\Request;
use App\Http\Controllers\API\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\JwtMiddleware;

    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware([JwtMiddleware::class])->group(function () {
        Route::get('user', [AuthController::class, 'getUser']);
        Route::post('logout', [AuthController::class, 'logout']);

        // Company
        Route::get('company', [CompanyController::class, 'index']);
        Route::post('add-company', [CompanyController::class, 'store']);
        Route::delete('company/{id}', [CompanyController::class, 'destroy']);

        //Manager
        Route::get('manager', [ManagerController::class, 'index']);
        Route::get('manager/{id}', [ManagerController::class, 'detail']);
        Route::put('manager/{id}/update', [ManagerController::class, 'update']);
        Route::delete('manager/{id}', [ManagerController::class, 'destroy']);

        // Employee
        Route::get('employee', [EmployeeController::class, 'index']);
        Route::post('add-employee', [EmployeeController::class, 'store']);
        Route::get('employee/{id}', [EmployeeController::class, 'detail']);
        Route::put('employee/{id}/update', [EmployeeController::class, 'update']);
        Route::delete('employee/{id}', [EmployeeController::class, 'destroy']);
    });

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
