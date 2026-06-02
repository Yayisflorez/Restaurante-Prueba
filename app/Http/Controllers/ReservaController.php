<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reserva;
use Illuminate\Support\Str;

class ReservaController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'fecha' => 'required|date',
                'hora' => 'required',
                'personas' => 'required|integer',
                'zona' => 'required|string',
                'mesas' => 'required|array',
                'notas' => 'nullable|string'
            ]);

            $mesas = $request->mesas;
            $mesasString = implode(',', $mesas);

            $reserva = new Reserva([
                'fecha' => $request->fecha,
                'hora' => $request->hora,
                'personas' => $request->personas,
                'zona' => $request->zona,
                'mesa' => $mesasString,
                'notas' => $request->notas ?? ''
            ]);
            $reserva->user_id = auth()->id() ?? 1;
            $reserva->codigo_referencia = strtoupper(Str::random(8));
            $reserva->estado = 'pendiente';
            $reserva->save();

            return response()->json([
                'success' => true,
                'reserva' => $reserva
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . implode(', ', $e->errors())
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear reserva: ' . $e->getMessage()
            ], 500);
        }
    }

    public function historial()
    {
        $reservas = Reserva::where('user_id', auth()->id())
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

        return response()->json([
            'success' => true,
            'historial' => $reservas
        ]);
    }

    public function verificarDisponibilidad(Request $request)
    {
        $request->validate([
            'fecha' => 'required|date',
            'hora' => 'required',
            'zona' => 'required|string'
        ]);

        $fecha = $request->fecha;
        $hora = $request->hora;
        $zona = $request->zona;

        // Obtener mesas reservadas para la fecha, hora y zona específicos
        $reservas = Reserva::where('fecha', $fecha)
            ->where('hora', $hora)
            ->where('zona', $zona)
            ->where('estado', '!=', 'cancelada')
            ->get();

        // Expandir mesas ya que ahora pueden estar separadas por comas
        $mesasReservadas = [];
        foreach ($reservas as $reserva) {
            $mesasArray = explode(',', $reserva->mesa);
            foreach ($mesasArray as $mesa) {
                $mesasReservadas[] = trim($mesa);
            }
        }

        // Si la reserva es para la fecha y hora actual, también considerar pedidos sin reserva
        $fechaActual = now()->format('Y-m-d');
        $horaActual = now()->format('H:i');
        
        if ($fecha === $fechaActual && $hora === $horaActual) {
            $horaLimite = now()->subHour();
            $mesasOcupadasPedidos = \App\Models\Pedido::where('tipo_pedido', 'sin_reserva')
                ->where('created_at', '>=', $horaLimite)
                ->where('estado', '!=', 'completado')
                ->pluck('mesa')
                ->toArray();

            // Expandir mesas de pedidos
            foreach ($mesasOcupadasPedidos as $mesa) {
                $mesasArray = explode(',', $mesa);
                foreach ($mesasArray as $m) {
                    $mesasReservadas[] = trim($m);
                }
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
                'disponible' => !in_array((string)$mesa, $mesasReservadas)
            ];
        }

        return response()->json([
            'success' => true,
            'disponibilidad' => $disponibilidad
        ]);
    }
}
