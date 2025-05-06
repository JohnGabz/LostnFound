<?php

use Illuminate\Support\Facades\Route;

//Default page
Route::get('/', function () {
    return view('login');
});

//Login and Register
Route::get('/login', function () {
    return view('login');
});
Route::get('/register', function () {
    return view('register');
});
