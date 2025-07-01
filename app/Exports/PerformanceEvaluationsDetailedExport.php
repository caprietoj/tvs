<?php

namespace App\Exports;

use App\Models\PerformanceEvaluation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Http\Request;

class PerformanceEvaluationsDetailedExport implements WithMultipleSheets
{
    protected $request;
    protected $user;

    public function __construct(Request $request, $user)
    {
        $this->request = $request;
        $this->user = $user;
    }

    /**
     * @return array
     */
    public function sheets(): array
    {
        return [
            new PerformanceEvaluationsExport($this->request, $this->user),
            new PerformanceEvaluationsSummarySheet($this->request, $this->user),
        ];
    }
}

class PerformanceEvaluationsSummarySheet implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    protected $request;
    protected $user;

    public function __construct(Request $request, $user)
    {
        $this->request = $request;
        $this->user = $user;
    }

    public function collection()
    {
        $query = PerformanceEvaluation::with(['user', 'evaluator']);

        // Aplicar mismos filtros que en el controlador principal
        if ($this->user->hasRole('admin') || $this->user->can('view-all-performance-evaluations')) {
            $query->with(['user', 'evaluator']);
        } else {
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

        return $query->where('status', 'completed')->orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Empleado',
            'Evaluador',
            'Total Evaluaciones',
            'Promedio General',
            'Nivel de Desempeño',
            'Estado Actual',
            'Última Evaluación',
        ];
    }

    public function map($evaluation): array
    {
        $finalScore = 0;
        if ($evaluation->objectives_supervisor_score && $evaluation->competencies_supervisor_score) {
            $finalScore = ($evaluation->objectives_supervisor_score * 0.6) + 
                         ($evaluation->competencies_supervisor_score * 0.4);
        }

        return [
            $evaluation->user->name ?? 'N/A',
            $evaluation->evaluator->name ?? 'No asignado',
            1, // Total evaluaciones (podría expandirse para contar múltiples)
            $finalScore ? number_format($finalScore, 2) : 'N/A',
            $this->getPerformanceLevel($finalScore),
            $this->getStatusLabel($evaluation->status),
            $evaluation->supervisor_evaluation_completed_at ? $evaluation->supervisor_evaluation_completed_at->format('d/m/Y') : 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '28A745'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25,  // Empleado
            'B' => 25,  // Evaluador
            'C' => 18,  // Total Evaluaciones
            'D' => 18,  // Promedio General
            'E' => 20,  // Nivel
            'F' => 20,  // Estado
            'G' => 20,  // Última Evaluación
        ];
    }

    public function title(): string
    {
        return 'Resumen por Empleado';
    }

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

    private function getPerformanceLevel($score)
    {
        if ($score >= 4.5) {
            return 'Supera las expectativas de desempeño';
        } elseif ($score >= 3.5) {
            return 'Buen desempeño con caracteristicas proactivas';
        } elseif ($score >= 2.5) {
            return 'Cumple con lo establecido sin proactividad';
        } elseif ($score >= 1.5) {
            return 'Aceptable';
        } elseif ($score > 0) {
            return 'No cumple';
        } else {
            return 'Sin evaluar';
        }
    }
}
