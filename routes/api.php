<?php

use App\Http\Controllers\API\{
    AuthController,
    ProductController,
    ShoppingController,
    BuyerController
};
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Buyer routes
    Route::put('/buyer', [BuyerController::class, 'update']);

    // Product routes
    Route::get('/products', [ProductController::class, 'index']);

    // Shopping cart routes
    Route::prefix('cart')->group(function () {
        Route::post('/add', [ShoppingController::class, 'addToCart']);
        Route::get('/', [ShoppingController::class, 'showCart']);
        Route::post('/edit', [ShoppingController::class, 'editCart']);
        Route::post('/remove', [ShoppingController::class, 'removeFromCart']);
    });

    // Checkout and order routes
    Route::post('/checkout', [ShoppingController::class, 'checkout']);
    Route::prefix('orders')->group(function () {
        Route::get('/', [ShoppingController::class, 'listOrders']);
        Route::get('/{order}/payment-link', [ShoppingController::class, 'getPaymentLink']);
        Route::post('/cancel', [ShoppingController::class, 'cancelOrder']);
    });

    // Logout route
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Payment notification route (public)
Route::post('payment-notification', [ShoppingController::class, 'handlePaymentNotification']);
