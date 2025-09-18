<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerControllers\PaymentController;

Route::get('/payment/verify', [PaymentController::class, 'verify'])->name('payment.verify');
Route::get('/payment/{invoice_id}', [PaymentController::class, 'payment']);
