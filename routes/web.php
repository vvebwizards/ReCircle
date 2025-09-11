<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/auth', function () {
    return view('auth.auth');
})->name('auth');

Route::get('/twofa', function () {
    return view('auth.twofa');
})->name('twofa');

Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->name('forgot-password');

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');
