<?php

use App\Http\Middleware\CheckPermission;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\LobbyController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;


Route::view('/', 'layouts');
Route::get('change-language/{locale}', [LanguageController::class, 'changeLanguage'])->name('change.language');

// Authentication Routes
Route::controller(AuthController::class)->group(function () {
    Route::get('/register', 'showRegistrationForm')->name('register');
    Route::post('/register', 'register')->name('register.post');
    Route::get('/login', 'showLoginForm')->name('login');
    Route::post('/login', 'authenticate')->name('login.post');
    Route::post('/logout', 'logout')->name('logout');

    // Email Verification Routes
    Route::get('/email/verify', [AuthController::class, 'verificationNotice'])->name('verification.notice');
    Route::post('/email/verify', [AuthController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/email/resend', [AuthController::class, 'resendVerificationCode'])->name('verification.send');

    // Change Email Routes
    Route::get('/email/change', [AuthController::class, 'showChangeEmailForm'])->name('email.change');
    Route::post('/email/change', [AuthController::class, 'changeEmail'])->name('change.email');
    Route::get('/email/change/verify', [AuthController::class, 'showVerifyEmailChangeForm'])->name('email.change.verify');
    Route::post('/email/change/verify', [AuthController::class, 'verifyEmailChange']);

    // Password Reset Routes
    Route::get('/forgot-password', 'showForgotPasswordForm')->middleware('guest')->name('password.request');
    Route::post('/forgot-password', 'sendResetLinkEmail')->middleware('guest')->name('password.email');
    Route::get('/reset-password/{token}', 'showResetPasswordForm')->middleware('guest')->name('password.reset');
    Route::post('/reset-password', 'resetPassword')->middleware('guest')->name('password.update');
});

    // Invoice for email
    Route::get('/public-invoice/{order}/{token}', [OrderController::class, 'showPublicInvoice'])->name('orders.public-invoice');

// Authenticated Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/lobby', [LobbyController::class, 'index'])->name('lobby.index');
    Route::get('/home', [AuthController::class, 'layouts'])->name('home');

    // Notification Routes
    Route::middleware(['check.permission:notifications'])->prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/get', [NotificationController::class, 'getNotifications'])->name('getNotifications');
        Route::post('mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('markAllAsRead');
        Route::post('batch-action', [NotificationController::class, 'batchAction'])->name('batchAction');
        Route::post('delete-selected', [NotificationController::class, 'deleteSelected'])->name('deleteSelected');
        Route::get('{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('markAsRead');
        Route::delete('{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    // Order Routes
    Route::middleware(['check.permission:orders'])->group(function () {
        Route::resource('orders', OrderController::class);
        Route::prefix('orders')->name('orders.')->group(function () {
            Route::get('/export', [OrderController::class, 'export'])->name('export');
            Route::post('{id}/cancel', [OrderController::class, 'cancel'])->name('cancel');
            Route::post('{id}/cancel-payment', [OrderController::class, 'cancelPayment'])->name('cancel-payment');
            Route::post('{id}/complete-payment', [OrderController::class, 'completePayment'])->name('complete-payment');
            Route::get('{id}/check-payment', [OrderController::class, 'checkPayment'])->name('check-payment');
            Route::get('{id}/pay', [OrderController::class, 'pay'])->name('pay');
            Route::get('{id}/download-invoice', [OrderController::class, 'downloadInvoice'])->name('download-invoice');
        });
    });

    // Category Routes
    Route::middleware(['check.permission:categories'])->group(function () {
        Route::prefix('categories')->name('categories.')->group(function () {
            Route::post('/import', [CategoryController::class, 'import'])->name('import');
            Route::get('/export', [CategoryController::class, 'export'])->name('export');
            Route::get('/template', [CategoryController::class, 'downloadTemplate'])->name('template');
        });

        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');

        Route::middleware(['check.permission:categories.create'])->group(function () {
            Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
            Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        });

        Route::middleware(['check.permission:categories.edit'])->group(function () {
            Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
            Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        });

        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])
            ->name('categories.destroy')
            ->middleware('check.permission:categories.delete');
    });

    // Product Routes
    Route::middleware(['check.permission:products'])->group(function () {
        Route::prefix('products')->name('products.')->group(function () {
            Route::post('/import', [ProductController::class, 'import'])->name('import');
            Route::get('/export', [ProductController::class, 'export'])->name('export');
            Route::get('/download-template', [ProductController::class, 'downloadTemplate'])->name('download.template');
        });

        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

        Route::middleware(['check.permission:products.create'])->group(function () {
            Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
            Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        });

        Route::middleware(['check.permission:products.edit'])->group(function () {
            Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
            Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        });

        Route::delete('/products/{product}', [ProductController::class, 'destroy'])
            ->name('products.destroy')
            ->middleware('check.permission:products.delete');
    });

    // User Routes
    Route::middleware(['check.permission:users'])->group(function () {
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/export', [UserController::class, 'export'])->name('export');
        });
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');

        Route::middleware(['check.permission:users.create'])->group(function () {
            Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('/users', [UserController::class, 'store'])->name('users.store');
        });

        Route::middleware(['check.permission:users.edit'])->group(function () {
            Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
        });

        Route::delete('/users/{user}', [UserController::class, 'destroy'])
            ->name('users.destroy')
            ->middleware('check.permission:users.delete');
    });

    // Role Routes
    Route::middleware(['check.permission:roles'])->group(function () {
        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/{role}', [RoleController::class, 'show'])->name('roles.show');

        Route::middleware(['check.permission:roles.create'])->group(function () {
            Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
            Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        });

        Route::middleware(['check.permission:roles.edit'])->group(function () {
            Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
            Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        });

        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
            ->name('roles.destroy')
            ->middleware('check.permission:roles.delete');
    });

    // Buyer Routes
    Route::middleware(['check.permission:buyers'])->group(function () {
        Route::prefix('buyer')->name('buyer.')->group(function () {
            Route::get('/export', [BuyerController::class, 'export'])->name('export');
            Route::patch('{buyer}/restore', [BuyerController::class, 'restore'])->name('restore');
        });
        Route::get('/buyer', [BuyerController::class, 'index'])->name('buyer.index');
        Route::get('/buyer/{buyer}', [BuyerController::class, 'show'])->name('buyer.show');

        Route::middleware(['check.permission:buyers.create'])->group(function () {
            Route::get('/buyer/create', [BuyerController::class, 'create'])->name('buyer.create');
            Route::post('/buyer', [BuyerController::class, 'store'])->name('buyer.store');
        });

        Route::middleware(['check.permission:buyers.edit'])->group(function () {
            Route::get('/buyer/{buyer}/edit', [BuyerController::class, 'edit'])->name('buyer.edit');
            Route::put('/buyer/{buyer}', [BuyerController::class, 'update'])->name('buyer.update');
        });

        Route::delete('/buyer/{buyer}', [BuyerController::class, 'destroy'])
            ->name('buyer.destroy')
            ->middleware('check.permission:buyers.delete');
    });

    // Image Routes
    Route::middleware(['check.permission:images'])->group(function () {
        Route::resource('image', ImageController::class);
    });

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update.password');
});
