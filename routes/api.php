<?php

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
                Route::get('/{id}', [App\Http\Controllers\CustomerControllers\OrdersController::class, 'show']);
            });

            Route::prefix('/invoices')->group(function () {
                Route::get('/', [InvoicesController::class, 'index']);
            });

        });

        Route::prefix('/courier')->middleware(['role.verification:courier'])->group(function () {
            
            Route::prefix('/info')->group(function () {
                Route::patch('/', [CourierController::class, 'create']);
            });

            Route::prefix('/vehicles')->group(function () {
                Route::post('/', [VehiclesController::class, 'create']);
            });

            Route::prefix('/orders')->group(function () {
                Route::get('/', [App\Http\Controllers\CourierControllers\OrdersController::class, 'index']);
                Route::get('/{id}', [App\Http\Controllers\CourierControllers\OrdersController::class, 'show']);
                Route::get('/latest', [App\Http\Controllers\CourierControllers\OrdersController::class, 'current']);
                Route::post('/', [App\Http\Controllers\CourierControllers\OrdersController::class, 'create']);
                Route::patch('/{id}', [App\Http\Controllers\CourierControllers\OrdersController::class, 'update']);
            });

            Route::prefix('/order-requests')->group(function () {
                Route::get('/', [App\Http\Controllers\CourierControllers\OrderRequestsController::class, 'index']);
            });

        });

    });

});
