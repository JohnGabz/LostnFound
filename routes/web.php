<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\Auth\TwoFactorController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\NotificationController;

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

// Redirect root to login
Route::redirect('/', '/login');

// Guest-only routes (Authentication) - Users NOT logged in
Route::middleware('guest')->group(function () {
    // Login Routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Registration Routes
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

    // Two-Factor Authentication Challenge (for users during login process)
    Route::get('/two-factor/challenge', [TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
    Route::post('/two-factor/challenge', [TwoFactorController::class, 'verify'])->name('two-factor.verify');
    Route::post('/two-factor/send-otp', [TwoFactorController::class, 'sendOtp'])->name('two-factor.send-otp');
});

// Basic Authentication Required - Users logged in but may not be verified
Route::middleware('auth')->group(function () {
    // Logout route
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
    // Email Verification Routes (user is logged in but email not verified)
    Route::get('/email/verify', [VerificationController::class, 'show'])->name('verification.notice');
    Route::post('/email/verification-notification', [VerificationController::class, 'resend'])
        ->middleware('throttle:6,1')->name('verification.send');
    
    // Email verification callback (signed route for security)
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->middleware('signed')->name('verification.verify');
});

/*
|--------------------------------------------------------------------------
| Fully Authenticated Routes
|--------------------------------------------------------------------------
| These routes require:
| 1. User to be logged in (auth)
| 2. Email to be verified (verified) 
| 3. Two-factor authentication if enabled (two-factor)
|
*/
Route::middleware(['auth', 'verified', 'two-factor'])->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | Dashboard Routes
    |--------------------------------------------------------------------------
    */
    // Main Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Enhanced Dashboard API Routes (for real-time updates)
    Route::get('/dashboard/realtime', [DashboardController::class, 'getRealtimeData'])->name('dashboard.realtime');
    Route::get('/dashboard/chart-data', [DashboardController::class, 'getChartData'])->name('dashboard.chart-data');
    
    // Dashboard Analytics & Export (optional features)
    Route::get('/dashboard/analytics', [DashboardController::class, 'analytics'])->name('dashboard.analytics');
    Route::get('/dashboard/export', [DashboardController::class, 'exportData'])->name('dashboard.export');

    /*
    |--------------------------------------------------------------------------
    | Items Management Routes
    |--------------------------------------------------------------------------
    */
    // Browse Items
    Route::get('/lost', [ItemController::class, 'lostIndex'])->name('lost.index');
    Route::get('/found', [ItemController::class, 'foundIndex'])->name('found.index');

    // Report Items
    Route::get('/items/report/{type}', [ItemController::class, 'report'])->name('items.report')
        ->where('type', 'lost|found'); // Restrict to valid types

    // Items CRUD Operations
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');
    Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::patch('/items/{item}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
    Route::patch('/items/{item}/mark-claimed', [ItemController::class, 'markAsClaimed'])->name('items.markClaimed');

    // User's Items
    Route::get('/my-items', [ItemController::class, 'myItems'])->name('items.my');

    // Item Matching
    Route::get('/items/{item}/match', [ItemController::class, 'match'])->name('items.match');

    /*
    |--------------------------------------------------------------------------
    | Claims Management Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/claims', [ClaimController::class, 'index'])->name('claims.index');
    Route::post('/claims', [ClaimController::class, 'store'])->name('claims.store');
    Route::patch('/claims/{claim}', [ClaimController::class, 'update'])->name('claims.update');
    
    // Bulk claims operations (optional)
    Route::patch('/claims/bulk-update', [ClaimController::class, 'bulkUpdate'])->name('claims.bulk-update');

    /*
    |--------------------------------------------------------------------------
    | User Profile Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/claimer/{name}', [ProfileController::class, 'claimer'])->name('profile.claimer');
    
    // Profile Security Settings
    Route::get('/profile/security', [ProfileController::class, 'security'])->name('profile.security');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication Management
    |--------------------------------------------------------------------------
    */
    Route::get('/two-factor', [TwoFactorController::class, 'show'])->name('two-factor.show');
    Route::post('/two-factor', [TwoFactorController::class, 'enable'])->name('two-factor.enable');
    Route::delete('/two-factor', [TwoFactorController::class, 'disable'])->name('two-factor.disable');
    
    // Development/Testing routes (should be removed in production)
    Route::post('/two-factor/test-otp', [TwoFactorController::class, 'testOtp'])
        ->name('two-factor.test-otp')
        ->middleware('env:local'); // Only available in local environment

    /*
    |--------------------------------------------------------------------------
    | API-like Routes for AJAX calls
    |--------------------------------------------------------------------------
    */
    Route::prefix('api')->group(function () {
        // Search routes
        Route::get('/search/items', [ItemController::class, 'searchApi'])->name('api.items.search');
        Route::get('/search/users', [ProfileController::class, 'searchUsersApi'])->name('api.users.search');
        
        // Quick stats for widgets
        Route::get('/stats/quick', [DashboardController::class, 'quickStats'])->name('api.stats.quick');
        Route::get('/stats/user/{user}', [DashboardController::class, 'userStats'])->name('api.stats.user');
        
        // Notifications
        Route::get('/notifications', [ProfileController::class, 'getNotifications'])->name('api.notifications');
        Route::patch('/notifications/{notification}/read', [ProfileController::class, 'markNotificationRead'])->name('api.notifications.read');
    });

    /*
    |--------------------------------------------------------------------------
    | Admin Routes (if user has admin role)
    |--------------------------------------------------------------------------
    */
    Route::middleware('admin')->prefix('admin')->group(function () {
        // Admin Dashboard
        Route::get('/dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');
        Route::get('/system-health', [DashboardController::class, 'systemHealth'])->name('admin.system-health');
        
        // User Management
        Route::get('/users', [ProfileController::class, 'adminUsersList'])->name('admin.users.index');
        Route::get('/users/{user}', [ProfileController::class, 'adminUserShow'])->name('admin.users.show');
        Route::patch('/users/{user}/status', [ProfileController::class, 'updateUserStatus'])->name('admin.users.status');
        Route::delete('/users/{user}', [ProfileController::class, 'deleteUser'])->name('admin.users.delete');
        
        // Items Management
        Route::get('/items', [ItemController::class, 'adminItemsList'])->name('admin.items.index');
        Route::patch('/items/{item}/moderate', [ItemController::class, 'moderateItem'])->name('admin.items.moderate');
        Route::delete('/items/{item}/force', [ItemController::class, 'forceDeleteItem'])->name('admin.items.force-delete');
        
        // Claims Management
        Route::get('/claims', [ClaimController::class, 'adminClaimsList'])->name('admin.claims.index');
        Route::patch('/claims/{claim}/moderate', [ClaimController::class, 'moderateClaim'])->name('admin.claims.moderate');
        
        // System Settings
        Route::get('/settings', [DashboardController::class, 'systemSettings'])->name('admin.settings');
        Route::patch('/settings', [DashboardController::class, 'updateSystemSettings'])->name('admin.settings.update');
        
        // Reports & Analytics
        Route::get('/reports', [DashboardController::class, 'adminReports'])->name('admin.reports');
        Route::get('/analytics/advanced', [DashboardController::class, 'advancedAnalytics'])->name('admin.analytics.advanced');
        
        // Security & Audit Logs
        Route::get('/security/logs', [DashboardController::class, 'securityLogs'])->name('admin.security.logs');
        Route::get('/security/failed-logins', [DashboardController::class, 'failedLogins'])->name('admin.security.failed-logins');
    });
    
    /*
    |--------------------------------------------------------------------------
    | Moderator Routes (if user has moderator privileges)
    |--------------------------------------------------------------------------
    */
    Route::middleware('moderator')->prefix('moderate')->group(function () {
        // Content Moderation
        Route::get('/items/pending', [ItemController::class, 'pendingModeration'])->name('moderate.items.pending');
        Route::patch('/items/{item}/approve', [ItemController::class, 'approveItem'])->name('moderate.items.approve');
        Route::patch('/items/{item}/reject', [ItemController::class, 'rejectItem'])->name('moderate.items.reject');
        
        // Claims Moderation
        Route::get('/claims/flagged', [ClaimController::class, 'flaggedClaims'])->name('moderate.claims.flagged');
        Route::patch('/claims/{claim}/resolve', [ClaimController::class, 'resolveClaim'])->name('moderate.claims.resolve');
    });
});

/*
|--------------------------------------------------------------------------
| Public Routes (accessible without authentication)
|--------------------------------------------------------------------------
*/
// Public item viewing (optional - for SEO or public access)
Route::get('/public/items/{item}', [ItemController::class, 'publicShow'])->name('public.items.show');
Route::get('/public/search', [ItemController::class, 'publicSearch'])->name('public.search');

// Help & Support Pages
Route::get('/help', function () {
    return view('help.index');
})->name('help');

Route::get('/about', function () {
    return view('about');
})->name('about');

Route::get('/privacy', function () {
    return view('privacy');
})->name('privacy');

Route::get('/terms', function () {
    return view('terms');
})->name('terms');

/*
|--------------------------------------------------------------------------
| Error Routes (for custom error pages)
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return view('errors.404');
});

/*
|--------------------------------------------------------------------------
| Development/Testing Routes
|--------------------------------------------------------------------------
| These routes should be removed or protected in production
*/
// if (app()->environment('local')) {
//     // Email preview routes
//     Route::get('/mail/preview/welcome', function () {
//         return new App\Mail\WelcomeMail(App\Models\User::first());
//     });
    
//     Route::get('/mail/preview/claim-notification', function () {
//         return new App\Mail\ClaimNotificationMail(App\Models\Claim::first());
//     });
    
//     // Test data seeding routes
//     Route::get('/dev/seed-test-data', function () {
//         Artisan::call('db:seed', ['--class' => 'TestDataSeeder']);
//         return 'Test data seeded successfully!';
//     });
    
//     // Performance testing
//     Route::get('/dev/performance-test', [DashboardController::class, 'performanceTest']);
// }