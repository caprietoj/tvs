<?php

namespace App\Exports;

use App\Models\SpaceBlock;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SpaceBlocksExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return SpaceBlock::with(['space', 'schoolCycle'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Espacio',
            'Ciclo Escolar',
            'Día del Ciclo',
            'Motivo'
        ];
    }

    /**
     * @param mixed $spaceBlock
     * @return array
     */
    public function map($spaceBlock): array
    {
        return [
            $spaceBlock->space ? $spaceBlock->space->name : 'N/A',
            $spaceBlock->schoolCycle ? $spaceBlock->schoolCycle->year : 'N/A',
            $spaceBlock->cycle_day ?? 'N/A',
            $spaceBlock->reason ?? 'Sin motivo especificado'
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 30,  // Espacio
            'B' => 15,  // Ciclo Escolar
            'C' => 15,  // Día del Ciclo
            'D' => 50,  // Motivo
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para la fila de encabezados
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ],
            // Estilo para todas las filas de datos
            'A:D' => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]
        ];
    }
}
