<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Storage;
use App\Models\ComprasKpi;
use App\Models\ComprasThreshold;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SatisfactionSurveyController extends Controller
{
    private $satisfactionMappings = [
        'MUY SATISFECHO' => 5,
        'SATISFECHO' => 4,
        'NEUTRAL' => 3,
        'POCO SATISFECHO' => 2,
        'INSATISFECHO' => 1,
        'SÍ' => 5,
        'NO' => 1,
        'A VECES' => 3
    ];

    public function processExcel(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'survey_data' => 'required|string',
                'threshold_id' => 'required|exists:compras_thresholds,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ]);
            }

            $rows = explode("\n", str_replace("\r", "", $request->survey_data));
            
            $areaData = [
                'estudiantes' => ['responses' => [], 'count' => 0],
                'administrativos' => ['responses' => [], 'count' => 0],
                'docentes' => ['responses' => [], 'count' => 0]
            ];

            foreach ($rows as $row) {
                if (empty(trim($row))) continue;
                
                $data = str_getcsv($row, "\t");
                if (count($data) < 6) continue;

                $area = $this->normalizeArea(strtolower($data[0]));
                if (!isset($areaData[$area])) continue;

                $responses = array_slice($data, 1, 5);
                $score = $this->calculateResponseScore($responses);
                
                if ($score > 0) {
                    $areaData[$area]['responses'][] = $score;
                    $areaData[$area]['count']++;
                }
            }

            $stats = $this->calculateStatistics($areaData);
            $threshold = ComprasThreshold::find($request->threshold_id);
            
            $kpi = ComprasKpi::create([
                'name' => 'Satisfacción al Cliente',
                'type' => 'measurement',
                'percentage' => $stats['total']['average'],
                'threshold_id' => $threshold->id,
                'is_achieved' => $stats['total']['average'] >= $threshold->value,
                'measurement_date' => now(),
                'area' => 'compras',
                'methodology' => 'Encuesta de satisfacción al cliente',
                'frequency' => 'Mensual'
            ]);

            $filename = $this->generateDetailedResultsExcel($stats, $kpi, $areaData);

            return response()->json([
                'success' => true,
                'percentage' => $stats['total']['average'],
                'stats' => $stats,
                'filename' => $filename
            ]);

        } catch (\Exception $e) {
            Log::error('Error procesando datos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar los datos: ' . $e->getMessage()
            ]);
        }
    }

    private function normalizeArea($area)
    {
        $area = strtolower(trim($area));
        
        if (str_contains($area, 'estudiante')) {
            return 'estudiantes';
        }
        if (str_contains($area, 'docente') || str_contains($area, 'profesor')) {
            return 'docentes';
        }
        if (str_contains($area, 'administrativo') || str_contains($area, 'admin')) {
            return 'administrativos';
        }
        
        return 'otros';
    }

    private function calculateResponseScore($responses)
    {
        $total = 0;
        $count = 0;

        foreach ($responses as $response) {
            $response = trim(strtoupper($response));
            if (isset($this->satisfactionMappings[$response])) {
                $total += $this->satisfactionMappings[$response];
                $count++;
            }
        }

        if ($count === 0) {
            return 0;
        }

        return ($total / ($count * 5)) * 100;
    }

    private function calculateStatistics($areaData)
    {
        $stats = [
            'areas' => [],
            'total' => [
                'responses' => 0,
                'average' => 0
            ]
        ];

        $totalScore = 0;
        $totalResponses = 0;

        foreach ($areaData as $area => $data) {
            if (count($data['responses']) > 0) {
                $areaAverage = array_sum($data['responses']) / count($data['responses']);
                $stats['areas'][$area] = [
                    'count' => $data['count'],
                    'average' => $areaAverage
                ];
                $totalScore += array_sum($data['responses']);
                $totalResponses += count($data['responses']);
            }
        }

        $stats['total']['responses'] = $totalResponses;
        $stats['total']['average'] = $totalResponses > 0 ? $totalScore / $totalResponses : 0;

        return $stats;
    }

    private function generateDetailedResultsExcel($stats, $kpi, $areaData)
    {
        $filename = 'satisfaccion_cliente_' . date('Y-m-d_His') . '.csv';
        $path = storage_path('app/public/reports/' . $filename);
        
        if (!file_exists(storage_path('app/public/reports'))) {
            mkdir(storage_path('app/public/reports'), 0777, true);
        }

        $file = fopen($path, 'w');
        
        // Write headers
        fputcsv($file, ['Reporte de Satisfacción al Cliente']);
        fputcsv($file, []);
        fputcsv($file, ['Fecha de medición:', $kpi->measurement_date->format('d/m/Y')]);
        fputcsv($file, ['KPI alcanzado:', $kpi->is_achieved ? 'SÍ' : 'NO']);
        fputcsv($file, []);
        
        // Write area results
        fputcsv($file, ['Resultados por Área']);
        fputcsv($file, ['Área', 'Total Encuestas', 'Porcentaje de Satisfacción']);
        
        foreach ($stats['areas'] as $area => $data) {
            fputcsv($file, [
                ucfirst($area),
                $data['count'],
                number_format($data['average'], 2) . '%'
            ]);
        }
        
        fputcsv($file, []);
        
        // Write totals
        fputcsv($file, ['Resultados Consolidados']);
        fputcsv($file, ['Total de encuestas:', $stats['total']['responses']]);
        fputcsv($file, ['Porcentaje general:', number_format($stats['total']['average'], 2) . '%']);
        
        fclose($file);
        
        return $filename;
    }
}
