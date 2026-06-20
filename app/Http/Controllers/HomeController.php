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
        if (auth()->check() && auth()->user()->rol === 'admin') {
            return redirect()->route('admin.index');
        }

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
                    'id' => $pedido->id,
                    'tipo' => 'pedido',
                    'detalle' => $detalles ?: 'Sin detalles',
                    'fecha_creacion' => $pedido->created_at->format('d/m/Y H:i'),
                    'fecha' => $pedido->created_at->format('d/m/Y'),
                    'fecha_reserva' => null,
                    'estado' => $pedido->estado
                ];
            });

        $reservas = \App\Models\Reserva::where('user_id', auth()->id())
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

        // Combinar y ordenar por fecha
        $historial = $pedidos->concat($reservas)->sortByDesc(function($item) {
            $parts = explode('/', $item['fecha']);
            if (count($parts) === 3) {
                return mktime(0, 0, 0, $parts[1], $parts[0], $parts[2]);
            }
            return 0;
        })->values();

        return response()->json([
            'success' => true,
            'historial' => $historial
        ]);
    }

    public function exportarHistorial(Request $request)
    {
        $filtro = $request->query('filtro', 'todos');
        
        $pedidos = collect();
        if (in_array($filtro, ['todos', 'pedidos'])) {
            $pedidos = \App\Models\Pedido::where('user_id', auth()->id())
                ->with(['detalles.plato'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($pedido) {
                    $detalles = $pedido->detalles->map(function ($detalle) {
                        return $detalle->plato->nombre . ' x' . $detalle->cantidad;
                    })->join(', ');

                    return [
                        'id' => $pedido->id,
                        'tipo' => 'Pedido',
                        'detalle' => $detalles ?: 'Sin detalles',
                        'fecha' => $pedido->created_at->format('d/m/Y'),
                        'estado' => ucfirst($pedido->estado)
                    ];
                });
        }

        $reservas = collect();
        if (in_array($filtro, ['todos', 'reservas'])) {
            $reservas = \App\Models\Reserva::where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($reserva) {
                    return [
                        'id' => $reserva->id,
                        'tipo' => 'Reserva',
                        'detalle' => "Mesa {$reserva->mesa} - {$reserva->personas} persona(s) - {$reserva->zona}",
                        'fecha' => $reserva->created_at->format('d/m/Y'),
                        'estado' => ucfirst($reserva->estado)
                    ];
                });
        }

        $historial = $pedidos->concat($reservas)->sortByDesc(function($item) {
            $parts = explode('/', $item['fecha']);
            if (count($parts) === 3) {
                return mktime(0, 0, 0, $parts[1], $parts[0], $parts[2]);
            }
            return 0;
        })->values();

        if ($historial->isEmpty()) {
            return back()->with('error', 'No hay datos en el historial para exportar.');
        }

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // --- 1. CONFIGURACIÓN GENERAL ---
        $sheet->setShowGridlines(false); // Ocultar líneas de cuadrícula para un look más limpio

        $tituloReporte = 'Reporte de Historial General';
        if ($filtro === 'pedidos') {
            $tituloReporte = 'Reporte de Pedidos';
        } elseif ($filtro === 'reservas') {
            $tituloReporte = 'Reporte de Reservas';
        }

        // --- 2. ENCABEZADO (HEADER) ---
        // Fondo oscuro y elegante para las primeras 4 filas
        $sheet->mergeCells('A1:E4');
        $sheet->getStyle('A1:E4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1C1C23');
        
        // Agregar Logo
        $logoPath = public_path('img/LogoRestaurant.png');
        if (file_exists($logoPath)) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Logo');
            $drawing->setDescription('Logo Sabor & Tradicion');
            $drawing->setPath($logoPath);
            $drawing->setHeight(60);
            $drawing->setCoordinates('A1');
            $drawing->setOffsetX(15);
            $drawing->setOffsetY(10);
            $drawing->setWorksheet($sheet);
        }

        // Título del documento
        $sheet->setCellValue('A1', "\nSabor & Tradición\n" . $tituloReporte);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1')->getAlignment()->setWrapText(true);
        // Fuente Times New Roman en dorado
        $sheet->getStyle('A1')->getFont()->setName('Times New Roman')->setBold(true)->setSize(16)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFC29545'));
        
        // Borde inferior dorado
        $sheet->getStyle('A4:E4')->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFC29545'));

        // --- 3. DATOS DEL CLIENTE ---
        $user = auth()->user();
        
        // Cabecera de la sección de cliente (Fondo Azul)
        $sheet->setCellValue('A6', 'DATOS DEL CLIENTE');
        $sheet->mergeCells('A6:E6');
        $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'));
        $sheet->getStyle('A6')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3C78');
        $sheet->getStyle('A6')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(6)->setRowHeight(20);

        // Fondo azul claro para los datos
        $sheet->getStyle('A7:E9')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFDCE6F8');

        $sheet->setCellValue('A7', 'Nombre:');
        $sheet->setCellValue('B7', $user->name . ' ' . $user->lastname);
        $sheet->setCellValue('A8', 'Documento:');
        $sheet->setCellValue('B8', $user->numero_documento ?? 'No registrado');
        $sheet->setCellValue('A9', 'Correo:');
        $sheet->setCellValue('B9', $user->email ?? 'No registrado');

        $sheet->getStyle('A7:A9')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3C78'));
        $sheet->getStyle('B7:B9')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1C1C23'));

        // Metadatos a la derecha
        $sheet->setCellValue('D8', 'Generado:');
        $sheet->setCellValue('E8', date('d/m/Y H:i'));
        $sheet->getStyle('D8')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3C78'));
        $sheet->getStyle('E8')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
        
        $sheet->setCellValue('D9', 'Filtro Activo:');
        $sheet->setCellValue('E9', ucfirst($filtro));
        $sheet->getStyle('D9')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF1E3C78'));
        $sheet->getStyle('E9')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);

        // --- 4. CABECERA DE LA TABLA ---
        $columns = ['A' => 'N°', 'B' => 'TIPO', 'C' => 'DETALLE', 'D' => 'FECHA', 'E' => 'ESTADO'];
        foreach ($columns as $col => $title) {
            $sheet->setCellValue($col . '11', $title);
        }

        $headerStyleArray = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => 'FFFFFFFF'],
                'size' => 10,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FFC29545'], // Dorado
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'bottom' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                    'color' => ['argb' => 'FF1C1C23']
                ],
            ],
        ];
        $sheet->getStyle('A11:E11')->applyFromArray($headerStyleArray);
        $sheet->getRowDimension(11)->setRowHeight(25);

        // --- 5. DATOS DE LA TABLA ---
        $rowNum = 12;
        foreach ($historial as $index => $row) {
            $sheet->setCellValue('A' . $rowNum, $index + 1);
            $sheet->setCellValue('B' . $rowNum, $row['tipo']);
            $sheet->setCellValue('C' . $rowNum, $row['detalle']);
            $sheet->setCellValue('D' . $rowNum, $row['fecha']);
            $sheet->setCellValue('E' . $rowNum, strtoupper($row['estado']));

            // Colores alternos
            $rowColor = ($rowNum % 2 == 0) ? 'FFFFFFFF' : 'FFF2F0E9'; // Blanco / Crema claro
            $sheet->getStyle('A'.$rowNum.':E'.$rowNum)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB($rowColor);

            // Borde sutil inferior
            $sheet->getStyle('A'.$rowNum.':E'.$rowNum)->getBorders()->getBottom()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_HAIR)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFDDDDDD'));

            // Colores por Estado (Badge Style)
            $estado = strtolower($row['estado']);
            $statusColor = 'FF1C1C23';
            if ($estado === 'completado' || $estado === 'confirmada') {
                $statusColor = 'FF226E3C'; // Verde
            } elseif ($estado === 'pendiente' || $estado === 'en proceso' || $estado === 'en_proceso') {
                $statusColor = 'FFAA640A'; // Ámbar
            } elseif ($estado === 'cancelado' || $estado === 'cancelada') {
                $statusColor = 'FFA02323'; // Rojo
            }
            $sheet->getStyle('E'.$rowNum)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($statusColor))->setBold(true);

            // Alineaciones
            $sheet->getStyle('A'.$rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B'.$rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C'.$rowNum)->getAlignment()->setWrapText(true);
            $sheet->getStyle('D'.$rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E'.$rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Centrado vertical
            $sheet->getStyle('A'.$rowNum.':E'.$rowNum)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            
            // Altura para texto largo
            $sheet->getRowDimension($rowNum)->setRowHeight(30);

            $rowNum++;
        }

        // Ajuste de columnas
        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(15);
        $sheet->getColumnDimension('C')->setWidth(50);
        $sheet->getColumnDimension('D')->setWidth(15);
        $sheet->getColumnDimension('E')->setWidth(18);

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = "historial_" . $filtro . "_" . date('Ymd_His') . ".xlsx";
        
        $callback = function() use ($writer) {
            $writer->save('php://output');
        };

        $headers = [
            "Content-Type" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
            "Content-Disposition" => "attachment; filename=\"$filename\"",
            "Cache-Control" => "max-age=0",
        ];

        return response()->stream($callback, 200, $headers);
    }
}
