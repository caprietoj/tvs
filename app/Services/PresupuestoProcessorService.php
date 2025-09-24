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

    // Mapeo específico para centros de costo que requieren clasificación especial
    private $specificCenterMapping = [
        // CAPACITACION BACHILLERATO INTERNACIONAL
        '130101' => 'CAPACITACION PREESCOLAR',
        '130102' => 'CAPACITACION PRIMARIA',
        '130103' => 'CAPACITACION MEDIA',
        '130104' => 'CAPACITACION ALTA',
        
        // CAPACITACIONES OTROS
        '1302' => 'CAPACITACIONES OTROS',
        '130201' => 'CAPACITACIONES OTROS PREESCOLAR',
        '130202' => 'CAPACITACIONES OTROS PRIMARIA',
        '130203' => 'CAPACITACIONES OTROS MEDIA',
        '130204' => 'CAPACITACIONES OTROS ALTA',
        '130205' => 'CAPACITACIONES OTROS PEP',
        
        // MATERIAL IMPORTADO
        '130301' => 'MATERIAL IMPORTADO PREESCOLAR',
        '130302' => 'MATERIAL IMPORTADO PRIMARIA',
        '130303' => 'MATERIAL IMPORTADO MEDIA',
        '130304' => 'MATERIAL IMPORTADO ALTA',
        '130305' => 'MATERIAL IMPORTADO PEP',
        
        // BIBLIOTECA
        '1304' => 'BIBLIOTECA',
        '130401' => 'BIBLIOTECA',
        '130402' => 'BIBLIOTECA',
        '130403' => 'BIBLIOTECA',
        '130404' => 'BIBLIOTECA',
        '130405' => 'BIBLIOTECA',
        '130406' => 'BIBLIOTECA',
        
        // MATERIALES
        '1305' => 'MATERIALES',
        '130501' => 'MATERIALES PREESCOLAR',
        '130502' => 'MATERIALES PRIMARIA',
        '130503' => 'MATERIALES MEDIA',
        '130504' => 'MATERIALES ALTA',
        
        // DEPORTIVOS/UCB
        '1306' => 'DEPORTIVOS/UCB',
        '130601' => 'DEPORTES',
        '130602' => 'DEPORTES',
        '130603' => 'DEPORTES',
        '130604' => 'DEPORTES',
        '130605' => 'DEPORTES',
        
        // MUSICALES
        '1307' => 'MUSICALES',
        '130701' => 'MUSICALES PREESCOLAR',
        '130702' => 'MUSICALES PRIMARIA',
        '130703' => 'MUSICALES MEDIA',
        '130704' => 'MUSICALES ALTA',
        
        // PART TIME TEACHER/REEMPLAZOS
        '1308' => 'PART TIME TEACHER/ REEMPLAZOS',
        '130801' => 'PART TIME PREESCOLAR',
        '130802' => 'PART TIME PRIMARIA',
        '130803' => 'PART TIME MEDIA',
        '130804' => 'PART TIME ALTA',
        
        // DOTACION
        '1309' => 'DOTACION',
        '130901' => 'DOTACION',
        '130902' => 'DOTACION',
        '130903' => 'DOTACION',
        '130904' => 'DOTACION',
        
        // EXHIBITION PEP
        '1310' => 'EXHIBITION PEP',
        '131001' => 'EXHIBITION PEP PRIMARIA',
        
        // PERSONAL PROYEC PAI
        '1311' => 'PERSONAL PROYEC PAI',
        '131101' => 'PROYECTO PERSONAL',
        
        // CAS/INTERCAS/PROYECTO COMUNITARIO
        '131201' => 'CAS',
        '131202' => 'INTERCAS',
        '131203' => 'PROYECTO COMUNITARIO',
        '131204' => 'MONOGRAFIA',
        
        // PRAE
        '1313' => 'PRAE',
        '131301' => 'BIENESTAR INSTITUCIONAL',
        
        // MODELO NACIONES UNIDAS TVS
        '1314' => 'MODELO NACIONES UNIDAS TVS',
        '131401' => 'MUN TVS',
        
        // MUN OTROS COLEGIOS
        '1315' => 'MUN OTROS COLEGIOS',
        '131501' => 'MUN OTROS COLEGIOS',
        
        // CONSEJERIA UNIVERSITARIA
        '1316' => 'CONSEJERIA UNIVERSITARIA',
        '131601' => 'CONSEJERIA UNIVERSITARIA',
        
        // EXHIBITION DE ARTE
        '1317' => 'EXHIBITION DE ARTE',
        '131701' => 'EXHIBITION DE ARTE',
        
        // PSICOLOGIA INSTITUCIONAL
        '1318' => 'PSICOLOGIA INSTITUCIONAL',
        '131801' => 'PSICOLOGIA INSTITUCIONAL',
        '131802' => 'PSICOLOGIA TUTORIAS',
        '131803' => 'CONSEJERIA ESTUDIANTIL',
        
        // TECNOLOGIA Y AUDIOVISUALES
        '1319' => 'TECNOLOGIA Y AUDIOVISUALES',
        '131901' => 'TECNOLOGIA INSTITUCIONAL',
        '131902' => 'TECNOLOGIA INSTITUCIONAL',
        '131903' => 'TECNOLOGIA INSTITUCIONAL',
        '131904' => 'TECNOLOGIA INSTITUCIONAL',
        
        // EVENTOS Y AGASAJOS
        '132001' => 'EVENTOS Y AGASAJOS PREESCOLAR',
        '132002' => 'EVENTOS Y AGASAJOS PRIMARIA',
        '132003' => 'EVENTOS Y AGASAJOS MEDIA',
        '132004' => 'EVENTOS Y AGASAJOS ALTA',
        '132005' => 'DIRECCION GENERAL',
        
        // CURSO PREICFES
        '1321' => 'CURSO PREICFES',
        '132101' => 'CURSO PREICFES',
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
        
        // Primero: verificar mapeo específico para centros de costo que requieren clasificación especial
        if (isset($this->specificCenterMapping[$centroCosto])) {
            return $this->specificCenterMapping[$centroCosto];
        }
        
        // Segundo: intentar buscar en la base de datos
        try {
            $centroCostoModel = \App\Models\CentroCosto::where('codigo', $centroCosto)->first();
            if ($centroCostoModel && $centroCostoModel->seccion) {
                return $centroCostoModel->seccion->nombre;
            }
        } catch (\Exception $e) {
            // Si hay error con la BD, continuar con mapeo estático
        }
        
        // Tercero: usar mapeo estático por prefijo
        if (strlen($centroCosto) < 2) return 'OTROS';
        
        $prefix = substr($centroCosto, 0, 2);
        
        return $this->sectionMapping[$prefix] ?? 'OTROS';
    }

    /**
     * Método público para obtener el mapeo específico de centros de costo
     * Útil para testing y debugging
     */
    public function getSpecificCenterMapping()
    {
        return $this->specificCenterMapping;
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
                
                // Excluir centro de costo específico durante la importación
                if ($item['centro_costo'] === '12010201') {
                    continue;
                }
                
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
