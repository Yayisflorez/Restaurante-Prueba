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
            'codigo_referencia' => 'required',
            'mesas' => 'nullable|array'
        ]);

        // Si se envía el array de mesas, verificar que todas estén en la reserva
        if ($request->has('mesas') && is_array($request->mesas)) {
            $reserva = Reserva::where('codigo_referencia', $request->codigo_referencia)
                ->first();

            if ($reserva) {
                // Expandir mesas de la reserva
                $mesasReserva = explode(',', $reserva->mesa);
                $mesasReserva = array_map('trim', $mesasReserva);
                
                // Verificar que todas las mesas enviadas estén en la reserva
                $mesasEnviadas = $request->mesas;
                $todasCoinciden = true;
                
                foreach ($mesasEnviadas as $mesa) {
                    if (!in_array($mesa, $mesasReserva)) {
                        $todasCoinciden = false;
                        break;
                    }
                }
                
                if ($todasCoinciden) {
                    return response()->json([
                        'success' => true,
                        'reserva' => $reserva
                    ]);
                }
            }

            return response()->json([
                'success' => false,
                'message' => 'Las mesas no coinciden con la reserva.'
            ], 404);
        }

        // Verificación tradicional (una sola mesa)
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
            'mesa' => 'required',
            'zona' => 'required|string',
            'tipo_pedido' => 'required|in:con_reserva,sin_reserva',
            'items' => 'required|array'
        ]);

        // Manejar mesa como array o string
        $mesas = $request->mesa;
        if (is_array($mesas)) {
            $mesasString = implode(',', $mesas);
        } else {
            $mesasString = $mesas;
        }

        $pedido = new Pedido([
            'total' => $request->total,
            'notas' => $request->notas,
            'mesa' => $mesasString,
            'zona' => $request->zona,
            'tipo_pedido' => $request->tipo_pedido
        ]);
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

    public function historial()
    {
        $pedidos = Pedido::where('user_id', auth()->id())
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

        return response()->json([
            'success' => true,
            'historial' => $pedidos
        ]);
    }

    public function verificarDisponibilidadTiempoReal(Request $request)
    {
        $request->validate([
            'zona' => 'required|string'
        ]);

        $zona = $request->zona;

        // Obtener mesas ocupadas por pedidos sin reserva en tiempo real (última hora)
        $horaLimite = now()->subHour();
        $mesasOcupadasPedidos = Pedido::where('tipo_pedido', 'sin_reserva')
            ->where('created_at', '>=', $horaLimite)
            ->where('estado', '!=', 'completado')
            ->pluck('mesa')
            ->toArray();

        // Expandir mesas de pedidos (pueden estar separadas por comas)
        $mesasOcupadas = [];
        foreach ($mesasOcupadasPedidos as $mesa) {
            $mesasArray = explode(',', $mesa);
            foreach ($mesasArray as $m) {
                $mesasOcupadas[] = trim($m);
            }
        }

        // Obtener mesas reservadas para la fecha y hora actual
        $fechaActual = now()->format('Y-m-d');
        $horaActual = now()->format('H:i');
        
        $mesasReservadas = Reserva::where('fecha', $fechaActual)
            ->where('hora', $horaActual)
            ->where('zona', $zona)
            ->where('estado', '!=', 'cancelada')
            ->pluck('mesa')
            ->toArray();

        // Expandir mesas de reservas
        foreach ($mesasReservadas as $mesa) {
            $mesasArray = explode(',', $mesa);
            foreach ($mesasArray as $m) {
                $mesasOcupadas[] = trim($m);
            }
        }

        // Calcular rango de mesas por zona (10 mesas por zona)
        $zonasMesas = [
            'interior' => range(1, 10),
            'terraza' => range(11, 20),
            'privado' => range(21, 30)
        ];

        $mesasZona = $zonasMesas[$zona] ?? range(1, 10);

        // Generar estado de cada mesa
        $disponibilidad = [];
        foreach ($mesasZona as $mesa) {
            $disponibilidad[] = [
                'mesa' => $mesa,
                'disponible' => !in_array((string)$mesa, $mesasOcupadas)
            ];
        }

        return response()->json([
            'success' => true,
            'disponibilidad' => $disponibilidad
        ]);
    }
}
