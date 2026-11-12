<?php

use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');
Route::view('/login', 'auth.login')->name('login');       // nama "login" dibutuhkan middleware auth Laravel
Route::view('/register', 'auth.register')->name('register');
Route::view('/dashboard', 'dashboard.index')->name('dashboard');
Route::view('/demo', 'demo');