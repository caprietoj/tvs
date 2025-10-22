<?php

namespace App\Exports;

use App\Models\PurchaseOrder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class PurchaseOrdersExport implements FromQuery, WithHeadings, WithMapping, WithStyles, 
    WithColumnWidths, WithTitle, WithEvents
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        $query = PurchaseOrder::with(['purchaseRequest', 'provider'])
            ->orderBy('created_at', 'desc');

        // Aplicar filtros si existen
        if (!empty($this->filters['order_number'])) {
            $query->where('order_number', 'like', '%' . $this->filters['order_number'] . '%');
        }

        if (!empty($this->filters['request_number'])) {
            $query->whereHas('purchaseRequest', function ($q) {
                $q->where('request_number', 'like', '%' . $this->filters['request_number'] . '%');
            });
        }

        if (!empty($this->filters['provider_name'])) {
            $query->whereHas('provider', function ($q) {
                $q->where('nombre', 'like', '%' . $this->filters['provider_name'] . '%');
            });
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        return $query;
    }

    /**
     * Set the title of the worksheet
     *
     * @return string
     */
    public function title(): string
    {
        return 'Órdenes de Compra';
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Número de Orden',
            'Solicitud',
            'Proveedor',
            'Monto',
            'Fecha de Entrega',
            'Creado',
        ];
    }

    /**
     * @param mixed $order
     * @return array
     */
    public function map($order): array
    {
        return [
            $order->order_number,
            $order->purchaseRequest ? $order->purchaseRequest->request_number : 'N/A',
            $order->provider ? $order->provider->nombre : 'N/A',
            '$' . number_format($order->total_amount, 2, ',', '.'),
            $order->delivery_date ? $order->delivery_date->format('d/m/Y') : 'N/A',
            $order->created_at->format('d/m/Y H:i'),
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para la cabecera
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '364E76'],
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
            ],
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 20,  // Número de Orden
            'B' => 18,  // Solicitud
            'C' => 35,  // Proveedor
            'D' => 18,  // Monto
            'E' => 18,  // Fecha de Entrega
            'F' => 20,  // Creado
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Obtener la última fila con datos
                $highestRow = $sheet->getHighestRow();
                
                // Aplicar bordes a todas las celdas con datos
                $sheet->getStyle('A1:F' . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                
                // Alineación central para todas las celdas excepto proveedor
                $sheet->getStyle('A2:B' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('D2:F' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Alineación izquierda para proveedor
                $sheet->getStyle('C2:C' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                
                // Aplicar color de fondo alternado a las filas
                for ($row = 2; $row <= $highestRow; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F8F9FA'],
                            ],
                        ]);
                    }
                }
                
                // Congelar la primera fila (encabezados)
                $sheet->freezePane('A2');
            },
        ];
    }
}
