<?php

use Illuminate\Support\Facades\Route;

//Default page
Route::get('/', function () {
    return view('authentication/login');
});

//Login and Register
Route::get('/login', function () {
    return view('authentication/login');
});
Route::get('/register', function () {
    return view('authentication/register');
});
