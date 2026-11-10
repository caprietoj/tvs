<?php

namespace App\Exports;

use App\Models\RegistroPorteria;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class PorteriaExport implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $mesTexto;

    public function __construct($startDate, $endDate, $mesTexto)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->mesTexto = $mesTexto;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return RegistroPorteria::whereBetween('fecha', [$this->startDate, $this->endDate])
            ->orderBy('fecha', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Fecha',
            'Documento',
            'Nombre Completo',
            'Tipo de Persona',
            'Tipo de Registro',
            'Hora Entrada',
            'Hora Salida',
            'Estado',
            'Tiempo Permanencia (Horas)',
            'Día de la Semana'
        ];
    }

    /**
     * @param mixed $registro
     * @return array
     */
    public function map($registro): array
    {
        $horaEntrada = $registro->hora_entrada ? Carbon::parse($registro->hora_entrada) : null;
        $horaSalida = $registro->hora_salida ? Carbon::parse($registro->hora_salida) : null;
        
        // Calcular tiempo de permanencia
        $tiempoPermanencia = '';
        if ($horaEntrada && $horaSalida) {
            $diferencia = $horaEntrada->diffInMinutes($horaSalida);
            $horas = intval($diferencia / 60);
            $minutos = $diferencia % 60;
            $tiempoPermanencia = $horas . 'h ' . $minutos . 'm';
        }

        // Estado según el movimiento registrado
        if (!$horaEntrada && $horaSalida) {
            $estado = 'Salida registrada';
        } elseif ($horaEntrada && $horaSalida) {
            $estado = 'Completado';
        } elseif ($horaEntrada) {
            $estado = 'Dentro';
        } else {
            $estado = 'Sin registro';
        }

        return [
            Carbon::parse($registro->fecha)->format('d/m/Y'),
            $registro->documento,
            $registro->nombre,
            $registro->tipo_persona ?? 'No especificado',
            $registro->tipo_registro === 'salida' ? 'Salida' : 'Entrada',
            $horaEntrada ? $horaEntrada->format('H:i:s') : '',
            $horaSalida ? $horaSalida->format('H:i:s') : '',
            $estado,
            $tiempoPermanencia,
            Carbon::parse($registro->fecha)->locale('es')->dayName
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Registros Portería';
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        // Aplicar filtros automáticos
        $sheet->setAutoFilter('A1:J1');

        // Estilo para el encabezado
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['argb' => Color::COLOR_WHITE],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['argb' => 'FF4472C4']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ]
            ]
        ];
    }
}