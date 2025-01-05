<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourierControllers\CourierController;
use App\Http\Controllers\CourierControllers\VehicleController;
use App\Http\Controllers\CustomerControllers\OrderRequestsController;

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
                Route::get('/', [OrderRequestsController::class, 'index']);
                Route::get('/{id}', [OrderRequestsController::class, 'show']);
                Route::post('create-request', [OrderRequestsController::class, 'createOrderRequest']);
            });

        });

        Route::prefix('/courier')->middleware(['role.verification:courier'])->group(function () {
            Route::post('/add-vehicle', [VehicleController::class, 'addVehicle']);
            Route::post('/accept-request', [CourierController::class, 'acceptOrderRequest']);
            Route::post('/update-order-status', [CourierController::class, 'updateOrderStatus']);

        });

    });

});
