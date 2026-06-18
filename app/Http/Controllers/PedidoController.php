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

        // ─── Paleta ───────────────────────────────────────────────────────────────
        $gold    = [194, 154, 38];
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
            // degradado lateral gold → dark
            $rr = (int)(140 * (1 - $t) + 28 * $t);
            $gg = (int)(108 * (1 - $t) + 28 * $t);
            $bb = (int)(20  * (1 - $t) + 35 * $t);
            $pdf->SetFillColor($rr, $gg, $bb);
            $pdf->Rect(0, $i, $pageW, 1, 'F');
        }

        // Línea dorada brillante debajo del header
        for ($i = 52; $i < 56; $i++) {
            $t = ($i - 52) / 3;
            $r = (int)(255 * (1 - $t) + $goldL[0] * $t);
            $g = (int)(220 * (1 - $t) + $goldL[1] * $t);
            $b = (int)(80  * (1 - $t) + $goldL[2] * $t);
            $pdf->SetFillColor($r, $g, $b);
            $pdf->Rect(0, $i, $pageW, 1, 'F');
        }

        // Fondo cuerpo crema suave
        $pdf->SetFillColor($offWhite[0], $offWhite[1], $offWhite[2]);
        $pdf->Rect(0, 56, $pageW, 250, 'F');

        // ─── LOGO ────────────────────────────────────────────────────────────────
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

        // ─── Nombre del restaurante + subtítulo ──────────────────────────────────
        $pdf->SetXY($margin + 34, 10);
        $pdf->SetTextColor($goldL[0], $goldL[1], $goldL[2]);
        $pdf->SetFont('Arial', 'B', 18);
        $pdf->Cell(0, 10, $enc('Sabor & Tradicion'), 0, 1, 'L');

        $pdf->SetXY($margin + 34, 21);
        $pdf->SetTextColor($grayL[0], $grayL[1], $grayL[2]);
        $pdf->SetFont('Arial', '', 8);
        $pdf->Cell(0, 5, $enc('Restaurante de alta cocina  |  Comprobante oficial de pedido'), 0, 1, 'L');

        // Número de pedido (lado derecho del header)
        $pdf->SetXY(0, 9);
        $pdf->SetTextColor($goldXL[0], $goldXL[1], $goldXL[2]);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell($pageW - $margin, 6, $enc('PEDIDO  #' . $pedido->id), 0, 1, 'R');

        $pdf->SetXY(0, 16);
        $pdf->SetTextColor($grayL[0], $grayL[1], $grayL[2]);
        $pdf->SetFont('Arial', '', 7.5);
        $pdf->Cell($pageW - $margin, 5, $enc('Reporte: ' . $codigoReporte), 0, 1, 'R');

        $pdf->SetXY(0, 23);
        $pdf->SetFont('Arial', '', 7.5);
        $pdf->Cell($pageW - $margin, 5,
            $enc('Emitido: ' . now()->format('d/m/Y H:i:s')), 0, 1, 'R');

        // ══════════════════════════════════════════════════════════════════════════
        // SECCIÓN 1 — DATOS DEL CLIENTE  (fondo azul suave)
        // ══════════════════════════════════════════════════════════════════════════
        $secY = 63;

        // Cabecera sección
        $pdf->SetFillColor($blueD[0], $blueD[1], $blueD[2]);
        $pdf->Rect($margin, $secY, $contentW, 9, 'F');
        // Acento izquierdo
        $pdf->SetFillColor($goldL[0], $goldL[1], $goldL[2]);
        $pdf->Rect($margin, $secY, 3, 9, 'F');

        $pdf->SetTextColor($white[0], $white[1], $white[2]);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY($margin + 7, $secY + 1.5);
        $pdf->Cell($contentW - 7, 6, $enc('DATOS DEL CLIENTE'), 0, 1, 'L');

        // Cuerpo sección (fondo azul muy claro)
        $bodyH1 = 28;
        $pdf->SetFillColor($blueL[0], $blueL[1], $blueL[2]);
        $pdf->Rect($margin, $secY + 9, $contentW, $bodyH1, 'F');

        // Borde sutil izquierdo
        $pdf->SetFillColor($blueM[0], $blueM[1], $blueM[2]);
        $pdf->Rect($margin, $secY + 9, 0.8, $bodyH1, 'F');

        // Tres datos en fila horizontal
        $colWUser = $contentW / 3;
        $userData = [
            ['NOMBRE', $enc(trim(($user->name ?? '') . ' ' . ($user->lastname ?? '')))],
            ['DOCUMENTO', $enc($user->numero_documento ?? 'No registrado')],
            ['CORREO', $enc($user->email ?? 'No registrado')],
        ];

        foreach ($userData as $idx => $ud) {
            $ux = $margin + $idx * $colWUser + 5;
            // Etiqueta
            $pdf->SetTextColor($blueM[0], $blueM[1], $blueM[2]);
            $pdf->SetFont('Arial', 'B', 6.5);
            $pdf->SetXY($ux, $secY + 12);
            $pdf->Cell($colWUser - 8, 4, $ud[0], 0, 1, 'L');
            // Valor
            $pdf->SetTextColor($dark[0], $dark[1], $dark[2]);
            $pdf->SetFont('Arial', '', 9);
            $pdf->SetXY($ux, $secY + 17);
            $pdf->Cell($colWUser - 8, 6, $ud[1], 0, 1, 'L');
            // Divisor vertical (excepto último)
            if ($idx < 2) {
                $pdf->SetDrawColor($blueM[0], $blueM[1], $blueM[2]);
                $pdf->SetLineWidth(0.3);
                $pdf->Line($margin + ($idx + 1) * $colWUser, $secY + 11,
                            $margin + ($idx + 1) * $colWUser, $secY + 9 + $bodyH1 - 2);
            }
        }

        // ══════════════════════════════════════════════════════════════════════════
        // SECCIÓN 2 — INFORMACIÓN DEL PEDIDO  (fondo dorado suave)
        // ══════════════════════════════════════════════════════════════════════════
        $secY2 = $secY + 9 + $bodyH1 + 6;

        // Cabecera sección
        $pdf->SetFillColor($goldD[0], $goldD[1], $goldD[2]);
        $pdf->Rect($margin, $secY2, $contentW, 9, 'F');
        $pdf->SetFillColor($white[0], $white[1], $white[2]);
        $pdf->Rect($margin, $secY2, 3, 9, 'F');

        $pdf->SetTextColor($white[0], $white[1], $white[2]);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY($margin + 7, $secY2 + 1.5);
        $pdf->Cell($contentW - 7, 6, $enc('INFORMACION DEL PEDIDO'), 0, 1, 'L');

        // Tipo de pedido → color verde o ámbar
        $esCon = $pedido->tipo_pedido === 'con_reserva';
        $tipoBadgeLabel = $esCon ? $enc('CON RESERVA') : $enc('SIN RESERVA');
        $tipoBgColor  = $esCon ? $green  : $amber;
        $tipoBgColorL = $esCon ? $greenL : $amberL;

        // Cuerpo: grid 2x3 (6 datos)
        $bodyH2 = 42;
        $pdf->SetFillColor($goldXL[0], $goldXL[1], $goldXL[2]);
        $pdf->Rect($margin, $secY2 + 9, $contentW, $bodyH2, 'F');
        $pdf->SetFillColor($gold[0], $gold[1], $gold[2]);
        $pdf->Rect($margin, $secY2 + 9, 0.8, $bodyH2, 'F');

        $infoGrid = [
            ['N° PEDIDO',    '#' . $pedido->id],
            ['MESA',         $enc($pedido->mesa)],
            ['ZONA',         $enc(ucfirst($pedido->zona))],
            ['FECHA Y HORA', $enc($pedido->created_at->format('d/m/Y  H:i'))],
            ['ESTADO',       $enc(strtoupper($pedido->estado))],
            ['TIPO',         $tipoBadgeLabel],
        ];

        $colWInfo = $contentW / 3;
        foreach ($infoGrid as $idx => $ig) {
            $col = $idx % 3;
            $fila = (int)($idx / 3);
            $ix = $margin + $col * $colWInfo + 5;
            $iy = $secY2 + 12 + $fila * 20;

            // Etiqueta
            $pdf->SetTextColor($goldD[0], $goldD[1], $goldD[2]);
            $pdf->SetFont('Arial', 'B', 6.5);
            $pdf->SetXY($ix, $iy);
            $pdf->Cell($colWInfo - 8, 4, $ig[0], 0, 1, 'L');

            // Valor — "TIPO" lo pintamos con badge de color
            if ($ig[0] === 'TIPO') {
                $bx = $ix;
                $by = $iy + 4;
                $bw = min($colWInfo - 10, 38);
                $bh = 7;
                $pdf->SetFillColor($tipoBgColor[0], $tipoBgColor[1], $tipoBgColor[2]);
                $pdf->Rect($bx, $by, $bw, $bh, 'F');
                $pdf->SetFillColor($tipoBgColorL[0], $tipoBgColorL[1], $tipoBgColorL[2]);
                $pdf->Rect($bx, $by, $bw, 2, 'F');
                $pdf->SetTextColor($white[0], $white[1], $white[2]);
                $pdf->SetFont('Arial', 'B', 7);
                $pdf->SetXY($bx, $by + 1);
                $pdf->Cell($bw, 5, $ig[1], 0, 0, 'C');
            } else {
                $pdf->SetTextColor($dark[0], $dark[1], $dark[2]);
                $pdf->SetFont('Arial', 'B', 9.5);
                $pdf->SetXY($ix, $iy + 4);
                $pdf->Cell($colWInfo - 8, 6, $ig[1], 0, 1, 'L');
            }

            // Divisores verticales
            if ($col < 2) {
                $pdf->SetDrawColor($gold[0], $gold[1], $gold[2]);
                $pdf->SetLineWidth(0.3);
                $pdf->Line($margin + ($col + 1) * $colWInfo, $secY2 + 10,
                            $margin + ($col + 1) * $colWInfo, $secY2 + 9 + $bodyH2 - 1);
            }
            // Separador horizontal entre filas
            if ($fila === 0 && $col === 0) {
                $pdf->SetDrawColor($goldL[0], $goldL[1], $goldL[2]);
                $pdf->SetLineWidth(0.3);
                $pdf->Line($margin + 2, $secY2 + 9 + 20,
                            $margin + $contentW - 2, $secY2 + 9 + 20);
            }
        }

        // ══════════════════════════════════════════════════════════════════════════
        // SECCIÓN 3 — TABLA DE PRODUCTOS
        // ══════════════════════════════════════════════════════════════════════════
        $secY3 = $secY2 + 9 + $bodyH2 + 7;

        // Cabecera sección
        $pdf->SetFillColor($dark[0], $dark[1], $dark[2]);
        $pdf->Rect($margin, $secY3, $contentW, 9, 'F');
        $pdf->SetFillColor($gold[0], $gold[1], $gold[2]);
        $pdf->Rect($margin, $secY3, 3, 9, 'F');

        $pdf->SetTextColor($goldL[0], $goldL[1], $goldL[2]);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY($margin + 7, $secY3 + 1.5);
        $pdf->Cell($contentW - 7, 6, $enc('DETALLE DE PRODUCTOS'), 0, 1, 'L');

        // ─── Cabecera de columnas ────────────────────────────────────────────────
        // Anchos columnas (sobre contentW=180): Nro|Producto|Cant|P.Unit|Total
        $cNro    = 10;
        $cProd   = 68;
        $cCant   = 18;
        $cUnit   = 30;
        $cTot    = 34;
        // Total usado: 10+68+18+30+34 = 160  → margen interior 20mm sobrante para padding
        $innerX  = $margin + 5;

        $pdf->SetFillColor($charcoal[0], $charcoal[1], $charcoal[2]);
        $pdf->Rect($margin, $secY3 + 9, $contentW, 8, 'F');

        $pdf->SetTextColor($goldL[0], $goldL[1], $goldL[2]);
        $pdf->SetFont('Arial', 'B', 7.5);
        $hY = $secY3 + 11;
        $pdf->SetXY($innerX, $hY);
        $pdf->Cell($cNro,  5, '#',         0, 0, 'C');
        $pdf->Cell($cProd, 5, 'PRODUCTO',  0, 0, 'L');
        $pdf->Cell($cCant, 5, 'CANT.',     0, 0, 'C');
        $pdf->Cell($cUnit, 5, 'P. UNIT.',  0, 0, 'C');
        $pdf->Cell($cTot,  5, 'TOTAL',     0, 0, 'R');

        // ─── Filas de productos ──────────────────────────────────────────────────
        $rowStartY = $secY3 + 17;
        $rowItemH  = 18;   // alto de cada fila (nombre + descripción)

        foreach ($pedido->detalles as $j => $detalle) {
            $nombre = $enc($detalle->plato->nombre ?? '');
            $desc   = $enc($detalle->plato->descripcion ?? '');
            // Truncar descripción a 55 chars
            if (mb_strlen($detalle->plato->descripcion ?? '', 'UTF-8') > 55) {
                $desc = $enc(mb_substr($detalle->plato->descripcion, 0, 53, 'UTF-8') . '..');
            }

            $ry = $rowStartY + $j * $rowItemH;

            // Fondo alterno suave
            if ($j % 2 === 0) {
                $pdf->SetFillColor($offWhite[0], $offWhite[1], $offWhite[2]);
            } else {
                $pdf->SetFillColor(242, 240, 233);
            }
            $pdf->Rect($margin + 1, $ry, $contentW - 2, $rowItemH - 1, 'F');

            // Acento lateral izquierdo alterno (gold / transparente)
            if ($j % 2 === 0) {
                $pdf->SetFillColor($goldL[0], $goldL[1], $goldL[2]);
                $pdf->Rect($margin + 1, $ry, 2, $rowItemH - 1, 'F');
            }

            // Número de fila
            $pdf->SetTextColor($gray[0], $gray[1], $gray[2]);
            $pdf->SetFont('Arial', '', 7);
            $pdf->SetXY($innerX, $ry + 5);
            $pdf->Cell($cNro, 5, $j + 1, 0, 0, 'C');

            // Nombre del producto (negrita, oscuro)
            $pdf->SetTextColor($dark[0], $dark[1], $dark[2]);
            $pdf->SetFont('Arial', 'B', 8.5);
            $pdf->SetXY($innerX + $cNro, $ry + 3);
            $pdf->Cell($cProd, 5, $nombre, 0, 1, 'L');

            // Descripción (gris, cursiva, debajo del nombre)
            $pdf->SetTextColor($gray[0], $gray[1], $gray[2]);
            $pdf->SetFont('Arial', 'I', 7);
            $pdf->SetXY($innerX + $cNro, $ry + 9);
            $pdf->Cell($cProd, 4, $desc, 0, 1, 'L');

            // Cantidad
            $pdf->SetTextColor($dark[0], $dark[1], $dark[2]);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY($innerX + $cNro + $cProd, $ry + 5);
            $pdf->Cell($cCant, 5, $detalle->cantidad, 0, 0, 'C');

            // Precio unitario
            $pdf->SetFont('Arial', '', 8.5);
            $pdf->SetXY($innerX + $cNro + $cProd + $cCant, $ry + 5);
            $pdf->Cell($cUnit, 5, '$' . number_format($detalle->precio_unitario, 2), 0, 0, 'C');

            // Subtotal (bold, gold)
            $pdf->SetTextColor($goldD[0], $goldD[1], $goldD[2]);
            $pdf->SetFont('Arial', 'B', 9);
            $pdf->SetXY($innerX + $cNro + $cProd + $cCant + $cUnit, $ry + 5);
            $pdf->Cell($cTot, 5, '$' . number_format($detalle->subtotal, 2), 0, 0, 'R');

            // Línea separadora inferior
            $pdf->SetDrawColor($grayL[0], $grayL[1], $grayL[2]);
            $pdf->SetLineWidth(0.2);
            $pdf->Line($margin + 3, $ry + $rowItemH - 1, $margin + $contentW - 3, $ry + $rowItemH - 1);
        }

        // ─── FILA TOTAL ──────────────────────────────────────────────────────────
        $totalY = $rowStartY + count($pedido->detalles) * $rowItemH + 2;

        // Fondo dorado para el total
        $pdf->SetFillColor($gold[0], $gold[1], $gold[2]);
        $pdf->Rect($margin, $totalY, $contentW, 13, 'F');
        // Acento superior claro
        $pdf->SetFillColor($goldL[0], $goldL[1], $goldL[2]);
        $pdf->Rect($margin, $totalY, $contentW, 2.5, 'F');
        // Sombra debajo
        $pdf->SetFillColor(160, 130, 20);
        $pdf->Rect($margin + 2, $totalY + 13, $contentW - 2, 2, 'F');

        $pdf->SetTextColor($dark[0], $dark[1], $dark[2]);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->SetXY($margin + 5, $totalY + 3.5);
        $pdf->Cell($cNro + $cProd + $cCant + $cUnit - 5, 6, $enc('TOTAL A PAGAR'), 0, 0, 'R');

        $pdf->SetFont('Arial', 'B', 13);
        $pdf->SetTextColor($dark[0], $dark[1], $dark[2]);
        $pdf->SetXY($innerX + $cNro + $cProd + $cCant + $cUnit, $totalY + 2.5);
        $pdf->Cell($cTot, 8, '$' . number_format($pedido->total, 2), 0, 0, 'R');

        // ══════════════════════════════════════════════════════════════════════════
        // BADGE DE ESTADO
        // ══════════════════════════════════════════════════════════════════════════
        $badgeY = $totalY + 18;
        $estado = strtolower($pedido->estado);

        $stateMap = [
            'completado' => [[34, 120, 60],  [210, 245, 220], 'COMPLETADO'],
            'cancelado'  => [[160, 35, 35],  [245, 210, 210], 'CANCELADO'],
            'en_proceso' => [[30, 100, 180], [210, 230, 250], 'EN PROCESO'],
        ];
        [$badgeColor, $badgeColorL, $badgeText] = $stateMap[$estado]
            ?? [$gold, $goldL, 'PENDIENTE'];

        $bw = 70; $bh = 12;
        $bx = ($pageW - $bw) / 2;

        // Sombra del badge
        $pdf->SetFillColor(180, 170, 150);
        $pdf->Rect($bx + 2, $badgeY + 2, $bw, $bh, 'F');
        // Fondo
        $pdf->SetFillColor($badgeColor[0], $badgeColor[1], $badgeColor[2]);
        $pdf->Rect($bx, $badgeY, $bw, $bh, 'F');
        // Brillo superior
        $pdf->SetFillColor($badgeColorL[0], $badgeColorL[1], $badgeColorL[2]);
        $pdf->Rect($bx, $badgeY, $bw, 3, 'F');
        // Texto
        $pdf->SetTextColor($white[0], $white[1], $white[2]);
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->SetXY($bx, $badgeY + 2.5);
        $pdf->Cell($bw, 7, $enc('ESTADO: ' . $badgeText), 0, 0, 'C');

        // Subtexto bajo badge
        $pdf->SetTextColor($gray[0], $gray[1], $gray[2]);
        $pdf->SetFont('Arial', 'I', 7);
        $pdf->SetXY(0, $badgeY + $bh + 4);
        $pdf->Cell($pageW, 4, $enc('Estado actual de su pedido'), 0, 0, 'C');

        // ══════════════════════════════════════════════════════════════════════════
        // FOOTER
        // ══════════════════════════════════════════════════════════════════════════
        $footerY = $pdf->GetPageHeight() - 22;

        // Línea dorada separadora
        $pdf->SetDrawColor($gold[0], $gold[1], $gold[2]);
        $pdf->SetLineWidth(0.6);
        $pdf->Line($margin, $footerY, $pageW - $margin, $footerY);

        // Fondo oscuro footer
        $pdf->SetFillColor($dark[0], $dark[1], $dark[2]);
        $pdf->Rect(0, $footerY + 1, $pageW, 25, 'F');

        $pdf->SetTextColor($goldL[0], $goldL[1], $goldL[2]);
        $pdf->SetFont('Arial', 'B', 8.5);
        $pdf->SetXY(0, $footerY + 5);
        $pdf->Cell($pageW, 6, $enc('Sabor & Tradicion  —  Gracias por su preferencia'), 0, 1, 'C');

        $pdf->SetTextColor($gray[0], $gray[1], $gray[2]);
        $pdf->SetFont('Arial', '', 6.5);
        $pdf->SetXY(0, $footerY + 12);
        $pdf->Cell($pageW, 4,
            $enc('Generado el: ' . now()->format('d/m/Y H:i:s') . '   |   Documento oficial  |  ' . $codigoReporte),
            0, 1, 'C');

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
