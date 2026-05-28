<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reserva;
use App\Models\Pedido;
use App\Models\DetallePedido;

class PedidoController extends Controller
{
    public function verificarReserva(Request $request)
    {
        $request->validate([
            'mesa' => 'required',
            'codigo_referencia' => 'required'
        ]);

        $reserva = Reserva::where('mesa', $request->mesa)
            ->where('codigo_referencia', $request->codigo_referencia)
            ->first();

        if ($reserva) {
            return response()->json([
                'success' => true,
                'reserva' => $reserva
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Código de reserva o mesa incorrectos.'
        ], 404);
    }

    public function store(Request $request)
    {
        $request->validate([
            'total' => 'required|numeric',
            'notas' => 'nullable|string',
            'mesa' => 'required|string',
            'zona' => 'required|string',
            'tipo_pedido' => 'required|in:con_reserva,sin_reserva',
            'items' => 'required|array'
        ]);

        $pedido = new Pedido($request->only(['total', 'notas', 'mesa', 'zona', 'tipo_pedido']));
        $pedido->user_id = auth()->id() ?? 1;
        $pedido->estado = 'pendiente';
        $pedido->save();

        foreach ($request->items as $item) {
            $detalle = new DetallePedido();
            $detalle->pedido_id = $pedido->id;
            $detalle->plato_id = $item['id'];
            $detalle->cantidad = $item['cantidad'];
            $detalle->precio_unitario = $item['precio'];
            $detalle->subtotal = $item['cantidad'] * $item['precio'];
            $detalle->save();
        }

        return response()->json([
            'success' => true,
            'pedido' => $pedido
        ]);
    }
}
