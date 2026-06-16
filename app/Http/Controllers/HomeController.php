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
        
        $sheet->setCellValue('A1', 'Reporte de Historial - Sabor & Tradición');
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFC29545'));
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $user = auth()->user();

        // Date generated
        $sheet->setCellValue('A2', 'Generado el: ' . date('d/m/Y H:i:s'));
        $sheet->mergeCells('A2:E2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2')->getFont()->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FF555555'));

        // Client Info
        $sheet->setCellValue('A4', 'Información del Cliente:');
        $sheet->getStyle('A4')->getFont()->setBold(true)->setSize(12);
        
        $sheet->setCellValue('A5', 'Nombre:');
        $sheet->setCellValue('B5', $user->name . ' ' . $user->lastname);
        $sheet->getStyle('A5')->getFont()->setBold(true);

        $sheet->setCellValue('A6', 'Documento:');
        $sheet->setCellValue('B6', $user->numero_documento ?? 'N/A');
        $sheet->getStyle('A6')->getFont()->setBold(true);

        $sheet->setCellValue('A7', 'Teléfono:');
        $sheet->setCellValue('B7', $user->telefono ?? 'N/A');
        $sheet->getStyle('A7')->getFont()->setBold(true);

        $sheet->setCellValue('A8', 'Correo:');
        $sheet->setCellValue('B8', $user->email);
        $sheet->getStyle('A8')->getFont()->setBold(true);

        $columns = ['A' => '#', 'B' => 'Tipo', 'C' => 'Detalle', 'D' => 'Fecha', 'E' => 'Estado'];
        foreach ($columns as $col => $title) {
            $sheet->setCellValue($col . '10', $title);
        }

        $headerStyleArray = [
            'font' => [
                'bold' => true,
                'color' => ['argb' => \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['argb' => 'FF1A1A1A'], // Dark background for header
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => 'FFC29545']
                ],
            ],
        ];
        $sheet->getStyle('A10:E10')->applyFromArray($headerStyleArray);

        $rowNum = 11;
        foreach ($historial as $index => $row) {
            $sheet->setCellValue('A' . $rowNum, $index + 1);
            $sheet->setCellValue('B' . $rowNum, $row['tipo']);
            $sheet->setCellValue('C' . $rowNum, $row['detalle']);
            $sheet->setCellValue('D' . $rowNum, $row['fecha']);
            $sheet->setCellValue('E' . $rowNum, $row['estado']);

            // Border for data row
            $sheet->getStyle('A'.$rowNum.':E'.$rowNum)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFDDDDDD'));
            
            // Alternating colors
            if ($rowNum % 2 == 0) {
                $sheet->getStyle('A'.$rowNum.':E'.$rowNum)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF9F9F9');
            }

            // Status color highlight
            $statusColor = 'FF000000';
            if (strtolower($row['estado']) === 'completado') $statusColor = 'FF27AE60';
            else if (strtolower($row['estado']) === 'pendiente') $statusColor = 'FFE67E22';
            else if (strtolower($row['estado']) === 'cancelada') $statusColor = 'FFE74C3C';
            $sheet->getStyle('E'.$rowNum)->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color($statusColor))->setBold(true);

            // Center align some columns
            $sheet->getStyle('A'.$rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('B'.$rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('D'.$rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('E'.$rowNum)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            $rowNum++;
        }

        foreach (range('A', 'E') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

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
