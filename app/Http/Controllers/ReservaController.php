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

            $user = auth()->user();
            if ($user) {
                \App\Services\MailerService::sendEmail(
                    $user->email,
                    $user->name,
                    'Reserva Confirmada - Sabor & Tradición',
                    '¡Tu reserva ha sido confirmada!',
                    "Hola {$user->name},<br><br>Hemos recibido tu reserva para el día {$reserva->fecha} a las {$reserva->hora} en la zona {$reserva->zona}. Mesas reservadas: {$reserva->mesa}.<br>Código de referencia: <b>{$reserva->codigo_referencia}</b>"
                );
            }

            return response()->json([
                'success' => true,
                'reserva' => $reserva
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $errorMessages = [];
            foreach ($errors as $field => $messages) {
                $errorMessages[] = $field . ': ' . implode(', ', $messages);
            }
            return response()->json([
                'success' => false,
                'message' => 'Error de validación: ' . implode('; ', $errorMessages)
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
                    'id' => $reserva->id,
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

    public function generarPDF($id)
    {
        $reserva = Reserva::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$reserva) {
            return response()->json(['success' => false, 'message' => 'Reserva no encontrada'], 404);
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

        // ─── LOGO con proporciones correctas ──────────────────────────────────────
        $logoPath = public_path('img/LogoRestaurant.png');
        if (file_exists($logoPath)) {
            // Obtener dimensiones reales del logo para no estirarlo
            $logoInfo = getimagesize($logoPath);
            if ($logoInfo) {
                $logoW_px = $logoInfo[0];
                $logoH_px = $logoInfo[1];
                $ratio = $logoH_px / $logoW_px;

                // Ancho fijo de 22mm, alto proporcional
                $logoW = 38;
                $logoH = $logoW * $ratio;
                $logoX = (210 - $logoW) / 2;   // centrado
                $logoY = max(3, (58 - $logoH) / 2);   // centrado verticalmente en el header
                $pdf->Image($logoPath, $logoX, $logoY, $logoW, $logoH);
            }
        }

        // ─── Título ───────────────────────────────────────────────────────────────
        $pdf->SetY(43);
        $pdf->SetTextColor($white[0], $white[1], $white[2]);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 8, 'COMPROBANTE DE RESERVA', 0, 1, 'C');

        // Línea dorada decorativa
        $pdf->SetFillColor($goldL[0], $goldL[1], $goldL[2]);
        $pdf->Rect(0, 60, 210, 2.5, 'F');

        // ─── FONDO CUERPO ─────────────────────────────────────────────────────────
        $pdf->SetFillColor($lightBg[0], $lightBg[1], $lightBg[2]);
        $pdf->Rect(0, 62, 210, 206, 'F');

        // Código de reporte
        $pdf->SetY(66);
        $pdf->SetTextColor($gray[0], $gray[1], $gray[2]);
        $pdf->SetFont('Arial', 'I', 8);
        $pdf->Cell(0, 5, 'Reporte: ' . $codigoReporte, 0, 1, 'C');

        // ─── TARJETA PRINCIPAL ────────────────────────────────────────────────────
        $cardX = 20;
        $cardY = 76;
        $cardW = 170;

        // SIN Estado en la tabla — se muestra solo en el badge
        $info = [
            ['Codigo de Reserva', $reserva->codigo_referencia],
            ['Fecha', $reserva->fecha],
            ['Hora', $reserva->hora],
            ['Mesa(s)', $reserva->mesa],
            ['Personas', $reserva->personas],
            ['Zona', ucfirst($reserva->zona)],
        ];
        if ($reserva->notas) {
            $info[] = ['Notas', $reserva->notas];
        }

        $rowH = 14;
        $cardH = count($info) * $rowH + 14;

        // Sombra
        $pdf->SetFillColor(195, 182, 148);
        $pdf->Rect($cardX + 3, $cardY + 3, $cardW, $cardH, 'F');
        // Fondo blanco
        $pdf->SetFillColor(255, 255, 255);
        $pdf->Rect($cardX, $cardY, $cardW, $cardH, 'F');
        // Borde dorado
        $pdf->SetDrawColor($gold[0], $gold[1], $gold[2]);
        $pdf->SetLineWidth(0.9);
        $pdf->Rect($cardX, $cardY, $cardW, $cardH, 'D');
        $pdf->SetLineWidth(0.2);

        // Barra título tarjeta
        $pdf->SetFillColor($gold[0], $gold[1], $gold[2]);
        $pdf->Rect($cardX, $cardY, $cardW, 13, 'F');

        // Línea dorada clara dentro de la barra (detalle)
        $pdf->SetFillColor($goldL[0], $goldL[1], $goldL[2]);
        $pdf->Rect($cardX, $cardY + 11.5, $cardW, 1, 'F');

        $pdf->SetTextColor($white[0], $white[1], $white[2]);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetXY($cardX, $cardY);
        $pdf->Cell($cardW, 13, 'DETALLE DE LA RESERVA', 0, 1, 'C');

        // ─── FILAS ────────────────────────────────────────────────────────────────
        $labelW = 58;
        $startY = $cardY + 14;

        foreach ($info as $i => $row) {
            $y = $startY + $i * $rowH;

            // Fondo alterno
            $pdf->SetFillColor(...($i % 2 === 0 ? $cream : $cream2));
            $pdf->Rect($cardX + 1, $y, $cardW - 2, $rowH - 1, 'F');

            // Separador
            $pdf->SetDrawColor($lineSep[0], $lineSep[1], $lineSep[2]);
            $pdf->SetLineWidth(0.15);
            $pdf->Line($cardX + 1, $y + $rowH - 1, $cardX + $cardW - 1, $y + $rowH - 1);

            // Línea vertical separando label/valor
            $pdf->SetDrawColor($goldL[0], $goldL[1], $goldL[2]);
            $pdf->Line($cardX + $labelW + 6, $y + 2, $cardX + $labelW + 6, $y + $rowH - 3);

            // Label
            $pdf->SetTextColor($gold[0], $gold[1], $gold[2]);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY($cardX + 6, $y + 4);
            $pdf->Cell($labelW, 5, $row[0] . ':', 0, 0, 'L');

            // Valor
            $pdf->SetTextColor($dark[0], $dark[1], $dark[2]);
            $pdf->SetFont('Arial', '', 10);
            $pdf->SetXY($cardX + $labelW + 10, $y + 4);
            $pdf->Cell($cardW - $labelW - 14, 5, $row[1], 0, 0, 'L');
        }

        // ─── BADGE ESTADO (único, bonito) ─────────────────────────────────────────
        $badgeY = $cardY + $cardH + 12;
        $estado = strtolower($reserva->estado);

        if ($estado === 'confirmada') {
            $badgeColor = [34, 120, 34];
            $badgeColorL = [180, 230, 180];
            $icono = 'CONFIRMADA';
        } elseif ($estado === 'cancelada') {
            $badgeColor = [160, 35, 35];
            $badgeColorL = [230, 180, 180];
            $icono = 'CANCELADA';
        } else {
            $badgeColor = $gold;
            $badgeColorL = $goldL;
            $icono = 'PENDIENTE';
        }

        // Sombra del badge
        $pdf->SetFillColor(150, 140, 110);
        $pdf->Rect(63, $badgeY + 2, 86, 14, 'F');

        // Fondo badge
        $pdf->SetFillColor($badgeColor[0], $badgeColor[1], $badgeColor[2]);
        $pdf->Rect(60, $badgeY, 90, 14, 'F');

        // Franja clara en la parte superior del badge (efecto luz)
        $pdf->SetFillColor($badgeColorL[0], $badgeColorL[1], $badgeColorL[2]);
        $pdf->Rect(60, $badgeY, 90, 3, 'F');

        // Texto badge
        $pdf->SetTextColor($white[0], $white[1], $white[2]);
        $pdf->SetFont('Arial', 'B', 11);
        $pdf->SetXY(60, $badgeY + 3);
        $pdf->Cell(90, 8, 'ESTADO: ' . $icono, 0, 1, 'C');

        // Etiqueta pequeña bajo el badge
        $pdf->SetTextColor($gray[0], $gray[1], $gray[2]);
        $pdf->SetFont('Arial', 'I', 7);
        $pdf->SetXY(60, $badgeY + 15);
        $pdf->Cell(90, 4, 'Estado actual de su reserva', 0, 1, 'C');

        // ─── FOOTER ───────────────────────────────────────────────────────────────
        // Franja dorada encima del footer
        $pdf->SetFillColor($gold[0], $gold[1], $gold[2]);
        $pdf->Rect(0, 265, 210, 2, 'F');

        // Fondo footer oscuro
        $pdf->SetFillColor($dark[0], $dark[1], $dark[2]);
        $pdf->Rect(0, 267, 210, 30, 'F');

        // Línea dorada clara dentro del footer
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

        $pdf->Output('D', 'reserva_' . $reserva->codigo_referencia . '.pdf');
    }

    public function destroy($id)
    {
        $reserva = Reserva::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$reserva) {
            return response()->json(['success' => false, 'message' => 'Reserva no encontrada'], 404);
        }

        $reserva->delete();

        return response()->json(['success' => true, 'message' => 'Reserva eliminada correctamente']);
    }

    public function update(Request $request, $id)
    {
        $reserva = Reserva::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$reserva) {
            return response()->json(['success' => false, 'message' => 'Reserva no encontrada'], 404);
        }

        $request->validate([
            'fecha' => 'required|date',
            'hora' => 'required',
            'zona' => 'required|string',
            'personas' => 'required|integer|min:1',
            'mesas' => 'required|array',
            'notas' => 'nullable|string'
        ]);

        $reserva->fecha = $request->fecha;
        $reserva->hora = $request->hora;
        $reserva->zona = $request->zona;
        $reserva->personas = $request->personas;
        $reserva->mesa = implode(',', $request->mesas);
        $reserva->notas = $request->notas ?? '';
        $reserva->save();

        $user = auth()->user();
        if ($user) {
            \App\Services\MailerService::sendEmail(
                $user->email,
                $user->name,
                'Reserva Modificada - Sabor & Tradición',
                'Cambios en tu reserva',
                "Hola {$user->name},<br><br>Tu reserva con código <b>{$reserva->codigo_referencia}</b> ha sido actualizada. Nueva fecha: {$reserva->fecha} a las {$reserva->hora}."
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Reserva actualizada correctamente',
            'reserva' => $reserva
        ]);
    }

    public function show($id)
    {
        $reserva = Reserva::where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$reserva) {
            return response()->json(['success' => false, 'message' => 'Reserva no encontrada'], 404);
        }

        return response()->json([
            'success' => true,
            'reserva' => [
                'id' => $reserva->id,
                'fecha' => $reserva->fecha,
                'hora' => $reserva->hora,
                'zona' => $reserva->zona,
                'personas' => $reserva->personas,
                'mesas' => explode(',', $reserva->mesa),
                'notas' => $reserva->notas,
                'codigo_referencia' => $reserva->codigo_referencia
            ]
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
                'disponible' => !in_array((string) $mesa, $mesasReservadas)
            ];
        }

        return response()->json([
            'success' => true,
            'disponibilidad' => $disponibilidad
        ]);
    }
}
