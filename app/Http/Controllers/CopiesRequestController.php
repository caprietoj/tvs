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
            'A' => 'MES',
            'B' => 'NOMBRE DOCENTE', 
            'C' => 'SECCIÓN',
            'D' => 'CURSO',
            'E' => 'IMPRESIONES B/N',
            'F' => 'IMPRESIONES COLOR',
            'G' => 'DOBLE CARTA COLOR',
            'H' => 'FECHA DE ENTREGA',
            'I' => 'RECIBIDO A SATISFACCIÓN'
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
        $sheet->getStyle('A3:I3')->applyFromArray($headerStyle);

        // Ajustar altura de la fila de encabezados
        $sheet->getRowDimension(3)->setRowHeight(25);

        // Establecer ancho de las columnas
        $columnWidths = [
            'A' => 12,  // MES
            'B' => 25,  // NOMBRE DOCENTE
            'C' => 15,  // SECCIÓN
            'D' => 20,  // CURSO
            'E' => 18,  // IMPRESIONES B/N
            'F' => 18,  // IMPRESIONES COLOR
            'G' => 20,  // DOBLE CARTA COLOR
            'H' => 18,  // FECHA DE ENTREGA
            'I' => 25   // RECIBIDO A SATISFACCIÓN
        ];

        foreach ($columnWidths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        // Llenar datos
        $row = 4;
        foreach ($requests as $request) {
            if (!empty($request->copy_items)) {
                foreach ($request->copy_items as $item) {
                    // Solo procesar elementos que tengan al menos un campo de impresiones
                    if (!empty($item['black_white']) || !empty($item['color']) || !empty($item['double_letter_color'])) {
                        
                        // MES - extraer mes de la fecha de solicitud
                        $month = $request->request_date ? $request->request_date->format('F Y') : 'N/A';
                        $sheet->setCellValue("A{$row}", $month);
                        
                        // NOMBRE DOCENTE
                        $sheet->setCellValue("B{$row}", $request->requester ?? 'N/A');
                        
                        // SECCIÓN
                        $sheet->setCellValue("C{$row}", $request->section ?? 'N/A');
                        
                        // CURSO
                        $sheet->setCellValue("D{$row}", $request->grade ?? 'N/A');
                        
                        // IMPRESIONES B/N
                        $sheet->setCellValue("E{$row}", intval($item['black_white'] ?? 0));
                        
                        // IMPRESIONES COLOR
                        $sheet->setCellValue("F{$row}", intval($item['color'] ?? 0));
                        
                        // DOBLE CARTA COLOR
                        $sheet->setCellValue("G{$row}", intval($item['double_letter_color'] ?? 0));
                        
                        // FECHA DE ENTREGA
                        $deliveryDate = $request->delivery_date ? $request->delivery_date->format('d/m/Y') : 'N/A';
                        $sheet->setCellValue("H{$row}", $deliveryDate);
                        
                        // RECIBIDO A SATISFACCIÓN
                        $satisfactionStatus = $request->approved_by ? 'Sí' : 'Pendiente';
                        $sheet->setCellValue("I{$row}", $satisfactionStatus);
                        
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
                        $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($rowStyle);
                        
                        $row++;
                    }
                }
            }
        }

        // Si no hay datos, agregar una fila indicándolo
        if ($row === 4) {
            $sheet->setCellValue('A4', 'No hay solicitudes de fotocopias registradas');
            $sheet->mergeCells('A4:I4');
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
