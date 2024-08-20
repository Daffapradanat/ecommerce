<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ShoppingController;
use App\Http\Controllers\API\BuyerController;
use App\Http\Controllers\API\NotificationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread', [NotificationController::class, 'unreadNotifications']);
        Route::get('/read', [NotificationController::class, 'readNotifications']);
        Route::post('/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
        Route::post('/mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
        // Route::post('/new-product/{product}', [NotificationController::class, 'newProductNotification']);
        Route::post('/order-status-changed/{order}', [NotificationController::class, 'orderStatusChangedNotification']);
    });

    // Buyer
    Route::put('/buyer', [BuyerController::class, 'update']);

    // Product
    Route::get('/products', [ProductController::class, 'index']);

    // Shopping cart
    Route::prefix('cart')->group(function () {
        Route::post('/add', [ShoppingController::class, 'addToCart']);
        Route::get('/', [ShoppingController::class, 'showCart']);
        Route::post('/edit', [ShoppingController::class, 'editCart']);
        Route::post('/remove', [ShoppingController::class, 'removeFromCart']);
    });

    // Checkout and order
    Route::post('/checkout', [ShoppingController::class, 'checkout']);
    Route::post('/orders/download-invoice', [ShoppingController::class, 'downloadInvoice']);
    Route::get('/orders', [ShoppingController::class, 'listOrders']);
    Route::get('/orders/{order}/payment-link', [ShoppingController::class, 'getPaymentLink']);
    Route::post('/orders/cancel', [ShoppingController::class, 'cancelOrder']);

    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Payment notification
Route::post('payment-notification', [ShoppingController::class, 'handlePaymentNotification']);
