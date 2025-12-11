<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Illuminate\Support\Collection;

class SpaceReservationsExport implements FromCollection, WithHeadings, WithStyles, WithTitle, WithColumnWidths, WithCustomStartCell, WithEvents
{
    protected $reservations;
    protected $stats;

    public function __construct($reservations, $stats)
    {
        $this->reservations = $reservations;
        $this->stats = $stats;
    }

    /**
     * Celda inicial (dejamos espacio para las estadísticas arriba)
     */
    public function startCell(): string
    {
        return 'A8';
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->reservations->map(function ($reservation) {
            return [
                'espacio' => $reservation->space->name,
                'fecha' => \Carbon\Carbon::parse($reservation->date)->format('d/m/Y'),
                'hora_inicio' => \Carbon\Carbon::parse($reservation->start_time)->format('H:i'),
                'hora_fin' => \Carbon\Carbon::parse($reservation->end_time)->format('H:i'),
                'solicitante' => $reservation->user->name,
                'proposito' => $reservation->purpose,
                'estado' => $this->getEstadoTexto($reservation->status),
            ];
        });
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ESPACIO',
            'FECHA',
            'HORA INICIO',
            'HORA FIN',
            'SOLICITANTE',
            'PROPÓSITO',
            'ESTADO'
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Reservas de Espacios';
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 25,
            'B' => 12,
            'C' => 12,
            'D' => 12,
            'E' => 25,
            'F' => 40,
            'G' => 15,
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Título principal
        $sheet->setCellValue('A1', 'REPORTE DE RESERVAS DE ESPACIOS');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '3C8DBC'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Información de generación
        $sheet->setCellValue('A2', 'Fecha de generación:');
        $sheet->setCellValue('B2', now()->format('d/m/Y H:i'));
        $sheet->setCellValue('E2', 'Generado por:');
        $sheet->setCellValue('F2', auth()->user()->name);
        $sheet->getStyle('A2:G2')->applyFromArray([
            'font' => ['size' => 10],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'F8F9FA'],
            ],
        ]);
        $sheet->getStyle('A2:A2')->getFont()->setBold(true);
        $sheet->getStyle('E2:E2')->getFont()->setBold(true);

        // Estadísticas - Fila 4
        $sheet->setCellValue('A4', 'TOTAL RESERVAS');
        $sheet->setCellValue('B4', $this->stats['total']);
        $sheet->setCellValue('C4', 'APROBADAS');
        $sheet->setCellValue('D4', $this->stats['approved']);
        $sheet->setCellValue('E4', 'PENDIENTES');
        $sheet->setCellValue('F4', $this->stats['pending']);
        $sheet->setCellValue('G4', 'RECHAZADAS');
        
        $sheet->setCellValue('A5', '');
        $sheet->setCellValue('B5', '');
        $sheet->setCellValue('C5', '');
        $sheet->setCellValue('D5', '');
        $sheet->setCellValue('E5', '');
        $sheet->setCellValue('F5', '');
        $sheet->setCellValue('G5', $this->stats['rejected']);

        // Estilo para etiquetas de estadísticas
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3C8DBC']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('C4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '00A65A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('E4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F39C12']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('G4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DD4B39']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        // Estilo para valores de estadísticas
        $sheet->getStyle('B4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E3F2FD']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('D4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E8F5E9']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('F4')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF3E0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle('G5')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFEBEE']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);

        $sheet->getRowDimension(4)->setRowHeight(25);
        $sheet->getRowDimension(5)->setRowHeight(25);

        // Encabezados de tabla (fila 8)
        $sheet->getStyle('A8:G8')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '367FA9'],
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
        $sheet->getRowDimension(8)->setRowHeight(20);

        // Estilo para los datos
        $lastRow = 8 + $this->reservations->count();
        $sheet->getStyle('A9:G' . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        // Centrar columnas específicas
        $sheet->getStyle('B9:D' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G9:G' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Alternar colores de filas
        for ($i = 9; $i <= $lastRow; $i++) {
            if ($i % 2 == 0) {
                $sheet->getStyle('A' . $i . ':G' . $i)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8F9FA'],
                    ],
                ]);
            }
        }

        // Colorear la columna de estado según el valor
        for ($i = 9; $i <= $lastRow; $i++) {
            $estado = $sheet->getCell('G' . $i)->getValue();
            $color = 'FFFFFF';
            
            if ($estado === 'Aprobada') {
                $color = 'D4EDDA';
            } elseif ($estado === 'Pendiente') {
                $color = 'FFF3CD';
            } elseif ($estado === 'Rechazada') {
                $color = 'F8D7DA';
            } elseif ($estado === 'Cancelada') {
                $color = 'E2E3E5';
            }
            
            $sheet->getStyle('G' . $i)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $color],
                ],
                'font' => ['bold' => true],
            ]);
        }

        return [];
    }

    /**
     * Obtener texto del estado
     */
    private function getEstadoTexto($status)
    {
        $estados = [
            'approved' => 'Aprobada',
            'pending' => 'Pendiente',
            'rejected' => 'Rechazada',
            'cancelled' => 'Cancelada',
        ];

        return $estados[$status] ?? $status;
    }

    /**
     * Registrar eventos para aplicar auto-filtro
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $lastRow = 8 + $this->reservations->count();
                
                // Aplicar auto-filtro a los encabezados de la tabla
                $event->sheet->setAutoFilter('A8:G' . $lastRow);
                
                // Congelar paneles (fijar las primeras 8 filas para que siempre estén visibles)
                $event->sheet->freezePane('A9');
            },
        ];
    }
}
