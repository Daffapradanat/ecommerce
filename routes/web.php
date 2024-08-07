<?php

use App\Http\Controllers\{
    AuthController,
    CategoryController,
    LobbyController,
    OrderController,
    ImageController,
    ProductController,
    UserController,
    BuyerController
};
use Illuminate\Support\Facades\Route;

// Public routes
Route::view('/', 'layouts');

// Authentication routes
Route::controller(AuthController::class)->group(function () {
    Route::get('/register', 'showRegistrationForm')->name('register');
    Route::post('/register', 'register')->name('register.post');
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'authenticate')->name('login.post');
    Route::post('/logout', 'logout')->name('logout');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/lobby', [LobbyController::class, 'index'])->name('lobby.index');
    Route::get('/home', [AuthController::class, 'layouts'])->name('home');

    // Resource routes
    Route::resources([
        'users' => UserController::class,
        'buyer' => BuyerController::class,
        'image' => ImageController::class,
        'products' => ProductController::class,
        'categories' => CategoryController::class,
        'orders' => OrderController::class,
    ]);

    // Buyer routes
    Route::patch('/buyer/{buyer}/restore', [BuyerController::class, 'restore'])->name('buyer.restore');

    // Order routes
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::post('/{id}/cancel', [OrderController::class, 'cancel'])->name('cancel');
        Route::get('/{id}/check-payment', [OrderController::class, 'checkPayment'])->name('check-payment');
        Route::get('/{id}/pay', [OrderController::class, 'pay'])->name('pay');
        Route::post('/{id}/cancel-payment', [OrderController::class, 'cancelPayment'])->name('cancel-payment');
        Route::post('/{id}/complete-payment', [OrderController::class, 'completePayment'])->name('complete-payment');
    });
});

// Midtrans callback (no CSRF)
Route::post('midtrans/callback', [OrderController::class, 'midtransCallback'])
    ->name('midtrans.callback')
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
