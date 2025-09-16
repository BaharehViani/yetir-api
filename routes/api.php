<?php

// -------------------------------------------------------------------------------------------
//
// Yetir Delivery API
// Copyright (c) 2025. Software engineering project . All rights reserved.
// Developed by Bahareh Viani <baharehviani@gamil.com>.
// 
// -------------------------------------------------------------------------------------------


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourierControllers\CourierController;
use App\Http\Controllers\CourierControllers\VehiclesController;
use App\Http\Controllers\CustomerControllers\InvoicesController;

Route::prefix('/v1')->group(function () {

    Route::prefix('/users')->group(function () {
        Route::post('/register', [\App\Http\Controllers\PublicControllers\UsersController::class, 'register']);
        Route::post('/authenticate', [\App\Http\Controllers\PublicControllers\UsersController::class, 'authenticate']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {

        Route::prefix('/x-user')->group(function () {
            Route::get('/', [App\Http\Controllers\XUserControllers\UserController::class, 'index']);
            Route::patch('/', [App\Http\Controllers\XUserControllers\UserController::class, 'update']);
        });

        Route::prefix('/customer')->middleware(['role.verification:customer'])->group(function () {
            Route::prefix('/order-requests')->group(function () {
                Route::get('/', [App\Http\Controllers\CustomerControllers\OrderRequestsController::class, 'index']);
                Route::get('/{id}', [App\Http\Controllers\CustomerControllers\OrderRequestsController::class, 'show']);
                Route::post('/', [App\Http\Controllers\CustomerControllers\OrderRequestsController::class, 'create']);
                Route::patch('/{id}', [App\Http\Controllers\CustomerControllers\OrderRequestsController::class, 'update']);
            });

            Route::prefix('/orders')->group(function () {
                Route::get('/', [App\Http\Controllers\CustomerControllers\OrdersController::class, 'index']);
                Route::get('/types', [App\Http\Controllers\CustomerControllers\OrdersController::class, 'ordersType']);
                Route::get('/stats', [App\Http\Controllers\CustomerControllers\OrdersController::class, 'ordersOrderRequestsStats']);
                Route::get('/spending-stats', [App\Http\Controllers\CustomerControllers\OrdersController::class, 'monthlySpendingStats']);
                Route::get('/{id}', [App\Http\Controllers\CustomerControllers\OrdersController::class, 'show']);
            });

            Route::prefix('/invoices')->group(function () {
                Route::get('/all', [InvoicesController::class, 'indexAll']);
                Route::get('/pending', [InvoicesController::class, 'indexPending']);
                Route::patch('/{id}', [InvoicesController::class, 'update']);
            });

        });

        Route::prefix('/courier')->middleware(['role.verification:courier'])->group(function () {

            Route::get('/', [CourierController::class, 'index']);
            
            Route::prefix('/info')->group(function () {
                Route::patch('/', [CourierController::class, 'create']);
            });

            Route::prefix('/vehicles')->group(function () {
                Route::post('/', [VehiclesController::class, 'create']);
            });

            Route::prefix('/orders')->group(function () {
                Route::get('/', [App\Http\Controllers\CourierControllers\OrdersController::class, 'index']);
                Route::get('/active', [App\Http\Controllers\CourierControllers\OrdersController::class, 'activeOrder']);
                Route::get('/income', [App\Http\Controllers\CourierControllers\OrdersController::class, 'monthlyIncome']);
                Route::get('/types', [App\Http\Controllers\CourierControllers\OrdersController::class, 'ordersType']);
                Route::get('/count', [App\Http\Controllers\CourierControllers\OrdersController::class, 'dailyOrdersCount']);
                Route::get('/heat', [App\Http\Controllers\CourierControllers\OrdersController::class, 'weekHoursActivity']);
                Route::post('/', [App\Http\Controllers\CourierControllers\OrdersController::class, 'create']);
                Route::patch('/{id}', [App\Http\Controllers\CourierControllers\OrdersController::class, 'update']);
            });

            Route::prefix('/order-requests')->group(function () {
                Route::get('/', [App\Http\Controllers\CourierControllers\OrderRequestsController::class, 'index']);
            });

        });

    });

});
