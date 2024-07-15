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
    Route::delete('/cart/remove/{product_id}', [ShoppingController::class, 'removeFromCart']);
    Route::post('/checkout', [ShoppingController::class, 'checkout']);
    Route::get('/orders', [ShoppingController::class, 'listOrders']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
