<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('/v1')->group(function () {

    Route::prefix('/users')->group(function () {
        Route::post('/register', [\App\Http\Controllers\PublicControllers\UsersController::class, 'register']);
        Route::post('/authenticate', [\App\Http\Controllers\PublicControllers\UsersController::class, 'authenticate']);
    });

    Route::middleware(['auth:sanctum'])->group(function () {

        Route::prefix('/customer')->middleware(['role.verification:customer'])->group(function () {

            Route::prefix('/order-requests')->group(function () {
                Route::get('/', [\App\Http\Controllers\CustomerControllers\OrderRequestsController::class, 'index']);
                Route::get('/{id}', [\App\Http\Controllers\CustomerControllers\OrderRequestsController::class, 'show']);
            });

        });

    });

});
