<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\AdminController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/login', [HomeController::class, 'login'])->name('login');
Route::post('/login', [App\Http\Controllers\AuthController::class, 'loginPost'])->name('login.post');
Route::get('/register', [HomeController::class, 'register'])->name('register');
Route::post('/register', [App\Http\Controllers\AuthController::class, 'registerPost'])->name('register.post');
Route::post('/logout', [App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

use App\Http\Controllers\ReservaController;
use App\Http\Controllers\PedidoController;

Route::middleware(['auth'])->group(function () {
    Route::put('/perfil/actualizar', [App\Http\Controllers\AuthController::class, 'updateProfile'])->name('perfil.update');
    Route::get('/home2', [HomeController::class, 'home2'])->name('home2');
    Route::post('/reservas', [ReservaController::class, 'store'])->name('reservas.store');
    Route::post('/reservas/disponibilidad', [ReservaController::class, 'verificarDisponibilidad'])->name('reservas.disponibilidad');
    Route::get('/reservas/{id}/pdf', [ReservaController::class, 'generarPDF'])->name('reservas.pdf');
    Route::get('/reservas/{id}', [ReservaController::class, 'show'])->name('reservas.show');
    Route::put('/reservas/{id}', [ReservaController::class, 'update'])->name('reservas.update');
    Route::delete('/reservas/{id}', [ReservaController::class, 'destroy'])->name('reservas.destroy');
    Route::post('/pedidos/verificar', [PedidoController::class, 'verificarReserva'])->name('pedidos.verificar');
    Route::post('/pedidos/disponibilidad-tiempo-real', [PedidoController::class, 'verificarDisponibilidadTiempoReal'])->name('pedidos.disponibilidad-tiempo-real');
    Route::post('/pedidos', [PedidoController::class, 'store'])->name('pedidos.store');
    Route::get('/pedidos/{id}/pdf', [PedidoController::class, 'generarPDF'])->name('pedidos.pdf');
    Route::get('/pedidos/{id}', [PedidoController::class, 'show'])->name('pedidos.show');
    Route::put('/pedidos/{id}', [PedidoController::class, 'update'])->name('pedidos.update');
    Route::delete('/pedidos/{id}', [PedidoController::class, 'destroy'])->name('pedidos.destroy');
    Route::get('/platos', [PedidoController::class, 'getPlatos'])->name('platos.index');
    Route::get('/historial/exportar', [HomeController::class, 'exportarHistorial'])->name('historial.exportar');
    Route::get('/historial', [HomeController::class, 'historial'])->name('historial');
    Route::get('/historial/pedidos', [PedidoController::class, 'historial'])->name('historial.pedidos');
    Route::get('/historial/reservas', [ReservaController::class, 'historial'])->name('historial.reservas');
});

Route::middleware(['auth', \App\Http\Middleware\AdminMiddleware::class])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/usuarios', [AdminController::class, 'usuarios'])->name('admin.usuarios');
    Route::get('/admin/menu', [AdminController::class, 'menu'])->name('admin.menu');
    Route::get('/admin/reservas', [AdminController::class, 'reservas'])->name('admin.reservas');
    Route::get('/admin/pedidos', [AdminController::class, 'pedidos'])->name('admin.pedidos');
    Route::post('/admin/usuarios', [AdminController::class, 'storeUsuario'])->name('admin.usuarios.store');
    Route::post('/admin/empleados', [AdminController::class, 'storeEmpleado'])->name('admin.empleados.store');
    Route::post('/admin/reservas', [AdminController::class, 'storeReserva'])->name('admin.reservas.store');
    Route::delete('/admin/usuarios/{id}', [AdminController::class, 'deleteUser'])->name('admin.usuarios.destroy');
    Route::post('/admin/usuarios/{id}/correo', [AdminController::class, 'enviarCorreoCliente'])->name('admin.usuarios.enviarCorreo');
    Route::post('/admin/platos', [AdminController::class, 'storePlato'])->name('admin.platos.store');
    Route::put('/admin/platos/{id}', [AdminController::class, 'updatePlato'])->name('admin.platos.update');
    Route::delete('/admin/platos/{id}', [AdminController::class, 'destroyPlato'])->name('admin.platos.destroy');
    Route::put('/admin/reservas/{id}', [AdminController::class, 'updateReserva'])->name('admin.reservas.update');
    Route::delete('/admin/reservas/{id}', [AdminController::class, 'destroyReserva'])->name('admin.reservas.destroy');
    Route::get('/admin/platos-all', [AdminController::class, 'getPlatosAdmin'])->name('admin.platos.all');
    Route::get('/admin/pedidos/{id}', [AdminController::class, 'getPedidoAdmin'])->name('admin.pedidos.show');
    Route::put('/admin/pedidos/{id}', [AdminController::class, 'updatePedido'])->name('admin.pedidos.update');
    Route::delete('/admin/pedidos/{id}', [AdminController::class, 'destroyPedido'])->name('admin.pedidos.destroy');
});

Route::get('/api/updates/check', function (\Illuminate\Http\Request $request) {
    $lastCheck = $request->query('last_check');
    if (!$lastCheck) {
        return response()->json(['has_updates' => false, 'timestamp' => now()->toIso8601String()]);
    }
    
    try {
        $lastCheckDate = \Carbon\Carbon::parse($lastCheck);
        $hasPedidoUpdates = \App\Models\Pedido::where('updated_at', '>', $lastCheckDate)->exists();
        $hasReservaUpdates = \App\Models\Reserva::where('updated_at', '>', $lastCheckDate)->exists();
        $hasPlatoUpdates = \App\Models\Plato::where('updated_at', '>', $lastCheckDate)->exists();
        $hasUserUpdates = \App\Models\User::where('updated_at', '>', $lastCheckDate)->exists();
        
        return response()->json([
            'has_updates' => $hasPedidoUpdates || $hasReservaUpdates || $hasPlatoUpdates || $hasUserUpdates,
            'timestamp' => now()->toIso8601String()
        ]);
    } catch (\Exception $e) {
        return response()->json(['has_updates' => false, 'timestamp' => now()->toIso8601String()]);
    }
})->name('api.updates.check');
