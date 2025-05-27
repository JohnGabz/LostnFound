<?php

use App\Http\Controllers\LogsController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\NotificationController;

// Redirect root to login
Route::redirect('/', '/login');

// Guest-only routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    // Password Reset Routes
    Route::get('/forgot-password', [App\Http\Controllers\Auth\PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [App\Http\Controllers\Auth\PasswordResetController::class, 'sendResetOtp'])->name('password.send-otp');
    Route::get('/reset-password/verify', [App\Http\Controllers\Auth\PasswordResetController::class, 'showVerifyOtpForm'])->name('password.verify-otp');
    Route::post('/reset-password/verify', [App\Http\Controllers\Auth\PasswordResetController::class, 'verifyOtp']);
    Route::get('/reset-password/{token}', [App\Http\Controllers\Auth\PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [App\Http\Controllers\Auth\PasswordResetController::class, 'resetPassword'])->name('password.update');
    Route::post('/reset-password/resend-otp', [App\Http\Controllers\Auth\PasswordResetController::class, 'resendOtp'])->name('password.resend-otp');

    // Two-Factor Authentication Challenge
    Route::get('/two-factor/challenge', [TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
    Route::post('/two-factor/challenge', [TwoFactorController::class, 'verify'])->name('two-factor.verify');
    Route::post('/two-factor/send-otp', [TwoFactorController::class, 'sendOtp'])->name('two-factor.send-otp');
});

// Basic Authentication Required
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Email Verification Routes
    Route::get('/email/verify', [VerificationController::class, 'show'])->name('verification.notice');
    Route::post('/email/verification-notification', [VerificationController::class, 'resend'])
        ->middleware('throttle:6,1')->name('verification.send');
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware('signed')->name('verification.verify');
});

// Fully Authenticated Routes
Route::middleware(['auth', 'verified', 'two-factor'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Items Routes - FIXED ORDER
    Route::get('/lost', [ItemController::class, 'lostIndex'])->name('lost.index');
    Route::get('/found', [ItemController::class, 'foundIndex'])->name('found.index');
    Route::get('/items/report/{type}', [ItemController::class, 'report'])->name('items.report')
        ->where('type', 'lost|found');
    Route::get('/my-items', [ItemController::class, 'myItems'])->name('items.my');

    // Items CRUD - FIXED: Specific routes before generic ones
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::patch('/items/{item}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
    Route::patch('/items/{item}/mark-claimed', [ItemController::class, 'markAsClaimed'])->name('items.markClaimed');
    Route::get('/items/{item}/match', [ItemController::class, 'match'])->name('items.match');
    // IMPORTANT: Generic show route MUST be last
    Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');

    // Claims Management
    Route::get('/claims', [ClaimController::class, 'index'])->name('claims.index');
    Route::post('/claims', [ClaimController::class, 'store'])->name('claims.store');
    Route::patch('/claims/{claim}', [ClaimController::class, 'update'])->name('claims.update');

    // Notifications - FIXED: Added missing routes
    Route::get('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::get('/notifications/mark-all-read', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');

    // User Profile Routes
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Two-Factor Authentication
    Route::get('/two-factor', [TwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('/two-factor', [TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::delete('/two-factor', [TwoFactorController::class, 'disable'])->name('two-factor.disable');

    // Admin Routes
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/logs', [LogsController::class, 'index'])->name('logs.index');
    });
});
