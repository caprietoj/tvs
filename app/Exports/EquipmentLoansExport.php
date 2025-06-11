<?php

namespace App\Exports;

use App\Models\EquipmentLoan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class EquipmentLoansExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $loans;

    public function __construct(Collection $loans)
    {
        $this->loans = $loans;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->loans;
    }

    /**
     * @param EquipmentLoan $loan
     * @return array
     */
    public function map($loan): array
    {
        return [
            'ID' => $loan->id,
            'Docente' => $loan->user ? $loan->user->name : 'N/A',
            'Sección' => ucfirst(str_replace('_', ' ', $loan->section)),
            'Salón' => $loan->grade, // Changed to match the column name shown in UI
            'Equipo' => $loan->equipment_type === 'laptop' ? 'Portátil' : 'iPad',
            'Cantidad' => $loan->units_requested,
            'Fecha' => $loan->loan_date ? Carbon::parse($loan->loan_date)->format('d/m/Y') : 'N/A',
            'Horario' => ($loan->start_time && $loan->end_time) ? 
                Carbon::parse($loan->start_time)->format('H:i') . ' - ' . 
                Carbon::parse($loan->end_time)->format('H:i') : 'N/A',
            'Estado' => $this->getStatusText($loan),
            'Observaciones de Entrega' => $loan->delivery_observations ?? '',
            'Observaciones de Devolución' => $loan->return_observations ?? '',
        ];
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Docente',
            'Sección',
            'Salón',
            'Equipo',
            'Cantidad',
            'Fecha',
            'Horario',
            'Estado',
            'Observaciones de Entrega',
            'Observaciones de Devolución',
        ];
    }

    /**
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        // Definir las columnas que queremos resaltar
        $highlightColumns = [
            'D' => 'Salón',     // Columna Salón
            'F' => 'Cantidad',  // Columna Cantidad
            'H' => 'Horario',   // Columna Horario
        ];
        
        // Estilo para la cabecera
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '364E76'], // Color primario del sitio
            ],
        ]);
        
        // Resaltar columnas específicas
        foreach ($highlightColumns as $column => $name) {
            // Estilo para las columnas resaltadas (desde la fila 2 hasta la última)
            $lastRow = $sheet->getHighestRow();
            $sheet->getStyle($column . '2:' . $column . $lastRow)->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFEB9C'], // Color amarillo claro
                ],
                'font' => [
                    'bold' => true,
                ],
            ]);
            
            // Estilo adicional para la cabecera de estas columnas
            $sheet->getStyle($column . '1')->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'ED3236'], // Color de acento del sitio
                ],
            ]);
        }
        
        // Borde para todas las celdas con datos
        $sheet->getStyle('A1:K' . $sheet->getHighestRow())->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);
    }

    /**
     * Obtener texto de estado del préstamo
     */
    private function getStatusText($loan)
    {
        switch ($loan->status) {
            case 'returned':
                return 'Devuelto';
            case 'delivered':
                return 'Entregado';
            case 'pending':
                return 'Pendiente';
            default:
                return ucfirst($loan->status);
        }
    }
}