<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class PhotocopiesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $requests;

    public function __construct($requests)
    {
        $this->requests = $requests;
    }

    public function collection()
    {
        return $this->requests;
    }

    public function headings(): array
    {
        return [
            'MES',
            'NOMBRE DOCENTE',
            'SECCIÓN',
            'CURSO',
            'IMPRESIONES B/N',
            'IMPRESIONES COLOR',
            'DOBLE CARTA COLOR',
            'FECHA DE ENTREGA',
            'RECIBIDO A SATISFACCIÓN'
        ];
    }

    public function map($request): array
    {
        // Calcular totales de impresiones desde copy_items
        $blancoNegroTotal = 0;
        $colorTotal = 0;
        $dobleCartaTotal = 0;
        
        if (is_array($request->copy_items)) {
            foreach ($request->copy_items as $item) {
                $blancoNegroTotal += (int)($item['black_white'] ?? 0);
                $colorTotal += (int)($item['color'] ?? 0);
                $dobleCartaTotal += (int)($item['double_letter_color'] ?? 0);
            }
        }
        
        // Estado de satisfacción basado en el status
        $satisfaccion = in_array($request->status, ['approved', 'completed']) ? 'SÍ' : 'PENDIENTE';
        
        return [
            Carbon::parse($request->created_at)->format('F Y'),
            $request->requester ?? '',
            $request->section ?? '',
            $request->grade ?? '',
            $blancoNegroTotal,
            $colorTotal,
            $dobleCartaTotal,
            $request->delivery_date ? Carbon::parse($request->delivery_date)->format('Y-m-d') : '',
            $satisfaccion
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilos para la fila de encabezados
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '364E76']
                ]
            ]
        ];
    }
}
