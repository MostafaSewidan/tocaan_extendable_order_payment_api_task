<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SewidanFakeGatewayController;

Route::prefix('fake-gateway')->group(function () {
    Route::get('/checkout', [SewidanFakeGatewayController::class, 'checkout'])->name('fake-gateway.checkout');
    Route::get('/pay', [SewidanFakeGatewayController::class, 'showPaymentForm'])->name('fake-gateway.pay');
    Route::post('/pay/{id}', [SewidanFakeGatewayController::class, 'processPayment'])->name('fake-gateway.process');
});
