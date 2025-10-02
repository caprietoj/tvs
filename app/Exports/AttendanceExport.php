<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class AttendanceExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles, WithEvents, ShouldAutoSize
{
    private $records;
    private $mes;

    public function __construct($records, $mes)
    {
        $this->records = $records;
        $this->mes = $mes;
    }

    public function collection()
    {
        return $this->records;
    }

    public function headings(): array
    {
        return [
            'ID Empleado',
            'Nombre y Apellidos',
            'Fecha',
            'Hora de Entrada',
            'Hora de Salida',
            'Departamento',
            'Estado',
            'Minutos de Retraso'
        ];
    }

    public function map($record): array
    {
        // Calcular estado y minutos de retraso
        $estado = 'Ausente';
        $minutosRetraso = 0;

        if (!empty($record->entrada)) {
            try {
                $entrada = null;
                $horaStr = $record->entrada;
                
                $formatos = ['H:i:s', 'H:i', 'h:i:s A', 'h:i A'];
                
                foreach ($formatos as $formato) {
                    try {
                        $entrada = Carbon::createFromFormat($formato, $horaStr);
                        break;
                    } catch (\Exception $e) {
                        continue;
                    }
                }
                
                if (!$entrada) {
                    $entrada = Carbon::parse($horaStr);
                }
                
                $limite = Carbon::createFromFormat('H:i', '07:00');
                
                if ($entrada->gt($limite)) {
                    $estado = 'Tarde';
                    $minutosRetraso = $entrada->diffInMinutes($limite);
                } else {
                    $estado = 'A tiempo';
                }
            } catch (\Exception $e) {
                $estado = 'Ausente';
            }
        }

        return [
            $record->no_id,
            $record->nombre_apellidos,
            $record->fecha ? Carbon::parse($record->fecha)->format('d/m/Y') : 'N/A',
            $record->entrada ?: 'No registrado',
            $record->salida ?: 'No registrado',
            $record->departamento,
            $estado,
            $minutosRetraso > 0 ? $minutosRetraso : ''
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,  // ID Empleado
            'B' => 35,  // Nombre y Apellidos
            'C' => 12,  // Fecha
            'D' => 15,  // Hora de Entrada
            'E' => 15,  // Hora de Salida
            'F' => 25,  // Departamento
            'G' => 15,  // Estado
            'H' => 18,  // Minutos de Retraso
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para el encabezado
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2E86AB'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
            // Bordes para toda la tabla
            'A1:H' . ($this->records->count() + 1) => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ],
            // Centrar columnas específicas
            'A:A' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'C:C' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'D:D' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'E:E' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'G:G' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            'H:H' => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Obtener el rango total de datos (incluye encabezados)
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $range = 'A1:' . $highestColumn . $highestRow;
                
                // Aplicar AutoFiltro a toda la tabla
                $sheet->setAutoFilter($range);
                
                // Congelar la primera fila (encabezados)
                $sheet->freezePane('A2');
                
                // Agregar validación de datos para la columna Estado (G)
                $estadoRange = 'G2:G' . $highestRow;
                $validation = $sheet->getCell('G2')->getDataValidation();
                $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                $validation->setAllowBlank(false);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setShowDropDown(true);
                $validation->setErrorTitle('Valor no válido');
                $validation->setError('Por favor, seleccione un estado válido de la lista.');
                $validation->setPromptTitle('Estados disponibles');
                $validation->setPrompt('Seleccione: A tiempo, Tarde, o Ausente');
                $validation->setFormula1('"A tiempo,Tarde,Ausente"');
                
                // Aplicar la validación a todo el rango de la columna Estado
                $sheet->setDataValidation($estadoRange, $validation);
                
                // Agregar colores condicionales para la columna Estado
                $conditionalStyles = [];
                
                // Verde para "A tiempo"
                $greenStyle = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
                $greenStyle->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CONTAINSTEXT);
                $greenStyle->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_CONTAINSTEXT);
                $greenStyle->setText('A tiempo');
                $greenStyle->getStyle()->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('C6EFCE');
                $greenStyle->getStyle()->getFont()->getColor()->setRGB('006100');
                
                // Amarillo para "Tarde"
                $yellowStyle = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
                $yellowStyle->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CONTAINSTEXT);
                $yellowStyle->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_CONTAINSTEXT);
                $yellowStyle->setText('Tarde');
                $yellowStyle->getStyle()->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFEB9C');
                $yellowStyle->getStyle()->getFont()->getColor()->setRGB('9C6500');
                
                // Rojo para "Ausente"
                $redStyle = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
                $redStyle->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CONTAINSTEXT);
                $redStyle->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_CONTAINSTEXT);
                $redStyle->setText('Ausente');
                $redStyle->getStyle()->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFC7CE');
                $redStyle->getStyle()->getFont()->getColor()->setRGB('9C0006');
                
                // Aplicar estilos condicionales
                $conditionalStyles[] = $greenStyle;
                $conditionalStyles[] = $yellowStyle;
                $conditionalStyles[] = $redStyle;
                
                $sheet->getStyle($estadoRange)->setConditionalStyles($conditionalStyles);
                
                // Establecer altura de filas
                $sheet->getDefaultRowDimension()->setRowHeight(18);
                $sheet->getRowDimension('1')->setRowHeight(25);
                
                // Agregar título en la parte superior
                $sheet->insertNewRowBefore(1, 2);
                $sheet->setCellValue('A1', 'REPORTE DE ASISTENCIA - ' . strtoupper($this->mes));
                $sheet->setCellValue('A2', 'Fecha de generación: ' . now()->format('d/m/Y H:i:s'));
                
                // Estilo para el título
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'color' => ['rgb' => '2E86AB'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
                
                // Estilo para la fecha
                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 10,
                        'color' => ['rgb' => '666666'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
                
                // Combinar celdas para el título
                $sheet->mergeCells('A1:H1');
                $sheet->mergeCells('A2:H2');
                
                // Actualizar el rango del autofiltro después de insertar filas
                $newHighestRow = $sheet->getHighestRow();
                $newRange = 'A3:' . $highestColumn . $newHighestRow;
                $sheet->setAutoFilter($newRange);
                
                // Actualizar el punto de congelamiento
                $sheet->freezePane('A4');
            },
        ];
    }
}