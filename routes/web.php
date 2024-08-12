<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\LobbyController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::view('/', 'layouts');

// Authentication Routes
Route::controller(AuthController::class)->group(function () {
    Route::get('/register', 'showRegistrationForm')->name('register');
    Route::post('/register', 'register')->name('register.post');
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'authenticate')->name('login.post');
    Route::post('/logout', 'logout')->name('logout');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/lobby', [LobbyController::class, 'index'])->name('lobby.index');
    Route::get('/home', [AuthController::class, 'layouts'])->name('home');

    // Order Routes
    Route::prefix('orders')->name('orders.')->group(function () {
        // Buyer Route
        Route::patch('/buyer/{buyer}/restore', [BuyerController::class, 'restore'])->name('buyer.restore');

        // Order Routes
        Route::post('{id}/cancel', [OrderController::class, 'cancel'])->name('cancel');
        Route::post('{id}/cancel-payment', [OrderController::class, 'cancelPayment'])->name('cancel-payment');
        Route::post('{id}/complete-payment', [OrderController::class, 'completePayment'])->name('complete-payment');
        Route::get('{id}/check-payment', [OrderController::class, 'checkPayment'])->name('check-payment');
        Route::get('{id}/pay', [OrderController::class, 'pay'])->name('pay');
        Route::post('midtrans/callback', [OrderController::class, 'midtransCallback'])
            ->name('midtrans.callback')
            ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

        // Export
        Route::get('/export', [OrderController::class, 'export'])->name('export');
    });

    // Category Routes
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::post('/import', [CategoryController::class, 'import'])->name('import');
        Route::get('/export', [CategoryController::class, 'export'])->name('export');
        Route::get('/template', [CategoryController::class, 'downloadTemplate'])->name('template');
    });

    // Product Routes
    Route::prefix('products')->name('products.')->group(function () {
        Route::post('/products/upload-image', [ProductController::class, 'uploadImage'])->name('products.upload-image');
        Route::post('/import', [ProductController::class, 'import'])->name('import');
        Route::get('/export', [ProductController::class, 'export'])->name('export');
        Route::get('/download-template', [ProductController::class, 'downloadTemplate'])->name('download.template');
    });

    // User Routes
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/export', [UserController::class, 'export'])->name('export');
    });

    // Buyer Routes
    Route::prefix('buyer')->name('buyer.')->group(function () {
        Route::get('/export', [BuyerController::class, 'export'])->name('export');
    });

    // Resource Routes
    Route::resources([
        'users' => UserController::class,
        'buyer' => BuyerController::class,
        'image' => ImageController::class,
        'products' => ProductController::class,
        'categories' => CategoryController::class,
        'orders' => OrderController::class,
    ]);
});
