<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Response;

class CopiesRequestController extends Controller
{
    public function index()
    {
        $requests = PurchaseRequest::where('type', 'materials')
            ->whereNotNull('copy_items')
            ->whereNull('material_items')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('copies-requests.index', compact('requests'));
    }
    
    public function export()
    {
        $requests = PurchaseRequest::where('type', 'materials')
            ->whereNotNull('copy_items')
            ->whereNull('material_items')
            ->with(['user', 'approver'])
            ->orderBy('created_at', 'desc')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Configuración inicial del documento
        $spreadsheet->getProperties()
            ->setCreator('Intranet TVS')
            ->setTitle('Solicitudes de Copias')
            ->setDescription('Exportación de solicitudes de fotocopias');

        // Establecer título del documento
        $sheet->setCellValue('A1', 'SOLICITUDES DE FOTOCOPIAS');
        $sheet->mergeCells('A1:I1');
        
        // Estilo del título
        $titleStyle = [
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '364E76']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ]
        ];
        $sheet->getStyle('A1')->applyFromArray($titleStyle);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Encabezados de las columnas según la nueva estructura
        $headers = [
            'A' => 'FECHA',
            'B' => 'N° SOLICITUD',
            'C' => 'DOCENTE',
            'D' => 'SECCIÓN',
            'E' => 'GRADO',
            'F' => 'ORIGINAL',
            'G' => 'COPIAS REQ.',
            'H' => 'BLANCO Y NEGRO',
            'I' => 'COLOR',
            'J' => 'DOBLE CARTA COLOR',
            'K' => 'IMPRESIÓN',
            'L' => 'TOTAL',
            'M' => 'FECHA ENTREGA',
            'N' => 'ESTADO',
            'O' => 'APROBADO POR'
        ];

        // Establecer encabezados en la fila 3
        foreach ($headers as $column => $header) {
            $sheet->setCellValue($column . '3', $header);
        }

        // Estilo de los encabezados
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '364E76']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];
        $sheet->getStyle('A3:O3')->applyFromArray($headerStyle);

        // Ajustar altura de la fila de encabezados
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Establecer ancho de las columnas
        $columnWidths = [
            'A' => 12,  // FECHA
            'B' => 15,  // N° SOLICITUD
            'C' => 25,  // DOCENTE
            'D' => 15,  // SECCIÓN
            'E' => 12,  // GRADO
            'F' => 30,  // ORIGINAL
            'G' => 12,  // COPIAS REQ.
            'H' => 12,  // BLANCO Y NEGRO
            'I' => 12,  // COLOR
            'J' => 18,  // DOBLE CARTA COLOR
            'K' => 12,  // IMPRESIÓN
            'L' => 10,  // TOTAL
            'M' => 15,  // FECHA ENTREGA
            'N' => 12,  // ESTADO
            'O' => 20   // APROBADO POR
        ];

        foreach ($columnWidths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        // Llenar datos
        $row = 4;
        foreach ($requests as $request) {
            if (!empty($request->copy_items)) {
                foreach ($request->copy_items as $item) {
                    // FECHA
                    $sheet->setCellValue("A{$row}", $request->request_date ? $request->request_date->format('d/m/Y') : 'N/A');
                    
                    // N° SOLICITUD
                    $sheet->setCellValue("B{$row}", $request->request_number ?? 'N/A');
                    
                    // DOCENTE
                    $sheet->setCellValue("C{$row}", $request->requester ?? 'N/A');
                    
                    // SECCIÓN
                    $sheet->setCellValue("D{$row}", $request->section ?? 'N/A');
                    
                    // GRADO
                    $sheet->setCellValue("E{$row}", $request->grade ?? 'N/A');
                    
                    // ORIGINAL
                    $sheet->setCellValue("F{$row}", $item['original'] ?? 'N/A');
                    
                    // COPIAS REQUERIDAS
                    $sheet->setCellValue("G{$row}", intval($item['copies_required'] ?? 0));
                    
                    // BLANCO Y NEGRO
                    $sheet->setCellValue("H{$row}", isset($item['black_white']) && $item['black_white'] ? 'SÍ' : 'NO');
                    
                    // COLOR
                    $sheet->setCellValue("I{$row}", isset($item['color']) && $item['color'] ? 'SÍ' : 'NO');
                    
                    // DOBLE CARTA COLOR
                    $sheet->setCellValue("J{$row}", isset($item['double_letter_color']) && $item['double_letter_color'] ? 'SÍ' : 'NO');
                    
                    // IMPRESIÓN
                    $sheet->setCellValue("K{$row}", isset($item['impresion']) && $item['impresion'] ? 'SÍ' : 'NO');
                    
                    // TOTAL
                    $sheet->setCellValue("L{$row}", intval($item['total'] ?? 0));
                    
                    // FECHA DE ENTREGA
                    $deliveryDate = $request->delivery_date ? $request->delivery_date->format('d/m/Y') : 'N/A';
                    $sheet->setCellValue("M{$row}", $deliveryDate);
                    
                    // ESTADO
                    $status = '';
                    switch($request->status) {
                        case 'pending':
                            $status = 'Pendiente';
                            break;
                        case 'approved':
                            $status = 'Aprobada';
                            break;
                        case 'rejected':
                            $status = 'Rechazada';
                            break;
                        default:
                            $status = 'N/A';
                    }
                    $sheet->setCellValue("N{$row}", $status);
                    
                    // APROBADO POR
                    $approver = $request->approved_by ? optional($request->approver)->name : 'Pendiente';
                    $sheet->setCellValue("O{$row}", $approver);
                    
                    // Aplicar estilo a la fila de datos
                    $rowStyle = [
                        'alignment' => [
                            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                'color' => ['rgb' => '000000']
                            ]
                        ]
                    ];
                    $sheet->getStyle("A{$row}:O{$row}")->applyFromArray($rowStyle);
                    
                    $row++;
                }
            }
        }

        // Si no hay datos, agregar una fila indicándolo
        if ($row === 4) {
            $sheet->setCellValue('A4', 'No hay solicitudes de fotocopias registradas');
            $sheet->mergeCells('A4:O4');
            $sheet->getStyle('A4')->applyFromArray([
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                ],
                'font' => ['italic' => true]
            ]);
        }

        // Configurar respuesta para descarga
        $filename = 'solicitudes_fotocopias_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        $writer = new Xlsx($spreadsheet);
        
        // Preparar respuesta
        ob_start();
        $writer->save('php://output');
        $excelOutput = ob_get_clean();        
        return Response::make($excelOutput, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
