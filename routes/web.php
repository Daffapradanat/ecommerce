<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LobbyController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BuyerController;
use Illuminate\Support\Facades\Route;
use Laravel\Telescope\Telescope;

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

Route::get('/', function () {
    return view('layouts');
});

// Authentication Routes
Route::get('/register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/lobby', [LobbyController::class, 'index'])->name('lobby.index');
    Route::get('/home', [AuthController::class, 'layouts'])->name('home');

    // Resource Routes
    Route::resources([
        'users' => UserController::class,
        'buyer' => BuyerController::class,
        'image' => ImageController::class,
        'products' => ProductController::class,
        'categories' => CategoryController::class,
        'orders' => OrderController::class,
    ]);

    // Order-related Routes
    Route::prefix('orders')->group(function () {
        Route::post('midtrans/callback', [OrderController::class, 'midtransCallback'])->name('midtrans.callback')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
        Route::get('/orders/{id}/check-payment', [OrderController::class, 'checkPayment'])->name('orders.check-payment');
        Route::get('{id}/pay', [OrderController::class, 'pay'])->name('orders.pay');
        Route::post('/orders/{id}/cancel-payment', [OrderController::class, 'cancelPayment'])->name('orders.cancel-payment');
        Route::get('{id}/pay', [OrderController::class, 'pay'])->name('orders.pay');
        Route::post('/orders/{id}/complete-payment', [OrderController::class, 'completePayment'])->name('orders.complete-payment');
    });
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
