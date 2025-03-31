<?php
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\OrderController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'], function () {
    
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
});

Route::middleware('auth:api')->group(function () {

    Route::get('products', [ProductController::class, 'list']);

    Route::group(['prefix' => 'orders'], function () {
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/', [OrderController::class, 'index']);
        Route::delete('{id}', [OrderController::class, 'destroy']);
    });
});

Route::prefix('payment')->as('api.payment.')->group(function () {
    Route::get('success', [PaymentController::class, 'successPay'])->name('success');
    Route::get('failure', [PaymentController::class, 'failure'])->name('failure');
    
    //needed in live gateways
    //Route::get('webhook', [PaymentController::class, 'webhook'])->name('failure');
});