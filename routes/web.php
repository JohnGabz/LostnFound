<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\ProfileController;


// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Protected Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/lost', [ItemController::class, 'lostIndex'])->name('lost.index');
    Route::get('/found', [ItemController::class, 'foundIndex'])->name('found.index');
    Route::get('/items/report/{type}', [ItemController::class, 'report'])->name('items.report');
    Route::post('/items', [ItemController::class, 'store'])->name('items.store');
    Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');
    Route::get('/items/{item}/edit', [ItemController::class, 'edit'])->name('items.edit');
    Route::patch('/items/{item}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{item}', [ItemController::class, 'destroy'])->name('items.destroy');
    Route::get('/my-items', [ItemController::class, 'myItems'])->name('items.my');
    Route::get('/claims', [ClaimController::class, 'index'])->name('claims.index');
    Route::post('/claims', [ClaimController::class, 'store'])->name('claims.store');
    Route::patch('/claims/{claim}', [ClaimController::class, 'update'])->name('claims.update');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/items/{item}/match', [ItemController::class, 'match'])->name('items.match');
});

// Home route
Route::get('/', function () {
    return redirect()->route('login');
});

// //Default page
// Route::get('/', function () {
//     return view('authentication/login');
// });

// //Login and Register
// Route::get('/login', function () {
//     return view('authentication/login');
// });
// Route::get('/register', function () {
//     return view('authentication/register');
// });



