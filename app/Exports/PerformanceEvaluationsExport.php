<?php

namespace App\Exports;

use App\Models\PerformanceEvaluation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Http\Request;

class PerformanceEvaluationsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $request;
    protected $user;

    public function __construct(Request $request, $user)
    {
        $this->request = $request;
        $this->user = $user;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = PerformanceEvaluation::with(['user', 'evaluator']);

        // Aplicar mismos filtros que en el controlador
        if ($this->user->hasRole('admin') || $this->user->can('view-all-performance-evaluations')) {
            // Los admins pueden ver todas las evaluaciones
            $query->with(['user', 'evaluator']);
        } else {
            // Los empleados y supervisores pueden ver sus propias evaluaciones y las que deben evaluar
            $query->where(function($q) {
                $q->where('evaluator_id', $this->user->id)
                  ->orWhere('user_id', $this->user->id);
            })->with(['user', 'evaluator']);
        }

        // Aplicar filtros de la request
        if ($this->request->filled('status')) {
            $query->where('status', $this->request->status);
        }

        if ($this->request->filled('type')) {
            $query->where('evaluation_type', $this->request->type);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Encabezados del Excel
     */
    public function headings(): array
    {
        return [
            'ID',
            'Empleado',
            'Email Empleado',
            'Evaluador',
            'Email Evaluador',
            'Tipo de Evaluación',
            'Período Inicio',
            'Período Fin',
            'Estado',
            'Puntaje Autoevaluación Objetivos',
            'Puntaje Autoevaluación Competencias',
            'Puntaje Supervisor Objetivos',
            'Puntaje Supervisor Competencias',
            'Puntaje Final',
            'Nivel de Desempeño',
            'Observaciones Empleado',
            'Observaciones Supervisor',
            'Fecha Creación',
            'Fecha Autoevaluación',
            'Fecha Evaluación Supervisor',
        ];
    }

    /**
     * Mapear datos para cada fila
     */
    public function map($evaluation): array
    {
        // Calcular puntaje final y nivel
        $finalScore = 0;
        $level = 'Sin calificar';

        if ($evaluation->objectives_supervisor_score && $evaluation->competencies_supervisor_score) {
            $finalScore = ($evaluation->objectives_supervisor_score * 0.6) + 
                         ($evaluation->competencies_supervisor_score * 0.4);
            $level = $this->getPerformanceLevel($finalScore);
        } elseif ($evaluation->objectives_self_score && $evaluation->competencies_self_score) {
            $finalScore = ($evaluation->objectives_self_score * 0.6) + 
                         ($evaluation->competencies_self_score * 0.4);
            $level = $this->getPerformanceLevel($finalScore) . ' (Autoevaluación)';
        }

        return [
            $evaluation->id,
            $evaluation->user->name ?? 'N/A',
            $evaluation->user->email ?? 'N/A',
            $evaluation->evaluator->name ?? 'No asignado',
            $evaluation->evaluator->email ?? 'N/A',
            $evaluation->evaluation_type === 'periodo_prueba' ? 'Período de Prueba' : 'Periódica',
            $evaluation->evaluation_period_start ? $evaluation->evaluation_period_start->format('d/m/Y') : 'N/A',
            $evaluation->evaluation_period_end ? $evaluation->evaluation_period_end->format('d/m/Y') : 'N/A',
            $this->getStatusLabel($evaluation->status),
            $evaluation->objectives_self_score ? number_format($evaluation->objectives_self_score, 2) : 'N/A',
            $evaluation->competencies_self_score ? number_format($evaluation->competencies_self_score, 2) : 'N/A',
            $evaluation->objectives_supervisor_score ? number_format($evaluation->objectives_supervisor_score, 2) : 'N/A',
            $evaluation->competencies_supervisor_score ? number_format($evaluation->competencies_supervisor_score, 2) : 'N/A',
            $finalScore ? number_format($finalScore, 2) : 'N/A',
            $level,
            $evaluation->self_observations ?? 'N/A',
            $evaluation->supervisor_observations ?? 'N/A',
            $evaluation->created_at ? $evaluation->created_at->format('d/m/Y H:i') : 'N/A',
            $evaluation->self_evaluation_completed_at ? $evaluation->self_evaluation_completed_at->format('d/m/Y H:i') : 'N/A',
            $evaluation->supervisor_evaluation_completed_at ? $evaluation->supervisor_evaluation_completed_at->format('d/m/Y H:i') : 'N/A',
        ];
    }

    /**
     * Aplicar estilos al Excel
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para los encabezados
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * Ancho de columnas
     */
    public function columnWidths(): array
    {
        return [
            'A' => 8,   // ID
            'B' => 25,  // Empleado
            'C' => 30,  // Email Empleado
            'D' => 25,  // Evaluador
            'E' => 30,  // Email Evaluador
            'F' => 20,  // Tipo
            'G' => 15,  // Período Inicio
            'H' => 15,  // Período Fin
            'I' => 20,  // Estado
            'J' => 15,  // Puntaje Auto Objetivos
            'K' => 20,  // Puntaje Auto Competencias
            'L' => 20,  // Puntaje Sup Objetivos
            'M' => 25,  // Puntaje Sup Competencias
            'N' => 15,  // Puntaje Final
            'O' => 20,  // Nivel
            'P' => 30,  // Observaciones Empleado
            'Q' => 30,  // Observaciones Supervisor
            'R' => 20,  // Fecha Creación
            'S' => 20,  // Fecha Autoevaluación
            'T' => 25,  // Fecha Evaluación Supervisor
        ];
    }

    /**
     * Título de la hoja
     */
    public function title(): string
    {
        return 'Evaluaciones de Desempeño';
    }

    /**
     * Obtener etiqueta del estado
     */
    private function getStatusLabel($status)
    {
        $statuses = [
            'draft' => 'Borrador',
            'self_completed' => 'Autoevaluación Completada',
            'supervisor_review' => 'En Revisión Supervisor',
            'completed' => 'Completada',
        ];

        return $statuses[$status] ?? $status;
    }

    /**
     * Obtener nivel de desempeño
     */
    private function getPerformanceLevel($score)
    {
        if ($score >= 4.5) {
            return 'Excelente';
        } elseif ($score >= 3.5) {
            return 'Bueno';
        } elseif ($score >= 2.5) {
            return 'Satisfactorio';
        } else {
            return 'Necesita Mejora';
        }
    }
}
