<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class PresupuestoProcessorService
{
    private $sectionMapping = [
        '11' => 'PREESCOLAR Y PRIMARIA',
        '12' => 'ESCUELA MEDIA', 
        '13' => 'ALTA',
        '01' => 'ADMINISTRACION',
        '02' => 'ADMINISTRACION',
        '03' => 'DIRECCION GENERAL',
        '04' => 'BIBLIOTECA',
        '05' => 'DEPORTES',
        '06' => 'CAS',
        '07' => 'PAI',
        '08' => 'PEP',
        '09' => 'PSICOLOGIA INSTITUCIONAL',
        '15' => 'TECNOLOGIA INSTITUCIONAL',
    ];

    private $rubroMapping = [
        '616005109' => 'Capacitación',
        '616005056' => 'Capacitación',
        '616005356' => 'Material Importado',
        '616005452' => 'Material Importado',
        '616005959' => 'Material Importado',
        '616005103' => 'Servicios Profesionales',
        '616005202' => 'Servicios Básicos',
        '616005350' => 'Servicios Generales',
        '51053' => 'Nómina',
        '51054' => 'Nómina',
        '51056' => 'Prestaciones',
        '5105' => 'Gastos de Personal',
        '6160' => 'Gastos Operacionales',
        '6165' => 'Mantenimiento',
        '6170' => 'Seguros',
        '6175' => 'Servicios',
        '6180' => 'Impuestos',
    ];

    public function determineSection($centroCosto)
    {
        if (empty($centroCosto)) return 'SIN_ASIGNAR';
        
        $centroCosto = trim((string)$centroCosto);
        
        // Intentar buscar en la base de datos primero
        try {
            $centroCostoModel = \App\Models\CentroCosto::where('codigo', $centroCosto)->first();
            if ($centroCostoModel && $centroCostoModel->seccion) {
                return $centroCostoModel->seccion->nombre;
            }
        } catch (\Exception $e) {
            // Si hay error con la BD, usar mapeo estático
        }
        
        // Fallback: usar mapeo estático
        if (strlen($centroCosto) < 2) return 'OTROS';
        
        $prefix = substr($centroCosto, 0, 2);
        
        return $this->sectionMapping[$prefix] ?? 'OTROS';
    }

    public function determineRubro($cuenta)
    {
        if (empty($cuenta)) return 'Sin Clasificar';
        
        $cuenta = trim((string)$cuenta);
        
        // Intentar buscar en la base de datos primero
        try {
            $cuentaModel = \App\Models\Cuenta::where('codigo', $cuenta)->first();
            if ($cuentaModel && $cuentaModel->rubro) {
                return $cuentaModel->rubro->nombre;
            }
        } catch (\Exception $e) {
            // Si hay error con la BD, usar mapeo estático
        }
        
        // Fallback: usar mapeo estático
        // Convertir de hexadecimal si es necesario
        if (preg_match('/[A-Fa-f]/', $cuenta)) {
            try {
                $cuenta = (string) hexdec($cuenta);
            } catch (Exception $e) {
                // Mantener valor original si no se puede convertir
            }
        }
        
        // Buscar coincidencias de mayor a menor precisión
        foreach ([9, 8, 7, 6, 5, 4] as $length) {
            if (strlen($cuenta) >= $length) {
                $prefix = substr($cuenta, 0, $length);
                if (isset($this->rubroMapping[$prefix])) {
                    return $this->rubroMapping[$prefix];
                }
            }
        }
        
        return 'Sin Clasificar';
    }

    public function processExcelData($filePath)
    {
        try {
            // Leer el archivo Excel
            $data = Excel::toArray(new PresupuestoImport, $filePath)[0];
            
            $processedData = [];
            $sectionTotals = [];
            $processedCount = 0;

            foreach ($data as $index => $row) {
                // Saltar la primera fila (encabezados)
                if ($index === 0) continue;
                
                // Saltar filas completamente vacías
                if ($this->isEmptyRow($row)) continue;
                
                $item = [
                    'fuente' => $this->cleanValue($row[0] ?? null),
                    'documento' => $this->cleanValue($row[1] ?? null),
                    'fecha' => $this->parseDate($row[2] ?? null),
                    'cuenta' => $this->cleanValue($row[3] ?? null),
                    'descripcion' => $this->cleanValue($row[4] ?? null),
                    'valor' => $this->parseFloat($row[5] ?? 0),
                    'valor_moneda' => $this->parseFloat($row[6] ?? 0),
                    'cliente_proveedor' => $this->cleanValue($row[7] ?? null),
                    'nombre_cliente_proveedor' => $this->cleanValue($row[8] ?? null),
                    'tercero' => $this->cleanValue($row[9] ?? null),
                    'nombre_tercero' => $this->cleanValue($row[10] ?? null),
                    'auxiliar' => $this->cleanValue($row[11] ?? null),
                    'centro_costo' => $this->cleanValue($row[12] ?? null),
                    'es_total' => false,
                ];
                
                // Clasificar sección y rubro
                $item['seccion'] = $this->determineSection($item['centro_costo']);
                $item['rubro'] = $this->determineRubro($item['cuenta']);
                
                $processedData[] = $item;
                $processedCount++;
                
                // Acumular totales por sección
                if (!isset($sectionTotals[$item['seccion']])) {
                    $sectionTotals[$item['seccion']] = 0;
                }
                $sectionTotals[$item['seccion']] += $item['valor'];
            }

            // Agrupar por sección y agregar totales
            $result = $this->groupBySectionAndAddTotals($processedData, $sectionTotals);
            
            return [
                'data' => $result,
                'statistics' => [
                    'total_rows_processed' => $processedCount,
                    'total_sections' => count($sectionTotals),
                    'total_value' => array_sum($sectionTotals),
                    'section_totals' => $sectionTotals
                ]
            ];
            
        } catch (Exception $e) {
            throw new Exception("Error procesando archivo Excel: " . $e->getMessage());
        }
    }

    private function groupBySectionAndAddTotals($data, $sectionTotals)
    {
        // Agrupar datos por sección
        $groupedData = [];
        foreach ($data as $item) {
            $section = $item['seccion'];
            if (!isset($groupedData[$section])) {
                $groupedData[$section] = [];
            }
            $groupedData[$section][] = $item;
        }

        // Construir resultado final con totales
        $result = [];
        
        foreach ($groupedData as $section => $items) {
            // Agregar elementos de la sección
            foreach ($items as $item) {
                $result[] = $item;
            }
            
            // Agregar fila de total para la sección
            $result[] = [
                'fuente' => null,
                'documento' => null,
                'fecha' => null,
                'cuenta' => null,
                'descripcion' => "TOTAL {$section}",
                'valor' => $sectionTotals[$section],
                'valor_moneda' => 0,
                'cliente_proveedor' => null,
                'nombre_cliente_proveedor' => null,
                'tercero' => null,
                'nombre_tercero' => null,
                'auxiliar' => null,
                'centro_costo' => null,
                'seccion' => $section,
                'rubro' => 'TOTAL',
                'es_total' => true,
            ];
            
            // Agregar fila en blanco para separación
            $result[] = $this->createEmptyRow();
        }
        
        return $result;
    }

    private function createEmptyRow()
    {
        return [
            'fuente' => null,
            'documento' => null,
            'fecha' => null,
            'cuenta' => null,
            'descripcion' => null,
            'valor' => 0,
            'valor_moneda' => 0,
            'cliente_proveedor' => null,
            'nombre_cliente_proveedor' => null,
            'tercero' => null,
            'nombre_tercero' => null,
            'auxiliar' => null,
            'centro_costo' => null,
            'seccion' => null,
            'rubro' => null,
            'es_total' => false,
        ];
    }

    private function isEmptyRow($row)
    {
        return empty(array_filter($row, function($value) {
            return !empty(trim((string)$value));
        }));
    }

    private function cleanValue($value)
    {
        if (is_null($value)) return null;
        $cleaned = trim((string)$value);
        return empty($cleaned) ? null : $cleaned;
    }

    private function parseDate($dateValue)
    {
        if (empty($dateValue)) return null;
        
        try {
            // Si es un número (Excel date serial)
            if (is_numeric($dateValue)) {
                // Excel fecha base: 1900-01-01
                $excelBaseDate = Carbon::create(1900, 1, 1);
                return $excelBaseDate->addDays($dateValue - 2)->format('Y-m-d');
            }
            
            // Si es una fecha en formato string
            return Carbon::parse($dateValue)->format('Y-m-d');
            
        } catch (Exception $e) {
            return null;
        }
    }

    private function parseFloat($value)
    {
        if (is_null($value) || $value === '') return 0;
        
        // Limpiar el valor de caracteres no numéricos excepto punto y coma
        $cleaned = preg_replace('/[^0-9.,-]/', '', (string)$value);
        
        // Reemplazar coma por punto para decimales
        $cleaned = str_replace(',', '.', $cleaned);
        
        return floatval($cleaned);
    }
}

// Clase simple para importar Excel
class PresupuestoImport
{
    // Esta clase la usará Laravel Excel para leer el archivo
}
