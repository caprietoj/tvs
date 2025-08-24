<?php

namespace App\Exports;

use App\Models\PresupuestoItem;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PresupuestoExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        return PresupuestoItem::orderBy('seccion')
                            ->orderBy('created_at')
                            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Fuente',
            'Documento', 
            'Fecha',
            'Cuenta',
            'Sección',
            'Rubro',
            'Descripción',
            'Valor',
            'Valor Moneda',
            'Cliente/Proveedor',
            'Nombre Cliente/Proveedor',
            'Tercero',
            'Nombre Tercero',
            'Auxiliar',
            'Centro Costo',
            'Es Total',
            'Fecha Creación'
        ];
    }

    public function map($item): array
    {
        return [
            $item->id,
            $item->fuente,
            $item->documento,
            $item->fecha?->format('Y-m-d'),
            $item->cuenta,
            $item->seccion,
            $item->rubro,
            $item->descripcion,
            $item->valor,
            $item->valor_moneda,
            $item->cliente_proveedor,
            $item->nombre_cliente_proveedor,
            $item->tercero,
            $item->nombre_tercero,
            $item->auxiliar,
            $item->centro_costo,
            $item->es_total ? 'SÍ' : 'NO',
            $item->created_at->format('Y-m-d H:i:s')
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para el encabezado
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => Color::COLOR_WHITE],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => '366092'],
                ],
            ],
        ];
    }
}
