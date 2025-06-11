<?php

namespace App\Exports;

use App\Models\Proveedor;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;

class ProveedoresExport implements FromCollection, WithHeadings, WithMapping, WithStyles, 
    WithColumnFormatting, WithColumnWidths, WithTitle, WithEvents
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Proveedor::all();
    }

    /**
     * Set the title of the worksheet
     *
     * @return string
     */
    public function title(): string
    {
        return 'Listado de Proveedores';
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            'Nombre/Razón Social',
            'NIT',
            'Dirección',
            'Ciudad',
            'Teléfono',
            'Email',
            'Persona de Contacto',
            'Servicio/Producto Ofrecido',
            'Proveedor Crítico',
            'Alto Riesgo',
            'Segmento de Mercado',
            'Forma de Pago',
            'Descuento',
            'Cobertura',
            'Referencias Comerciales',
            'Nivel de Precios',
            'Valores Agregados',
            'Puntaje Forma de Pago',
            'Puntaje Referencias',
            'Puntaje Descuento',
            'Puntaje Cobertura',
            'Puntaje Valores Agregados',
            'Puntaje Precios',
            'Puntaje Criterios Técnicos',
            'Puntaje Total',
            'Estado',
            'Fecha de Creación',
            'Fecha de Actualización'
        ];
    }

    /**
     * @param mixed $row
     *
     * @return array
     */
    public function map($row): array
    {
        return [
            $row->id,
            $row->nombre,
            $row->nit, // NIT as string (formatting will be applied via columnFormats)
            $row->direccion,
            $row->ciudad,
            $row->telefono,
            $row->email,
            $row->persona_contacto,
            $row->servicio_producto,
            $row->proveedor_critico ? 'Sí' : 'No',
            $row->alto_riesgo ? 'Sí' : 'No',
            $row->market_segment,
            $row->forma_pago,
            $row->descuento . '%',
            $row->cobertura == 1 ? '1 Ciudad' : ($row->cobertura == 2 ? '2-3 Ciudades' : '4+ Ciudades'),
            $row->referencias_comerciales . ' Conceptos',
            $row->nivel_precios == 'alto' ? 'Precios Altos' : ($row->nivel_precios == 'promedio' ? 'Promedio Mercado' : 'Precios Bajos'),
            $row->valores_agregados,
            $row->puntaje_forma_pago,
            $row->puntaje_referencias,
            $row->puntaje_descuento,
            $row->puntaje_cobertura,
            $row->puntaje_valores_agregados,
            $row->puntaje_precios,
            $row->puntaje_criterios_tecnicos,
            number_format($row->puntaje_total, 2),
            $row->estado,
            $row->created_at ? $row->created_at->format('d/m/Y') : '',
            $row->updated_at ? $row->updated_at->format('d/m/Y') : ''
        ];
    }

    /**
     * Apply column formatting
     *
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            'C' => NumberFormat::FORMAT_TEXT, // NIT as text
            'F' => NumberFormat::FORMAT_TEXT, // Teléfono as text
            'Z' => NumberFormat::FORMAT_NUMBER_00, // Puntaje Total con 2 decimales
            'AB' => NumberFormat::FORMAT_DATE_DDMMYYYY, // Fecha creación
            'AC' => NumberFormat::FORMAT_DATE_DDMMYYYY, // Fecha actualización
        ];
    }

    /**
     * Set column widths
     *
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 8,      // ID
            'B' => 30,     // Nombre
            'C' => 15,     // NIT
            'D' => 30,     // Dirección
            'E' => 15,     // Ciudad
            'F' => 15,     // Teléfono
            'G' => 25,     // Email
            'H' => 25,     // Persona contacto
            'I' => 40,     // Servicio/Producto
            'J' => 15,     // Proveedor Crítico
            'K' => 15,     // Alto Riesgo
            'L' => 25,     // Segmento de Mercado
            'M' => 15,     // Forma de Pago
            'N' => 12,     // Descuento
            'O' => 15,     // Cobertura
            'P' => 22,     // Referencias Comerciales
            'Q' => 20,     // Nivel de Precios
            'R' => 30,     // Valores Agregados
            'S' => 15,     // Puntaje Forma Pago
            'T' => 15,     // Puntaje Referencias
            'U' => 15,     // Puntaje Descuento
            'V' => 15,     // Puntaje Cobertura
            'W' => 15,     // Puntaje Valores Agregados
            'X' => 15,     // Puntaje Precios
            'Y' => 15,     // Puntaje Criterios Técnicos
            'Z' => 15,     // Puntaje Total
            'AA' => 15,    // Estado
            'AB' => 15,    // Fecha Creación
            'AC' => 15,    // Fecha Actualización
        ];
    }

    /**
     * Styling for the worksheet
     *
     * @param Worksheet $sheet
     */
    public function styles(Worksheet $sheet)
    {
        // Estilo para cabeceras
        $sheet->getStyle('A1:AC1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '364E76'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Altura de la fila de encabezado
        $sheet->getRowDimension(1)->setRowHeight(25);
        
        return [
            // Estilo para todas las celdas
            'A2:AC' . ($sheet->getHighestRow()) => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Estilo para las celdas con puntajes
            'S2:Z' . ($sheet->getHighestRow()) => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
            // Estilo para las celdas de estado y fechas
            'AA2:AC' . ($sheet->getHighestRow()) => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * After sheet events to format the data as a table and set cell data types
     * 
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn();
                
                // Format as a proper Excel table
                $tableRange = 'A1:' . $lastColumn . $lastRow;
                $sheet->setAutoFilter($tableRange);
                
                // Apply zebra striping (alternating row colors)
                for ($row = 2; $row <= $lastRow; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle('A'.$row.':'.$lastColumn.$row)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F5F5F5'],
                            ],
                        ]);
                    }
                }
                
                // Ensure NIT is treated as text by setting the cell data type
                for ($row = 2; $row <= $lastRow; $row++) {
                    $sheet->getCell('C'.$row)->setValueExplicit(
                        $sheet->getCell('C'.$row)->getValue(), 
                        DataType::TYPE_STRING
                    );
                }
                
                // Freeze the header row
                $sheet->freezePane('A2');
            },
        ];
    }
}
