<?php

namespace App\Services;

use Exception;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class PresupuestoProcessorService
{
    private $sectionMapping = [
        '130' => 'PREESCOLAR Y PRIMARIA',  // Corregido: 130 en lugar de 11
        '12' => 'ESCUELA MEDIA', 
        '132' => 'ALTA',  // Más específico: 132xxx para eventos y agasajos de alta
        '07' => 'PAI',
        '08' => 'PEP',
        '1304' => 'BIBLIOTECA',  // Más específico: 1304xxx para biblioteca
        '05' => 'DEPORTES',
        '131' => 'CAS',  // 131xxx para CAS/INTERCAS/Proyecto Comunitario
        '15' => 'TECNOLOGIA INSTITUCIONAL',
        '01' => 'GASTOS INSTITUCIONALES',
        '02' => 'GASTOS ADMINISTRATIVOS',
        '03' => 'DIRECCION GENERAL',
        '09' => 'PSICOLOGIA INSTITUCIONAL',
    ];

    // Lista de centros de costo válidos según archivos oficiales
    private $validCostCenters = [
        // CAPACITACION BACHILLERATO INTERNACIONAL
        '130101', '130102', '130103', '130104',
        // CAPACITACIONES OTROS
        '1302', '130201', '130202', '130203', '130204', '130205',
        // MATERIAL IMPORTADO
        '130301', '130302', '130303', '130304', '130305',
        // BIBLIOTECA
        '1304', '130401', '130402', '130403', '130404', '130405', '130406',
        // MATERIALES
        '1305', '130501', '130502', '130503', '130504',
        // DEPORTIVOS/UCB
        '1306', '130601', '130602', '130603', '130604', '130605',
        // MUSICALES
        '1307', '130701', '130702', '130703', '130704',
        // PART TIME TEACHER/ REEMPLAZOS
        '1308', '130801', '130802', '130803', '130804',
        // DOTACION
        '1309', '130901', '130902', '130903', '130904',
        // EXHIBITION PEP
        '1310', '131001',
        // PERSONAL PROYEC PAI
        '1311', '131101',
        // CAS/INTERCAS/PROYECTO COMUNITARIO
        '131201', '131202', '131203', '131204',
        // PRAE
        '1313', '131301',
        // MODELO NACIONES UNIDAS TVS
        '1314', '131401',
        // MUN OTROS COLEGIOS
        '1315', '131501',
        // CONSEJERIA UNIVERSITARIA
        '1316', '131601',
        // EXHIBITION DE ARTE
        '1317', '131701',
        // PSICOLOGIA INSTITUCIONAL
        '1318', '131801', '131802', '131803',
        // TECNOLOGIA Y AUDIVISUALES
        '1319', '131901', '131902', '131903', '131904',
        // EVENTOS Y AGASAJOS
        '132001', '132002', '132003', '132004', '132005',
        // CURSO PREICFES
        '1321', '132101',
    ];

    /**
     * Valida si un centro de costo es válido según los archivos oficiales
     */
    public function isValidCostCenter($centroCosto)
    {
        return in_array($centroCosto, $this->validCostCenters);
    }

    // Mapeo específico para centros de costo que requieren clasificación especial
    private $specificCenterMapping = [
        // CAPACITACION BACHILLERATO INTERNACIONAL - BASADO EN DATOS REALES
        '130101' => 'PREESCOLAR Y PRIMARIA', // Capacitación
        '130102' => 'CAPACITACION PRIMARIA', // No encontrado en datos
        '130103' => 'CAPACITACION MEDIA', // No encontrado en datos
        '130104' => 'ALTA', // Capacitación
        
        // CAPACITACIONES OTROS - BASADO EN DATOS REALES
        '130201' => 'PREESCOLAR Y PRIMARIA', // Capacitación
        '130202' => 'PREESCOLAR Y PRIMARIA', // Capacitación
        '130203' => 'PAI', // Capacitación
        '130204' => 'ALTA', // Capacitación
        '130205' => 'PEP', // Capacitación
        
        // MATERIAL IMPORTADO - BASADO EN DATOS REALES
        '130301' => 'PREESCOLAR Y PRIMARIA', // Material Importado
        '130302' => 'PREESCOLAR Y PRIMARIA', // Material Importado
        '130303' => 'ESCUELA MEDIA', // Material Importado
        '130304' => 'ALTA', // Material Importado
        '130305' => 'PEP', // Material Importado
        
        // BIBLIOTECA - BASADO EN DATOS REALES
        '130401' => 'PREESCOLAR Y PRIMARIA', // Biblioteca
        '130403' => 'BIBLIOTECA', // Biblioteca
        '130404' => 'ALTA', // Biblioteca
        '130405' => 'BIBLIOTECA', // Biblioteca
        
        // MATERIALES - BASADO EN DATOS REALES
        '130501' => 'PREESCOLAR Y PRIMARIA', // Materiales
        '130502' => 'PREESCOLAR Y PRIMARIA', // Materiales
        '130503' => 'ESCUELA MEDIA', // Materiales
        '130504' => 'ALTA', // Materiales
        
        // DEPORTIVOS - BASADO EN DATOS REALES
        '130605' => 'DEPORTES', // Deportes
        
        // MUSICALES - BASADO EN DATOS REALES
        '130702' => 'PREESCOLAR Y PRIMARIA', // Musicales
        '130703' => 'ESCUELA MEDIA', // Musicales
        
        // PART TIME TEACHER - BASADO EN DATOS REALES
        '130801' => 'PREESCOLAR Y PRIMARIA', // Part time
        '130804' => 'ALTA', // Part time
        
        // DOTACION - BASADO EN DATOS REALES (130904 aparece en múltiples secciones)
        // Se asigna a PREESCOLAR Y PRIMARIA como principal
        '130904' => 'PREESCOLAR Y PRIMARIA', // Dotación (también ESCUELA MEDIA, ALTA, DEPORTES)
        
        // EXHIBITION PEP - BASADO EN DATOS REALES
        '131001' => 'PEP', // Exhibición PEP
        
        // PERSONAL PROJECT PAI - BASADO EN DATOS REALES
        '131101' => 'PAI', // Personal Project PAI
        
        // CAS/INTERCAS/PROYECTO COMUNITARIO - BASADO EN DATOS REALES
        '131201' => 'CAS', // Confirmado en datos reales
        '131203' => 'PAI', // Proyecto comunitario
        
        // MODELO NACIONES UNIDAS - BASADO EN DATOS REALES
        '131401' => 'MUN', // MUN
        
        // PSICOLOGIA INSTITUCIONAL - BASADO EN DATOS REALES
        '131801' => 'PSICOLOGIA INSTITUCIONAL', // Confirmado
        '131803' => 'CONSEJERIA ESTUDIANTIL', // Confirmado
        
        // TECNOLOGIA INSTITUCIONAL - BASADO EN DATOS REALES
        '131901' => 'TECNOLOGIA INSTITUCIONAL', // Confirmado
        '131902' => 'TECNOLOGIA INSTITUCIONAL', // Confirmado
        '131903' => 'TECNOLOGIA INSTITUCIONAL', // Confirmado
        '131904' => 'TECNOLOGIA INSTITUCIONAL', // Confirmado
        
        // EVENTOS - CORRECCIONES CRÍTICAS BASADAS EN DATOS REALES
        '132001' => 'PREESCOLAR Y PRIMARIA', // Eventos Preescolar
        '132002' => 'PREESCOLAR Y PRIMARIA', // Eventos Primaria
        '132003' => 'ESCUELA MEDIA', // Eventos Media
        '132004' => 'ALTA', // Eventos Alta
        '132005' => 'DIRECCION GENERAL', // Dirección General - Confirmado
        
        // PREPARACIÓN PRUEBAS SABER - BASADO EN DATOS REALES
        '132101' => 'PREPARACION PRUEBAS SABER', // Nuevo descubrimiento
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

    // Mapeo específico de centros de costo que requieren rubro específico
    private $centroCostoToRubroMapping = [
        '132005' => 'EVENTOS', // Centro 132005 (Dirección General) debe ir a rubro EVENTOS
        '132001' => 'EVENTOS', // Eventos Preescolar
        '132002' => 'EVENTOS', // Eventos Primaria  
        '132003' => 'EVENTOS', // Eventos Media
        '132004' => 'EVENTOS', // Eventos Alta
        '130405' => 'BIBLIOTECA', // Centro 130405 debe ir específicamente a rubro BIBLIOTECA
        '130501' => 'MATERIALES', // Centro 130501 debe ir a rubro MATERIALES (Preescolar)
        '130502' => 'MATERIALES', // Centro 130502 debe ir a rubro MATERIALES (Primaria)
        '130503' => 'MATERIALES', // Centro 130503 debe ir a rubro MATERIALES (Media)
        '130504' => 'MATERIALES', // Centro 130504 debe ir a rubro MATERIALES (Alta)
        '131203' => 'Proyecto comunitario', // Centro 131203 debe ir a rubro Proyecto comunitario
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

    public function determineRubro($cuenta, $centroCosto = null)
    {
        if (empty($cuenta)) return 'Sin Clasificar';
        
        $cuenta = trim((string)$cuenta);
        
        // PRIMERO: Verificar mapeo específico por centro de costo
        if (!empty($centroCosto) && isset($this->centroCostoToRubroMapping[$centroCosto])) {
            return $this->centroCostoToRubroMapping[$centroCosto];
        }
        
        // SEGUNDO: Intentar buscar en la base de datos
        try {
            $cuentaModel = \App\Models\Cuenta::where('codigo', $cuenta)->first();
            if ($cuentaModel && $cuentaModel->rubro) {
                return $cuentaModel->rubro->nombre;
            }
        } catch (\Exception $e) {
            // Si hay error con la BD, usar mapeo estático
        }
        
        // TERCERO: Fallback usar mapeo estático por cuenta
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
                
                // NUEVO: Verificar validez del centro de costo pero CONSERVAR todos los registros
                $is_valid_center = true;
                if (!empty($item['centro_costo'])) {
                    $is_valid_center = $this->isValidCostCenter($item['centro_costo']);
                    
                    if (!$is_valid_center) {
                        error_log("Centro de costo inválido CONSERVADO: {$row[12]} en fila " . ($index + 1));
                    }
                }

                // TAMBIÉN: Verificar y marcar registros con datos de prueba
                $descripcion_lower = strtolower($item['descripcion'] ?? '');
                $is_test_data = strpos($descripcion_lower, 'revision manual') !== false || 
                               strpos($descripcion_lower, 'test') !== false ||
                               strpos($descripcion_lower, 'prueba') !== false;
                
                if ($is_test_data) {
                    $is_valid_center = false;
                    error_log("Registro de prueba CONSERVADO: {$item['descripcion']} en fila " . ($index + 1));
                }
                
                // Agregar bandera de validez al item
                $item['is_valid_center'] = $is_valid_center;
                
                // Clasificar sección y rubro para TODOS los registros
                if ($is_valid_center) {
                    $item['seccion'] = $this->determineSection($item['centro_costo']);
                    $item['rubro'] = $this->determineRubro($item['cuenta'], $item['centro_costo']);
                } else {
                    // Para centros inválidos, usar clasificaciones especiales pero conservar el registro
                    $item['seccion'] = 'OTROS - REVISION';
                    $item['rubro'] = 'DATOS NO OFICIALES';
                }
                
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
