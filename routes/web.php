<?php

use Illuminate\Support\Facades\Route;

Route::get('/payment/verify', [App\Http\Controllers\CustomerControllers\PaymentController::class, 'verify'])->name('payment.verify');
Route::get('/payment/{invoice_id}', [App\Http\Controllers\CustomerControllers\PaymentController::class, 'payment'])->name('payment');
