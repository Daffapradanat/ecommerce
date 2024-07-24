<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ShoppingController;
use App\Http\Controllers\API\BuyerController;
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

    Route::put('/buyer', [BuyerController::class, 'update']);

    Route::get('/products', [ProductController::class, 'index']);

    Route::post('/cart/add', [ShoppingController::class, 'addToCart']);
    Route::get('/cart', [ShoppingController::class, 'showCart']);
    Route::post('/cart/edit', [ShoppingController::class, 'editCart']);
    Route::post('/cart/remove', [ShoppingController::class, 'removeFromCart']);

    Route::post('/checkout', [ShoppingController::class, 'checkout']);

    Route::get('/orders', [ShoppingController::class, 'listOrders']);
    Route::get('/orders/{order}/payment-link', [ShoppingController::class, 'getPaymentLink']);
    Route::post('/orders/cancel', [ShoppingController::class, 'cancelOrder']);

    Route::post('payment-notification', [ShoppingController::class, 'handlePaymentNotification']);

    Route::post('/logout', [AuthController::class, 'logout']);
});
