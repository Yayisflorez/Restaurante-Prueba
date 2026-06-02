<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/login', [HomeController::class, 'login'])->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'loginPost'])->name('login.post');
Route::get('/register', [HomeController::class, 'register'])->name('register');
Route::post('/register', [App\Http\Controllers\AuthController::class, 'registerPost'])->name('register.post');
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

use App\Http\Controllers\ReservaController;
use App\Http\Controllers\PedidoController;

Route::middleware(['auth'])->group(function () {
    Route::get('/home2', [HomeController::class, 'home2'])->name('home2');
    Route::post('/reservas', [ReservaController::class, 'store'])->name('reservas.store');
    Route::post('/reservas/disponibilidad', [ReservaController::class, 'verificarDisponibilidad'])->name('reservas.disponibilidad');
    Route::post('/pedidos/verificar', [PedidoController::class, 'verificarReserva'])->name('pedidos.verificar');
    Route::post('/pedidos/disponibilidad-tiempo-real', [PedidoController::class, 'verificarDisponibilidadTiempoReal'])->name('pedidos.disponibilidad-tiempo-real');
    Route::post('/pedidos', [PedidoController::class, 'store'])->name('pedidos.store');
    Route::get('/historial', [HomeController::class, 'historial'])->name('historial');
    Route::get('/historial/pedidos', [PedidoController::class, 'historial'])->name('historial.pedidos');
    Route::get('/historial/reservas', [ReservaController::class, 'historial'])->name('historial.reservas');
});
