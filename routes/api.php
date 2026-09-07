<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;

Route::apiResource('products', ProductController::class)->except(['destroy']);
Route::patch('products/{id}/stock', [ProductController::class, 'updateStock']);

Route::apiResource('orders', OrderController::class)->only(['index', 'show', 'store']);
Route::post('orders/{id}/cancel', [OrderController::class, 'cancel']);
