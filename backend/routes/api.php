<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\PaymentCallbackController;

// Public routes
Route::get('/health', fn() => response()->json(['status' => 'ok', 'timestamp' => now()]));

// Internal API for AI service (fix #15)
Route::post('/internal/recommendations', [RecommendationController::class, 'recommend']);

// Public product/catalog routes
Route::get('/products', [\App\Http\Controllers\ProductController::class, 'index']);
Route::get('/festivals', [\App\Http\Controllers\ProductController::class, 'festivals']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/checkout', [OrderController::class, 'checkout']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
});

// Payment callbacks (no auth needed, verified by signature)
Route::post('/payment/callback/{gateway}', [PaymentCallbackController::class, '__invoke'])->name('payment.callback');
Route::get('/payment/failure', fn() => response()->json(['error' => 'Payment failed'], 400))->name('payment.failure');
