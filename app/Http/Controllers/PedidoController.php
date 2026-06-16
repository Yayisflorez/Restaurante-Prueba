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

        $user = auth()->user();
        if ($user) {
            \App\Services\MailerService::sendEmail(
                $user->email,
                $user->name,
                'Pedido Confirmado - Sabor & Tradición',
                '¡Hemos recibido tu pedido!',
                "Hola {$user->name},<br><br>Hemos recibido tu pedido para la mesa {$mesasString} en la zona {$request->zona}.<br>El total de tu pedido es de <b>$" . number_format($request->total, 2) . "</b>."
            );
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
                    'id' => $pedido->id,
                    'tipo' => 'pedido',
                    'detalle' => $detalles ?: 'Sin detalles',
                    'fecha_creacion' => $pedido->created_at->format('d/m/Y H:i'),
                    'fecha' => $pedido->created_at->format('d/m/Y'),
                    'fecha_reserva' => null,
                    'estado' => $pedido->estado
                ];
            });

        return response()->json([
            'success' => true,
            'historial' => $pedidos
        ]);
    }

    public function generarPDF($id)
    {
        $pedido = Pedido::where('id', $id)
            ->where('user_id', auth()->id())
            ->with(['detalles.plato'])
            ->first();

        if (!$pedido) {
            return response()->json(['success' => false, 'message' => 'Pedido no encontrado'], 404);
        }

        $codigoReporte = 'RPT-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(false);

        // ─── Paleta ───────────────────────────────────────────────────────────────
        $gold = [194, 154, 38];
        $goldL = [240, 220, 140];
        $dark = [30, 30, 30];
        $white = [255, 255, 255];
        $gray = [110, 110, 110];
        $lightBg = [250, 248, 242];
        $lineSep = [220, 200, 120];
        $cream = [253, 251, 245];
        $cream2 = [246, 242, 230];

        // ─── HEADER DEGRADADO ─────────────────────────────────────────────────────
        for ($i = 0; $i < 60; $i++) {
            $r = (int) (175 + ($i / 60) * 30);
            $g = (int) (135 + ($i / 60) * 18);
            $b = (int) (18 + ($i / 60) * 15);
            $pdf->SetFillColor($r, $g, $b);
            $pdf->Rect(0, $i, 210, 1, 'F');
        }

        // ─── LOGO proporcional grande ─────────────────────────────────────────────
        $logoPath = public_path('img/LogoRestaurant.png');
        if (file_exists($logoPath)) {
            $logoInfo = getimagesize($logoPath);
            if ($logoInfo) {
                $logoW_px = $logoInfo[0];
                $logoH_px = $logoInfo[1];
                $ratio = $logoH_px / $logoW_px;
                $logoW = 38;
                $logoH = $logoW * $ratio;
                $logoX = (210 - $logoW) / 2;
                $logoY = max(3, (58 - $logoH) / 2);
                $pdf->Image($logoPath, $logoX, $logoY, $logoW, $logoH);
            }
        }

        // Título
        $pdf->SetY(44);
        $pdf->SetTextColor($white[0], $white[1], $white[2]);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 8, 'COMPROBANTE DE PEDIDO', 0, 1, 'C');

        // Línea dorada decorativa
        $pdf->SetFillColor($goldL[0], $goldL[1], $goldL[2]);
        $pdf->Rect(0, 60, 210, 2.5, 'F');

        // ─── FONDO CUERPO ─────────────────────────────────────────────────────────
        $pdf->SetFillColor($lightBg[0], $lightBg[1], $lightBg[2]);
        $pdf->Rect(0, 62, 210, 206, 'F');

        // Código de reporte
        $pdf->SetY(65);
        $pdf->SetTextColor($gray[0], $gray[1], $gray[2]);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 5, 'Reporte: ' . $codigoReporte, 0, 1, 'C');

        // ─── TARJETA INFO DEL PEDIDO ──────────────────────────────────────────────
        $cardX = 20;
        $cardY = 74;
        $cardW = 170;
        $infoItems = [
            ['Pedido Nro.', '#' . $pedido->id],
            ['Mesa', $pedido->mesa],
            ['Zona', ucfirst($pedido->zona)],
            ['Fecha', $pedido->created_at->format('d/m/Y H:i')],
        ];
        $rowH = 13;
        $infoH = count($infoItems) * $rowH + 14;

        // Sombra
        $pdf->SetFillColor(195, 182, 148);
        $pdf->Rect($cardX + 3, $cardY + 3, $cardW, $infoH, 'F');
        // Fondo
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect($cardX, $cardY, $cardW, $infoH, 'F');
        // Borde
        $pdf->SetDrawColor($gold[0], $gold[1], $gold[2]);
        $pdf->SetLineWidth(0.9);
        $pdf->Rect($cardX, $cardY, $cardW, $infoH, 'D');
        $pdf->SetLineWidth(0.2);

        // Barra título tarjeta
        $pdf->SetFillColor($gold[0], $gold[1], $gold[2]);
        $pdf->Rect($cardX, $cardY, $cardW, 13, 'F');
        $pdf->SetFillColor($goldL[0], $goldL[1], $goldL[2]);
        $pdf->Rect($cardX, $cardY + 11.5, $cardW, 1, 'F');
        $pdf->SetTextColor($white[0], $white[1], $white[2]);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetXY($cardX, $cardY);
        $pdf->Cell($cardW, 13, 'INFORMACION DEL PEDIDO', 0, 1, 'C');

        // Filas en dos columnas (2x2)
        $colW = $cardW / 2;
        $labelW = 30;
        foreach ($infoItems as $i => $row) {
            $col = $i % 2;
            $fila = (int) ($i / 2);
            $x = $cardX + $col * $colW + 4;
            $y = $cardY + 14 + $fila * $rowH;

            if ($fila % 2 === 0) {
                $pdf->SetFillColor(...$cream);
            } else {
                $pdf->SetFillColor(...$cream2);
            }
            if ($col === 0) {
                $pdf->Rect($cardX + 1, $y, $cardW - 2, $rowH - 1, 'F');
            }

            // Línea divisora vertical entre columnas
            $pdf->SetDrawColor($lineSep[0], $lineSep[1], $lineSep[2]);
            $pdf->SetLineWidth(0.15);
            if ($col === 0) {
                $pdf->Line($cardX + $colW, $y, $cardX + $colW, $y + $rowH - 1);
            }
            // Separador horizontal
            $pdf->Line($cardX + 1, $y + $rowH - 1, $cardX + $cardW - 1, $y + $rowH - 1);

            $pdf->SetTextColor($gold[0], $gold[1], $gold[2]);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetXY($x, $y + 3);
            $pdf->Cell($labelW, 5, $row[0] . ':', 0, 0, 'L');

            $pdf->SetTextColor($dark[0], $dark[1], $dark[2]);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY($x + $labelW, $y + 3);
            $pdf->Cell($colW - $labelW - 6, 5, $row[1], 0, 0, 'L');
        }

        // ─── TARJETA DETALLE DE ITEMS ─────────────────────────────────────────────
        $tblY = $cardY + $infoH + 10;
        $nItems = count($pedido->detalles);
        $tblH = $nItems * 9 + 32;

        // Sombra
        $pdf->SetFillColor(195, 182, 148);
        $pdf->Rect($cardX + 3, $tblY + 3, $cardW, $tblH, 'F');
        // Fondo
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect($cardX, $tblY, $cardW, $tblH, 'F');
        // Borde
        $pdf->SetDrawColor($gold[0], $gold[1], $gold[2]);
        $pdf->SetLineWidth(0.9);
        $pdf->Rect($cardX, $tblY, $cardW, $tblH, 'D');
        $pdf->SetLineWidth(0.2);

        // Barra título tabla
        $pdf->SetFillColor($gold[0], $gold[1], $gold[2]);
        $pdf->Rect($cardX, $tblY, $cardW, 13, 'F');
        $pdf->SetFillColor($goldL[0], $goldL[1], $goldL[2]);
        $pdf->Rect($cardX, $tblY + 11.5, $cardW, 1, 'F');
        $pdf->SetTextColor($white[0], $white[1], $white[2]);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetXY($cardX, $tblY);
        $pdf->Cell($cardW, 13, 'DETALLE DE ITEMS', 0, 1, 'C');

        // Cabecera columnas
        $pdf->SetFillColor($dark[0], $dark[1], $dark[2]);
        $pdf->Rect($cardX + 1, $tblY + 13, $cardW - 2, 9, 'F');
        $pdf->SetTextColor($goldL[0], $goldL[1], $goldL[2]);
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->SetXY($cardX + 5, $tblY + 15);
        $pdf->Cell(85, 5, 'PLATO', 0, 0, 'L');
        $pdf->Cell(25, 5, 'CANT.', 0, 0, 'C');
        $pdf->Cell(27, 5, 'P. UNIT.', 0, 0, 'C');
        $pdf->Cell(25, 5, 'SUBTOTAL', 0, 0, 'R');

        // Filas de detalles
        $rowY = $tblY + 23;
        foreach ($pedido->detalles as $j => $detalle) {
            $pdf->SetFillColor(...($j % 2 === 0 ? $cream : $cream2));
            $pdf->Rect($cardX + 1, $rowY, $cardW - 2, 8, 'F');

            // Línea vertical divisora
            $pdf->SetDrawColor($lineSep[0], $lineSep[1], $lineSep[2]);
            $pdf->SetLineWidth(0.15);
            $pdf->Line($cardX + 91, $rowY, $cardX + 91, $rowY + 8);
            $pdf->Line($cardX + 116, $rowY, $cardX + 116, $rowY + 8);
            $pdf->Line($cardX + 143, $rowY, $cardX + 143, $rowY + 8);

            $pdf->SetTextColor($dark[0], $dark[1], $dark[2]);
            $pdf->SetFont('Arial', '', 8);
            $pdf->SetXY($cardX + 5, $rowY + 1.5);
            $pdf->Cell(85, 5, $detalle->plato->nombre, 0, 0, 'L');
            $pdf->Cell(25, 5, $detalle->cantidad, 0, 0, 'C');
            $pdf->Cell(27, 5, '$' . number_format($detalle->precio_unitario, 2), 0, 0, 'C');
            $pdf->Cell(25, 5, '$' . number_format($detalle->subtotal, 2), 0, 0, 'R');

            // Separador horizontal
            $pdf->SetDrawColor($lineSep[0], $lineSep[1], $lineSep[2]);
            $pdf->Line($cardX + 1, $rowY + 8, $cardX + $cardW - 1, $rowY + 8);
            $rowY += 9;
        }

        // Fila TOTAL
        $pdf->SetFillColor($gold[0], $gold[1], $gold[2]);
        $pdf->Rect($cardX + 1, $rowY, $cardW - 2, 10, 'F');
        $pdf->SetFillColor($goldL[0], $goldL[1], $goldL[2]);
        $pdf->Rect($cardX + 1, $rowY, $cardW - 2, 2, 'F');
        $pdf->SetTextColor($white[0], $white[1], $white[2]);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY($cardX + 5, $rowY + 2);
        $pdf->Cell(115, 6, 'TOTAL A PAGAR', 0, 0, 'R');
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->Cell(27, 6, '$' . number_format($pedido->total, 2), 0, 0, 'R');

        // ─── BADGE ESTADO ─────────────────────────────────────────────────────────
        $badgeY = $tblY + $tblH + 10;
        $estado = strtolower($pedido->estado);

        if ($estado === 'completado') {
            $badgeColor = [34, 120, 34];
            $badgeColorL = [180, 230, 180];
            $icono = 'COMPLETADO';
        } elseif ($estado === 'cancelado') {
            $badgeColor = [160, 35, 35];
            $badgeColorL = [230, 180, 180];
            $icono = 'CANCELADO';
        } elseif ($estado === 'en_proceso') {
            $badgeColor = [30, 100, 180];
            $badgeColorL = [180, 210, 240];
            $icono = 'EN PROCESO';
        } else {
            $badgeColor = $gold;
            $badgeColorL = $goldL;
            $icono = 'PENDIENTE';
        }

        // Sombra badge
        $pdf->SetFillColor(150, 140, 110);
        $pdf->Rect(63, $badgeY + 2, 86, 14, 'F');
        // Fondo badge
        $pdf->SetFillColor($badgeColor[0], $badgeColor[1], $badgeColor[2]);
        $pdf->Rect(60, $badgeY, 90, 14, 'F');
        // Franja luz
        $pdf->SetFillColor($badgeColorL[0], $badgeColorL[1], $badgeColorL[2]);
        $pdf->Rect(60, $badgeY, 90, 3, 'F');

        $pdf->SetTextColor($white[0], $white[1], $white[2]);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetXY(60, $badgeY + 3);
        $pdf->Cell(90, 8, 'ESTADO: ' . $icono, 0, 1, 'C');

        $pdf->SetTextColor($gray[0], $gray[1], $gray[2]);
        $pdf->SetFont('Arial', 'I', 7);
        $pdf->SetXY(60, $badgeY + 15);
        $pdf->Cell(90, 4, 'Estado actual de su pedido', 0, 1, 'C');

        // ─── FOOTER ───────────────────────────────────────────────────────────────
        $pdf->SetFillColor($gold[0], $gold[1], $gold[2]);
        $pdf->Rect(0, 265, 210, 2, 'F');

        $pdf->SetFillColor($dark[0], $dark[1], $dark[2]);
        $pdf->Rect(0, 267, 210, 30, 'F');

        $pdf->SetFillColor($goldL[0], $goldL[1], $goldL[2]);
        $pdf->Rect(30, 275, 150, 0.5, 'F');

        $pdf->SetTextColor($goldL[0], $goldL[1], $goldL[2]);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY(0, 269);
        $pdf->Cell(0, 6, 'Restaurante - Gracias por su preferencia', 0, 1, 'C');

        $pdf->SetTextColor($gray[0], $gray[1], $gray[2]);
        $pdf->SetFont('Arial', '', 7);
        $pdf->SetXY(0, 276);
        $pdf->Cell(0, 5, 'Generado el: ' . now()->format('d/m/Y H:i:s') . '   |   Documento oficial', 0, 1, 'C');

        $pdf->Output('D', 'pedido_' . $pedido->id . '.pdf');
    }

    public function destroy($id)
    {
        $pedido = Pedido::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$pedido) {
            return response()->json(['success' => false, 'message' => 'Pedido no encontrado'], 404);
        }

        $pedido->delete();

        return response()->json(['success' => true, 'message' => 'Pedido eliminado correctamente']);
    }

    public function update(Request $request, $id)
    {
        $pedido = Pedido::where('id', $id)
            ->where('user_id', auth()->id())
            ->with(['detalles'])
            ->first();

        if (!$pedido) {
            return response()->json(['success' => false, 'message' => 'Pedido no encontrado'], 404);
        }

        $request->validate([
            'detalles' => 'required|array',
            'detalles.*.plato_id' => 'required|integer',
            'detalles.*.cantidad' => 'required|integer|min:1'
        ]);

        // Eliminar detalles existentes
        $pedido->detalles()->delete();

        // Crear nuevos detalles
        $total = 0;
        foreach ($request->detalles as $detalle) {
            $plato = \App\Models\Plato::find($detalle['plato_id']);
            if ($plato) {
                $subtotal = $plato->precio * $detalle['cantidad'];
                $total += $subtotal;

                $pedido->detalles()->create([
                    'plato_id' => $detalle['plato_id'],
                    'cantidad' => $detalle['cantidad'],
                    'precio_unitario' => $plato->precio,
                    'subtotal' => $subtotal
                ]);
            }
        }

        $pedido->total = $total;
        $pedido->save();

        $user = auth()->user();
        if ($user) {
            \App\Services\MailerService::sendEmail(
                $user->email,
                $user->name,
                'Pedido Modificado - Sabor & Tradición',
                'Cambios en tu pedido',
                "Hola {$user->name},<br><br>Tu pedido con ID #{$pedido->id} ha sido actualizado. El nuevo total a pagar es de <b>$" . number_format($pedido->total, 2) . "</b>."
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pedido actualizado correctamente',
            'pedido' => $pedido
        ]);
    }

    public function show($id)
    {
        $pedido = Pedido::where('id', $id)
            ->where('user_id', auth()->id())
            ->with(['detalles.plato'])
            ->first();

        if (!$pedido) {
            return response()->json(['success' => false, 'message' => 'Pedido no encontrado'], 404);
        }

        $detalles = $pedido->detalles->map(function ($detalle) {
            return [
                'id' => $detalle->id,
                'plato_id' => $detalle->plato_id,
                'plato_nombre' => $detalle->plato->nombre,
                'plato_precio' => $detalle->plato->precio,
                'cantidad' => $detalle->cantidad,
                'subtotal' => $detalle->subtotal
            ];
        });

        return response()->json([
            'success' => true,
            'pedido' => [
                'id' => $pedido->id,
                'total' => $pedido->total,
                'estado' => $pedido->estado,
                'detalles' => $detalles
            ]
        ]);
    }

    public function getPlatos()
    {
        $platos = \App\Models\Plato::where('estado', 'disponible')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'precio']);

        return response()->json([
            'success' => true,
            'platos' => $platos
        ]);
    }

    public function verificarDisponibilidadTiempoReal(Request $request)
    {
        $request->validate([
            'zona' => 'required|string'
        ]);

        $zona = $request->zona;

        // Obtener la fecha y hora proporcionadas o usar la actual
        $fechaActual = $request->fecha ? $request->fecha : now()->format('Y-m-d');
        $horaActual = $request->hora ? \Carbon\Carbon::parse($request->hora) : now();

        // Obtener mesas ocupadas por pedidos sin reserva en tiempo real (última hora desde la hora seleccionada)
        // Como 'sin reserva' es inmediato, evaluamos si la hora actual choca con pedidos sin reserva recientes (1 hora)
        $horaLimitePedidos = $horaActual->copy()->subHour();
        $mesasOcupadasPedidos = Pedido::where('tipo_pedido', 'sin_reserva')
            ->whereDate('created_at', $fechaActual)
            ->whereTime('created_at', '>=', $horaLimitePedidos->format('H:i:s'))
            ->whereTime('created_at', '<=', $horaActual->format('H:i:s'))
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

        // Asumir que una reserva dura 2 horas
        $duracionReserva = 2; // horas

        // Obtener reservas activas para la fecha actual
        $reservasActivas = Reserva::where('fecha', $fechaActual)
            ->where('zona', $zona)
            ->where('estado', '!=', 'cancelada')
            ->get();

        $mesasReservadas = [];
        foreach ($reservasActivas as $reserva) {
            try {
                $horaReserva = \Carbon\Carbon::parse($reserva->hora);
                $horaFinReserva = $horaReserva->copy()->addHours($duracionReserva);

                // Verificar si la hora actual está dentro del rango de la reserva
                if ($horaActual->between($horaReserva, $horaFinReserva)) {
                    $mesasReservadas[] = $reserva->mesa;
                }
            } catch (\Exception $e) {
                // Ignore parse errors for badly formatted times
            }
        }

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
                'disponible' => !in_array((string) $mesa, $mesasOcupadas)
            ];
        }

        return response()->json([
            'success' => true,
            'disponibilidad' => $disponibilidad
        ]);
    }
}
