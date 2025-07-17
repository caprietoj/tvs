<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SystemsSurveyExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    private $results;

    public function __construct($results)
    {
        $this->results = $results;
    }

    public function collection()
    {
        return $this->results;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Dependencia',
            'Tiempos de Respuesta',
            'Efectividad Técnica',
            'Profesionalismo',
            'Comentarios Personal',
            'Estado de Equipos',
            'Comentarios Equipos',
            'Apoyo en Usabilidad',
            'Plataformas de Interacción',
            'Otra Plataforma',
            'Calidad Internet',
            'Problemas de Conectividad',
            'Intervención en Eventos',
            'Comentarios Eventos',
            'Aspectos Destacados',
            'Oportunidades de Mejora',
            'Año',
            'Mes'
        ];
    }

    public function map($result): array
    {
        return [
            $result->response_timestamp->format('d/m/Y H:i:s'),
            $result->dependencia,
            $result->tiempos_respuesta,
            $result->efectividad_tecnica,
            $result->profesionalismo,
            $result->comentarios_personal,
            $result->estado_equipos,
            $result->comentarios_equipos,
            $result->apoyo_usabilidad,
            $result->plataformas_interaccion,
            $result->otra_plataforma,
            $result->calidad_internet,
            $result->problemas_conectividad,
            $result->intervencion_eventos,
            $result->comentarios_eventos,
            $result->aspectos_destacados,
            $result->oportunidades_mejora,
            $result->survey_year,
            $result->getMonthName()
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // Fecha
            'B' => 20,  // Dependencia
            'C' => 15,  // Tiempos de Respuesta
            'D' => 15,  // Efectividad Técnica
            'E' => 15,  // Profesionalismo
            'F' => 30,  // Comentarios Personal
            'G' => 15,  // Estado de Equipos
            'H' => 30,  // Comentarios Equipos
            'I' => 15,  // Apoyo en Usabilidad
            'J' => 20,  // Plataformas de Interacción
            'K' => 15,  // Otra Plataforma
            'L' => 15,  // Calidad Internet
            'M' => 30,  // Problemas de Conectividad
            'N' => 15,  // Intervención en Eventos
            'O' => 30,  // Comentarios Eventos
            'P' => 30,  // Aspectos Destacados
            'Q' => 30,  // Oportunidades de Mejora
            'R' => 10,  // Año
            'S' => 15,  // Mes
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
            'A:S' => ['alignment' => ['wrapText' => true]],
        ];
    }
}
