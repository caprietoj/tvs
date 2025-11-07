<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class EnfermeriaEstudiantesExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles, WithEvents, WithTitle
{
    private $reporteData;
    private $filtros;

    public function __construct($reporteData, $filtros = [])
    {
        $this->reporteData = $reporteData;
        $this->filtros = $filtros;
    }

    public function collection()
    {
        return $this->reporteData;
    }

    public function title(): string
    {
        return 'Reporte Estudiantes';
    }

    public function headings(): array
    {
        return [
            'FECHA',
            'PREESCOLAR',
            'PRIMARIA',
            'BACHILLERATO',
            'ACTIVIDADES DEPORTIVAS',
            'CASOS ESPECIALES',
            'SALIDAS',
            'OBSERVACIONES',
            'NOVEDADES'
        ];
    }

    public function map($dato): array
    {
        return [
            Carbon::parse($dato->fecha)->format('d/m/Y'),
            $dato->preescolar ?? 0,
            $dato->primaria ?? 0,
            $dato->bachillerato ?? 0,
            $dato->deportivas ?? 0,
            $dato->casos_especiales ?? 0,
            $dato->salidas ?? 0,
            $dato->observaciones ?: 'Sin observaciones',
            $dato->novedades ?: 'Sin novedades'
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // Fecha
            'B' => 15,  // Preescolar
            'C' => 15,  // Primaria
            'D' => 18,  // Bachillerato
            'E' => 25,  // Actividades Deportivas
            'F' => 20,  // Casos Especiales
            'G' => 12,  // Salidas
            'H' => 40,  // Observaciones
            'I' => 40,  // Novedades
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '314569']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // Agregar información de filtros si existen
                if (!empty($this->filtros)) {
                    $sheet->insertNewRowBefore(1, 3);
                    
                    $sheet->setCellValue('A1', 'REPORTE DE ENFERMERÍA - ESTUDIANTES');
                    $sheet->mergeCells('A1:I1');
                    $sheet->getStyle('A1')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 16,
                            'color' => ['rgb' => '314569']
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    $filtrosTexto = 'Filtros aplicados: ';
                    if (isset($this->filtros['fecha_desde']) && isset($this->filtros['fecha_hasta'])) {
                        $filtrosTexto .= 'Período: ' . $this->filtros['fecha_desde'] . ' a ' . $this->filtros['fecha_hasta'] . ' | ';
                    }
                    if (isset($this->filtros['seccion']) && $this->filtros['seccion']) {
                        $filtrosTexto .= 'Sección: ' . ucfirst($this->filtros['seccion']) . ' | ';
                    }
                    $filtrosTexto .= 'Generado: ' . date('d/m/Y H:i');

                    $sheet->setCellValue('A2', $filtrosTexto);
                    $sheet->mergeCells('A2:I2');
                    $sheet->getStyle('A2')->applyFromArray([
                        'font' => ['size' => 10, 'italic' => true],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    ]);

                    $highestRow += 3;
                }

                // Aplicar bordes a todas las celdas con datos
                $sheet->getStyle('A4:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Centrar columnas numéricas
                for ($i = 4; $i <= $highestRow; $i++) {
                    $sheet->getStyle('A' . $i . ':G' . $i)->applyFromArray([
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);
                }

                // Alinear a la izquierda las columnas de texto
                $sheet->getStyle('H4:I' . $highestRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_TOP,
                        'wrapText' => true,
                    ],
                ]);

                // Agregar fila de totales
                $totalRow = $highestRow + 1;
                $sheet->setCellValue('A' . $totalRow, 'TOTALES:');
                $sheet->getStyle('A' . $totalRow)->getFont()->setBold(true);
                
                // Calcular totales
                $sheet->setCellValue('B' . $totalRow, '=SUM(B4:B' . $highestRow . ')');
                $sheet->setCellValue('C' . $totalRow, '=SUM(C4:C' . $highestRow . ')');
                $sheet->setCellValue('D' . $totalRow, '=SUM(D4:D' . $highestRow . ')');
                $sheet->setCellValue('E' . $totalRow, '=SUM(E4:E' . $highestRow . ')');
                $sheet->setCellValue('F' . $totalRow, '=SUM(F4:F' . $highestRow . ')');
                $sheet->setCellValue('G' . $totalRow, '=SUM(G4:G' . $highestRow . ')');

                $sheet->getStyle('A' . $totalRow . ':G' . $totalRow)->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E8F4F8']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Ajustar altura de filas
                for ($i = 4; $i <= $highestRow; $i++) {
                    $sheet->getRowDimension($i)->setRowHeight(30);
                }
                $sheet->getRowDimension(4)->setRowHeight(35);
            },
        ];
    }
}
