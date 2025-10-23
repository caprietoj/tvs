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
            'Producto/Servicio Solicitado',
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
        // Obtener el concepto/descripción de la solicitud
        $concept = $this->getRequestConcept($order->purchaseRequest);
        
        return [
            $order->order_number,
            $order->purchaseRequest ? $order->purchaseRequest->request_number : 'N/A',
            $concept,
            $order->provider ? $order->provider->nombre : 'N/A',
            '$' . number_format($order->total_amount, 2, ',', '.'),
            $order->delivery_date ? $order->delivery_date->format('d/m/Y') : 'N/A',
            $order->created_at->format('d/m/Y H:i'),
        ];
    }

    /**
     * Obtener el concepto o descripción de la solicitud
     */
    private function getRequestConcept($purchaseRequest)
    {
        if (!$purchaseRequest) {
            return 'N/A';
        }

        // Para solicitudes de compra (purchase)
        if ($purchaseRequest->type === 'purchase') {
            if ($purchaseRequest->purchase_justification) {
                return \Illuminate\Support\Str::limit($purchaseRequest->purchase_justification, 100);
            }
            // Si no hay justificación, intentar obtener de los items
            if ($purchaseRequest->purchase_items) {
                $items = is_string($purchaseRequest->purchase_items) 
                    ? json_decode($purchaseRequest->purchase_items, true) 
                    : $purchaseRequest->purchase_items;
                
                if (is_array($items) && count($items) > 0) {
                    $descriptions = array_column($items, 'description');
                    return \Illuminate\Support\Str::limit(implode(', ', array_filter($descriptions)), 100);
                }
            }
        }

        // Para solicitudes de servicios (services)
        if ($purchaseRequest->type === 'services') {
            if ($purchaseRequest->service_justification) {
                return \Illuminate\Support\Str::limit($purchaseRequest->service_justification, 100);
            }
            // Si no hay justificación, intentar obtener de los items
            if ($purchaseRequest->service_items) {
                $items = is_string($purchaseRequest->service_items) 
                    ? json_decode($purchaseRequest->service_items, true) 
                    : $purchaseRequest->service_items;
                
                if (is_array($items) && count($items) > 0) {
                    $descriptions = array_column($items, 'description');
                    return \Illuminate\Support\Str::limit(implode(', ', array_filter($descriptions)), 100);
                }
            }
        }

        // Para solicitudes de materiales o fotocopias
        if ($purchaseRequest->type === 'materials') {
            $descriptions = [];
            
            // Fotocopias
            if ($purchaseRequest->copy_items) {
                $items = is_string($purchaseRequest->copy_items) 
                    ? json_decode($purchaseRequest->copy_items, true) 
                    : $purchaseRequest->copy_items;
                
                if (is_array($items) && count($items) > 0) {
                    return 'Fotocopias (' . count($items) . ' documentos)';
                }
            }
            
            // Materiales
            if ($purchaseRequest->material_items) {
                $items = is_string($purchaseRequest->material_items) 
                    ? json_decode($purchaseRequest->material_items, true) 
                    : $purchaseRequest->material_items;
                
                if (is_array($items) && count($items) > 0) {
                    $itemDescriptions = array_column($items, 'description');
                    return \Illuminate\Support\Str::limit(implode(', ', array_filter($itemDescriptions)), 100);
                }
            }
        }

        return 'Sin descripción';
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
            'C' => 50,  // Producto/Servicio Solicitado
            'D' => 35,  // Proveedor
            'E' => 18,  // Monto
            'F' => 18,  // Fecha de Entrega
            'G' => 20,  // Creado
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
                $sheet->getStyle('A1:G' . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                
                // Alineación central para todas las celdas excepto descripción y proveedor
                $sheet->getStyle('A2:B' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E2:G' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Alineación izquierda para descripción y proveedor
                $sheet->getStyle('C2:D' . $highestRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                
                // Aplicar color de fondo alternado a las filas
                for ($row = 2; $row <= $highestRow; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle('A' . $row . ':G' . $row)->applyFromArray([
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
