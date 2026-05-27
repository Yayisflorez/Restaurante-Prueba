<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/login', [HomeController::class, 'login'])->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'loginPost'])->name('login.post');
Route::get('/register', [HomeController::class, 'register'])->name('register');
Route::post('/register', [App\Http\Controllers\AuthController::class, 'registerPost'])->name('register.post');
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/home2', [HomeController::class, 'home2'])->name('home2');
});
