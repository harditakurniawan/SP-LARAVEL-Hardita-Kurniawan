<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->group(function () {
    Route::apiResource('customers', CustomerController::class)->only(['index', 'store', 'show']);
    Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show']);
    Route::apiResource('products', ProductController::class);

    Route::post('/orders/paid', [OrderController::class, 'markAsPaid']);
    Route::post('/orders/canceled', [OrderController::class, 'markAsCanceled']);
});
