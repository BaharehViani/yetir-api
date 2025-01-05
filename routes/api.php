<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('/v1')->group(function () {

    Route::prefix('/users')->group(function () {
        Route::post('/register', [\App\Http\Controllers\PublicControllers\UsersController::class, 'register']);
        Route::post('/authenticate', [\App\Http\Controllers\PublicControllers\UsersController::class, 'authenticate']);
    });

});
