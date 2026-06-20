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
                    'fecha_creacion' => $reserva->created_at->format('d/m/Y H:i'),
                    'fecha' => $reserva->created_at->format('d/m/Y'),
                    'fecha_reserva' => \Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y'),
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

        $user = auth()->user();

        // ─── Helper: convertir UTF-8 a Latin1 para FPDF ───────────────────────────
        $enc = function ($str) {
            return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', (string) $str);
        };

        $codigoReporte = 'RPT-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

        $pdf = new \FPDF();
        $pdf->SetMargins(15, 10, 15);
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 20);

        // ─── Paleta (Igual que en la web) ─────────────────────────────────────────
        $gold    = [194, 149, 69]; // Color primario web
        $goldD   = [140, 108, 20];
        $goldL   = [240, 220, 140];
        $goldXL  = [252, 246, 220];
        $dark    = [28, 28, 35];
        $charcoal= [55, 55, 65];
        $white   = [255, 255, 255];
        $offWhite= [250, 249, 245];
        $gray    = [120, 120, 130];
        $grayL   = [200, 200, 205];
        $blueD   = [30, 60, 120];
        $blueM   = [50, 90, 160];
        $blueL   = [220, 230, 248];
        $green   = [34, 110, 60];
        $greenL  = [210, 240, 220];
        $amber   = [170, 100, 10];
        $amberL  = [255, 240, 200];

        $pageW = 210;
        $margin = 15;
        $contentW = $pageW - $margin * 2;   // 180mm

        // ══════════════════════════════════════════════════════════════════════════
        // HEADER: banda degradada oscura (dorado profundo → negro)
        // ══════════════════════════════════════════════════════════════════════════
        for ($i = 0; $i < 52; $i++) {
            $t = $i / 51;
            $r = (int)(28  + $t * (28 - 28));
            $g = (int)(28  + $t * (28 - 28));
            $b = (int)(35  + $t * (35 - 35));
            $rr = (int)(140 * (1 - $t) + 28 * $t);
            $gg = (int)(108 * (1 - $t) + 28 * $t);
            $bb = (int)(20  * (1 - $t) + 35 * $t);
            $pdf->SetFillColor($rr, $gg, $bb);
            $pdf->Rect(0, $i, $pageW, 1, 'F');
        }

        for ($i = 52; $i < 56; $i++) {
            $t = ($i - 52) / 3;
            $r = (int)(255 * (1 - $t) + $goldL[0] * $t);
            $g = (int)(220 * (1 - $t) + $goldL[1] * $t);
            $b = (int)(80  * (1 - $t) + $goldL[2] * $t);
            $pdf->SetFillColor($r, $g, $b);
            $pdf->Rect(0, $i, $pageW, 1, 'F');
        }

        $pdf->SetFillColor($offWhite[0], $offWhite[1], $offWhite[2]);
        $pdf->Rect(0, 56, $pageW, 250, 'F');

        // LOGO
        $logoPath = public_path('img/LogoRestaurant.png');
        if (file_exists($logoPath)) {
            $logoInfo = getimagesize($logoPath);
            if ($logoInfo) {
                $ratio = $logoInfo[1] / $logoInfo[0];
                $logoW = 30;
                $logoH = $logoW * $ratio;
                $logoX = $margin;
                $logoY = max(4, (50 - $logoH) / 2);
                $pdf->Image($logoPath, $logoX, $logoY, $logoW, $logoH);
            }
        }

        $pdf->SetXY($margin + 34, 10);
        $pdf->SetTextColor($goldL[0], $goldL[1], $goldL[2]);
        $pdf->SetFont('Times', 'BI', 22);
        $pdf->Cell(0, 10, $enc('Sabor & Tradición'), 0, 1, 'L');

        $pdf->SetXY($margin + 34, 21);
        $pdf->SetTextColor($grayL[0], $grayL[1], $grayL[2]);
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 5, $enc('Restaurante de alta cocina  |  Comprobante oficial de reserva'), 0, 1, 'L');

        $pdf->SetXY(0, 9);
        $pdf->SetTextColor($goldXL[0], $goldXL[1], $goldXL[2]);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($pageW - $margin, 6, $enc('RESERVA  #' . $reserva->id), 0, 1, 'R');

        $pdf->SetXY(0, 16);
        $pdf->SetTextColor($grayL[0], $grayL[1], $grayL[2]);
        $pdf->SetFont('Arial', '', 7.5);
        $pdf->Cell($pageW - $margin, 5, $enc('Reporte: ' . $codigoReporte), 0, 1, 'R');

        $pdf->SetXY(0, 23);
        $pdf->SetFont('Arial', '', 7.5);
        $pdf->Cell($pageW - $margin, 5, $enc('Emitido: ' . now()->format('d/m/Y H:i:s')), 0, 1, 'R');

        // DATOS DEL CLIENTE
        $secY = 63;
        $pdf->SetFillColor($blueD[0], $blueD[1], $blueD[2]);
        $pdf->Rect($margin, $secY, $contentW, 9, 'F');
        $pdf->SetFillColor($goldL[0], $goldL[1], $goldL[2]);
        $pdf->Rect($margin, $secY, 3, 9, 'F');

        $pdf->SetTextColor($white[0], $white[1], $white[2]);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY($margin + 7, $secY + 1.5);
        $pdf->Cell($contentW - 7, 6, $enc('DATOS DEL CLIENTE'), 0, 1, 'L');

        $bodyH1 = 28;
        $pdf->SetFillColor($blueL[0], $blueL[1], $blueL[2]);
        $pdf->Rect($margin, $secY + 9, $contentW, $bodyH1, 'F');
        $pdf->SetFillColor($blueM[0], $blueM[1], $blueM[2]);
        $pdf->Rect($margin, $secY + 9, 0.8, $bodyH1, 'F');

        $colWUser = $contentW / 3;
        $userData = [
            ['NOMBRE', $enc(trim(($user->name ?? '') . ' ' . ($user->lastname ?? '')))],
            ['DOCUMENTO', $enc($user->numero_documento ?? 'No registrado')],
            ['CORREO', $enc($user->email ?? 'No registrado')],
        ];

        foreach ($userData as $idx => $ud) {
            $ux = $margin + $idx * $colWUser + 5;
            $pdf->SetTextColor($blueM[0], $blueM[1], $blueM[2]);
            $pdf->SetFont('Arial', 'B', 6.5);
            $pdf->SetXY($ux, $secY + 12);
            $pdf->Cell($colWUser - 8, 4, $ud[0], 0, 1, 'L');
            $pdf->SetTextColor($dark[0], $dark[1], $dark[2]);
            $pdf->SetFont('Arial', '', 9);
            $pdf->SetXY($ux, $secY + 17);
            $pdf->Cell($colWUser - 8, 6, $ud[1], 0, 1, 'L');
            if ($idx < 2) {
                $pdf->SetDrawColor($blueM[0], $blueM[1], $blueM[2]);
                $pdf->SetLineWidth(0.3);
                $pdf->Line($margin + ($idx + 1) * $colWUser, $secY + 11, $margin + ($idx + 1) * $colWUser, $secY + 9 + $bodyH1 - 2);
            }
        }

        // INFORMACION DE LA RESERVA
        $secY2 = $secY + 9 + $bodyH1 + 6;
        $pdf->SetFillColor($goldD[0], $goldD[1], $goldD[2]);
        $pdf->Rect($margin, $secY2, $contentW, 9, 'F');
        $pdf->SetFillColor($white[0], $white[1], $white[2]);
        $pdf->Rect($margin, $secY2, 3, 9, 'F');

        $pdf->SetTextColor($white[0], $white[1], $white[2]);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY($margin + 7, $secY2 + 1.5);
        $pdf->Cell($contentW - 7, 6, $enc('DETALLE DE LA RESERVA'), 0, 1, 'L');

        $bodyH2 = 42;
        $pdf->SetFillColor($goldXL[0], $goldXL[1], $goldXL[2]);
        $pdf->Rect($margin, $secY2 + 9, $contentW, $bodyH2, 'F');
        $pdf->SetFillColor($gold[0], $gold[1], $gold[2]);
        $pdf->Rect($margin, $secY2 + 9, 0.8, $bodyH2, 'F');

        $infoGrid = [
            ['CODIGO',       $enc($reserva->codigo_referencia)],
            ['FECHA',        $enc(\Carbon\Carbon::parse($reserva->fecha)->format('d/m/Y'))],
            ['HORA',         $enc($reserva->hora)],
            ['ZONA',         $enc(ucfirst($reserva->zona))],
            ['MESA(S)',      $enc($reserva->mesa)],
            ['PERSONAS',     $enc($reserva->personas . ' persona(s)')],
        ];

        $colWInfo = $contentW / 3;
        foreach ($infoGrid as $idx => $ig) {
            $col = $idx % 3;
            $fila = (int)($idx / 3);
            $ix = $margin + $col * $colWInfo + 5;
            $iy = $secY2 + 12 + $fila * 20;

            $pdf->SetTextColor($goldD[0], $goldD[1], $goldD[2]);
            $pdf->SetFont('Arial', 'B', 6.5);
            $pdf->SetXY($ix, $iy);
            $pdf->Cell($colWInfo - 8, 4, $ig[0], 0, 1, 'L');

            $pdf->SetTextColor($dark[0], $dark[1], $dark[2]);
            $pdf->SetFont('Arial', 'B', 9.5);
            $pdf->SetXY($ix, $iy + 4);
            $pdf->Cell($colWInfo - 8, 6, $ig[1], 0, 1, 'L');

            if ($col < 2) {
                $pdf->SetDrawColor($gold[0], $gold[1], $gold[2]);
                $pdf->SetLineWidth(0.3);
                $pdf->Line($margin + ($col + 1) * $colWInfo, $secY2 + 10, $margin + ($col + 1) * $colWInfo, $secY2 + 9 + $bodyH2 - 1);
            }
            if ($fila === 0 && $col === 0) {
                $pdf->SetDrawColor($goldL[0], $goldL[1], $goldL[2]);
                $pdf->SetLineWidth(0.3);
                $pdf->Line($margin + 2, $secY2 + 9 + 20, $margin + $contentW - 2, $secY2 + 9 + 20);
            }
        }

        $secY3 = $secY2 + 9 + $bodyH2 + 7;

        // NOTAS ADICIONALES (Opcional)
        if (!empty($reserva->notas)) {
            $pdf->SetFillColor($charcoal[0], $charcoal[1], $charcoal[2]);
            $pdf->Rect($margin, $secY3, $contentW, 8, 'F');
            $pdf->SetFillColor($gold[0], $gold[1], $gold[2]);
            $pdf->Rect($margin, $secY3, 3, 8, 'F');

            $pdf->SetTextColor($goldL[0], $goldL[1], $goldL[2]);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetXY($margin + 7, $secY3 + 1.5);
            $pdf->Cell($contentW - 7, 5, $enc('NOTAS ADICIONALES'), 0, 1, 'L');

            $pdf->SetFillColor(242, 240, 233);
            $pdf->Rect($margin, $secY3 + 8, $contentW, 16, 'F');
            $pdf->SetTextColor($dark[0], $dark[1], $dark[2]);
            $pdf->SetFont('Arial', 'I', 9);
            $pdf->SetXY($margin + 5, $secY3 + 10);
            $pdf->MultiCell($contentW - 10, 5, $enc($reserva->notas), 0, 'L');

            $secY3 += 32;
        }

        // BADGE DE ESTADO
        $badgeY = $secY3 + 10;
        $estado = strtolower($reserva->estado);

        $stateMap = [
            'confirmada' => [[34, 120, 60],  [210, 245, 220], 'CONFIRMADA'],
            'cancelada'  => [[160, 35, 35],  [245, 210, 210], 'CANCELADA'],
        ];
        [$badgeColor, $badgeColorL, $badgeText] = $stateMap[$estado] ?? [$amber, $amberL, 'PENDIENTE'];

        $bw = 70; $bh = 12;
        $bx = ($pageW - $bw) / 2;

        $pdf->SetFillColor(180, 170, 150);
        $pdf->Rect($bx + 2, $badgeY + 2, $bw, $bh, 'F');
        $pdf->SetFillColor($badgeColor[0], $badgeColor[1], $badgeColor[2]);
        $pdf->Rect($bx, $badgeY, $bw, $bh, 'F');
        $pdf->SetFillColor($badgeColorL[0], $badgeColorL[1], $badgeColorL[2]);
        $pdf->Rect($bx, $badgeY, $bw, 3, 'F');
        $pdf->SetTextColor($white[0], $white[1], $white[2]);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY($bx, $badgeY + 2.5);
        $pdf->Cell($bw, 7, $enc('ESTADO: ' . $badgeText), 0, 0, 'C');

        $pdf->SetTextColor($gray[0], $gray[1], $gray[2]);
        $pdf->SetFont('Arial', 'I', 7);
        $pdf->SetXY(0, $badgeY + $bh + 4);
        $pdf->Cell($pageW, 4, $enc('Estado actual de su reserva'), 0, 0, 'C');

        // FOOTER
        $pdf->SetAutoPageBreak(false); // Evitar salto de página al imprimir el footer
        $footerY = $pdf->GetPageHeight() - 22;
        $pdf->SetDrawColor($gold[0], $gold[1], $gold[2]);
        $pdf->SetLineWidth(0.6);
        $pdf->Line($margin, $footerY, $pageW - $margin, $footerY);
        $pdf->SetFillColor($dark[0], $dark[1], $dark[2]);
        $pdf->Rect(0, $footerY + 1, $pageW, 25, 'F');

        $pdf->SetTextColor($goldL[0], $goldL[1], $goldL[2]);
        $pdf->SetFont('Times', 'I', 10);
        $pdf->SetXY(0, $footerY + 5);
        $pdf->Cell($pageW, 6, $enc('Sabor & Tradición  —  Gracias por su preferencia'), 0, 1, 'C');

        $pdf->SetTextColor($gray[0], $gray[1], $gray[2]);
        $pdf->SetFont('Arial', '', 6.5);
        $pdf->SetXY(0, $footerY + 12);
        $pdf->Cell($pageW, 4, $enc('Generado el: ' . now()->format('d/m/Y H:i:s') . '   |   Documento oficial  |  ' . $codigoReporte), 0, 1, 'C');

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
