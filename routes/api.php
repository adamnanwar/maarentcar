<?php

use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Khusus untuk Midtrans Webhook (public, no CSRF protection)
|
*/

Route::post('/midtrans/webhook', [PaymentController::class, 'webhook']);
