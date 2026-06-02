<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $categorias = \App\Models\Categoria::with('platos')->get();
        return view('home', compact('categorias'));
    }

    public function login()
    {
        return view('login');
    }

    public function register()
    {
        $tipo_documentos = \App\Models\TipoDocumento::all();
        return view('register', compact('tipo_documentos'));
    }

    public function home2()
    {
        $categorias = \App\Models\Categoria::with('platos')->get();
        $metodos_pago = \App\Models\MetodoPago::where('activo', true)->get();
        return view('home2', compact('categorias', 'metodos_pago'));
    }

    public function historial()
    {
        $pedidos = \App\Models\Pedido::where('user_id', auth()->id())
            ->with(['detalles.plato'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($pedido) {
                $detalles = $pedido->detalles->map(function ($detalle) {
                    return $detalle->plato->nombre . ' x' . $detalle->cantidad;
                })->join(', ');
                
                return [
                    'tipo' => 'pedido',
                    'detalle' => $detalles ?: 'Sin detalles',
                    'fecha' => $pedido->created_at->format('d/m/Y'),
                    'estado' => $pedido->estado
                ];
            });

        $reservas = \App\Models\Reserva::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($reserva) {
                return [
                    'tipo' => 'reserva',
                    'detalle' => "Mesa {$reserva->mesa} - {$reserva->personas} persona(s) - {$reserva->zona}",
                    'fecha' => $reserva->created_at->format('d/m/Y'),
                    'estado' => $reserva->estado
                ];
            });

        $historial = $pedidos->concat($reservas)->sortByDesc('fecha')->values();

        return response()->json([
            'success' => true,
            'historial' => $historial
        ]);
    }
}
