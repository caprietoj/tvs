<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;

class SectionClassifierService
{
    /**
     * Determinar si una sección es académica o administrativa
     *
     * @param string $sectionName Nombre de la sección
     * @return string 'academic', 'administrative', o 'unknown'
     */
    public function classifySection(string $sectionName): string
    {
        $academicSections = DynamicSectionEmailsService::getConfig('section_types.academic', []);
        $administrativeSections = DynamicSectionEmailsService::getConfig('section_types.administrative', []);

        // Verificar coincidencia exacta primero
        if (in_array($sectionName, $academicSections)) {
            return 'academic';
        }
        
        if (in_array($sectionName, $administrativeSections)) {
            return 'administrative';
        }

        // Si no hay coincidencia exacta, buscar coincidencias parciales
        foreach ($academicSections as $section) {
            if (stripos($sectionName, $section) !== false || stripos($section, $sectionName) !== false) {
                return 'academic';
            }
        }

        foreach ($administrativeSections as $section) {
            if (stripos($sectionName, $section) !== false || stripos($section, $sectionName) !== false) {
                return 'administrative';
            }
        }

        // Si no se puede clasificar, devolver unknown
        return 'unknown';
    }

    /**
     * Obtener el correo del director correspondiente según la clasificación de la sección
     * NUEVA FUNCIONALIDAD: Solo incluir generaldirector@tvs.edu.co si el monto es >= $500.000
     *
     * @param string $sectionName Nombre de la sección
     * @param float $totalAmount Monto total de la solicitud (opcional)
     * @return string Correo electrónico del director
     */
    public function getDirectorEmail(string $sectionName, ?float $totalAmount = null): string
    {
        // EXCEPCIÓN ESPECIAL: CAS siempre va al director administrativo
        if (strtoupper(trim($sectionName)) === 'CAS') {
            \Illuminate\Support\Facades\Log::info("Notificación CAS dirigida al director administrativo", [
                'section' => $sectionName,
                'director_email' => 'administrativedirector@tvs.edu.co',
                'reason' => 'CAS_SPECIAL_ROUTING'
            ]);
            return DynamicSectionEmailsService::getConfig('directors.administrative');
        }
        
        $classification = $this->classifySection($sectionName);
        
        if ($classification == 'academic') {
            $directorEmail = DynamicSectionEmailsService::getConfig('directors.academic');
            
            // FILTRO POR MONTO: Solo enviar a generaldirector@tvs.edu.co si el monto es >= $500.000
            if ($directorEmail === 'generaldirector@tvs.edu.co' && $totalAmount !== null && $totalAmount < 500000) {
                \Illuminate\Support\Facades\Log::info("Notificación a generaldirector@tvs.edu.co omitida - Monto insuficiente", [
                    'section' => $sectionName,
                    'amount' => $totalAmount,
                    'minimum_required' => 500000,
                    'director_email' => $directorEmail
                ]);
                // Retornar email administrativo como alternativa
                return DynamicSectionEmailsService::getConfig('directors.administrative');
            }
            
            return $directorEmail;
        }
        
        if ($classification == 'administrative') {
            return DynamicSectionEmailsService::getConfig('directors.administrative');
        }
        
        // Si no se pudo clasificar, usar el correo administrativo por defecto
        return DynamicSectionEmailsService::getConfig('directors.administrative');
    }

    /**
     * Obtener los correos de aprobación para solicitudes de materiales y fotocopias
     * RESTRINGIDO: Solo para compras@tvs.edu.co + auxiliaralmacen@tvs.edu.co + administrativedirector@tvs.edu.co
     * EXCLUYE EXPLÍCITAMENTE: generaldirector@tvs.edu.co
     *
     * @param string $sectionName Nombre de la sección
     * @return array Lista de correos electrónicos para aprobación
     */
    public function getMaterialsApprovalEmails(string $sectionName): array
    {
        // Para solicitudes de materiales y fotocopias, solo los correos específicos autorizados
        $authorizedEmails = [
            'compras@tvs.edu.co',
            'auxiliaralmacen@tvs.edu.co',
            'administrativedirector@tvs.edu.co'
        ];
        
        // Usar el método centralizado para filtrar emails excluidos
        $filteredEmails = self::filterExcludedEmailsForMaterialsAndCopies($authorizedEmails);
        
        // \Illuminate\Support\Facades\Log::info("Notificaciones de materiales/fotocopias para sección {$sectionName}: " . implode(', ', $filteredEmails));
        
        return $filteredEmails;
    }

    /**
     * Obtener los correos específicos de una sección para notificaciones de pre-aprobación
     * NUEVA FUNCIONALIDAD: Solo incluir generaldirector@tvs.edu.co si el monto es >= $500.000
     *
     * @param string $sectionName Nombre de la sección
     * @param float $totalAmount Monto total de la solicitud (opcional)
     * @return array Lista de correos electrónicos de la sección
     */
    public function getSectionEmails(string $sectionName, ?float $totalAmount = null): array
    {
        // EXCEPCIÓN ESPECIAL: CAS siempre envía únicamente al director administrativo
        if (strtoupper(trim($sectionName)) === 'CAS') {
            $administrativeDirectorEmail = DynamicSectionEmailsService::getConfig('directors.administrative');
            \Illuminate\Support\Facades\Log::info("Notificación CAS dirigida únicamente al director administrativo", [
                'section' => $sectionName,
                'emails' => [$administrativeDirectorEmail],
                'reason' => 'CAS_EXCLUSIVE_ROUTING'
            ]);
            return [$administrativeDirectorEmail];
        }
        
        $sections = DynamicSectionEmailsService::getConfig('sections', []);
        $result = [];
        
        // Buscar coincidencia exacta primero
        if (isset($sections[$sectionName])) {
            $emails = $sections[$sectionName];
            // Si es un string, convertir a array
            if (is_string($emails)) {
                $result = [$emails];
            }
            // Si ya es un array, usarlo
            elseif (is_array($emails)) {
                $result = $emails;
            }
        } else {
            // Buscar coincidencias parciales
            foreach ($sections as $section => $emails) {
                if (stripos($sectionName, $section) !== false || stripos($section, $sectionName) !== false) {
                    // Si es un string, convertir a array
                    if (is_string($emails)) {
                        $result = [$emails];
                    }
                    // Si ya es un array, usarlo
                    elseif (is_array($emails)) {
                        $result = $emails;
                    }
                    break;
                }
            }
        }
        
        // Si no se encuentra configuración específica, usar el valor por defecto
        if (empty($result)) {
            $default = DynamicSectionEmailsService::getConfig('default');
            if ($default) {
                $result = is_array($default) ? $default : [$default];
            }
        }
        
        // FILTRO POR MONTO: Remover generaldirector@tvs.edu.co si el monto es < $500.000
        if ($totalAmount !== null && $totalAmount < 500000) {
            $originalCount = count($result);
            $result = array_filter($result, function($email) {
                return $email !== 'generaldirector@tvs.edu.co';
            });
            
            if (count($result) < $originalCount) {
                \Illuminate\Support\Facades\Log::info("Notificación a generaldirector@tvs.edu.co omitida de lista de sección - Monto insuficiente", [
                    'section' => $sectionName,
                    'amount' => $totalAmount,
                    'minimum_required' => 500000,
                    'emails_before' => $originalCount,
                    'emails_after' => count($result)
                ]);
            }
            
            // Reindexar el array después del filtrado
            $result = array_values($result);
        }
        
        // Asegurarse que el correo de compras esté siempre incluido según la configuración activa
        $alwaysNotify = DynamicSectionEmailsService::getConfig('always_notify', []);
        foreach ($alwaysNotify as $email) {
            if (!in_array($email, $result)) {
                $result[] = $email;
            }
        }
        
        return $result;
    }

    /**
     * Filtrar emails excluidos de las notificaciones de materiales y fotocopias
     * Este método centraliza la exclusión de emails específicos de estas notificaciones
     *
     * @param array $emails Lista de emails a filtrar
     * @return array Lista de emails filtrada
     */
    public static function filterExcludedEmailsForMaterialsAndCopies(array $emails): array
    {
        $excludedEmails = [
            'generaldirector@tvs.edu.co',
            'cafeteriaaldimark@tvs.edu.co',
            'aprendizsistemas@tvs.edu.co',
            'jefesistemas@tvs.edu.co',
            'administrativedirector@tvs.edu.co',
            'auxiliarsistemas@tvs.edu.co',
            'contabilidad@tvs.edu.co'
        ];
        
        $filteredEmails = array_diff($emails, $excludedEmails);
        
        // \Illuminate\Support\Facades\Log::info("Filtrado de emails para materiales/fotocopias - Originales: " . implode(', ', $emails) . " | Filtrados: " . implode(', ', $filteredEmails) . " | Excluidos: " . implode(', ', $excludedEmails));
        
        return array_values($filteredEmails); // Reindexar el array
    }

    /**
     * Obtener el monto total de una solicitud de compra para evaluar si debe notificar al director general
     *
     * @param \App\Models\PurchaseRequest $purchaseRequest Solicitud de compra
     * @return float Monto total de la solicitud
     */
    public function getTotalAmountFromPurchaseRequest($purchaseRequest): float
    {
        $totalAmount = 0;

        try {
            // Primero, intentar obtener el monto de la cotización seleccionada
            if ($purchaseRequest->selectedQuotation) {
                $totalAmount = floatval($purchaseRequest->selectedQuotation->total_amount ?? 0);
                \Illuminate\Support\Facades\Log::info("Monto obtenido de cotización seleccionada", [
                    'request_id' => $purchaseRequest->id,
                    'quotation_id' => $purchaseRequest->selectedQuotation->id,
                    'amount' => $totalAmount
                ]);
            }
            
            // Si no hay cotización seleccionada, buscar en selecciones mixtas
            if ($totalAmount <= 0) {
                $mixedSelections = $purchaseRequest->mixedSelections;
                if ($mixedSelections && $mixedSelections->count() > 0) {
                    $totalAmount = $mixedSelections->sum('total_price');
                    \Illuminate\Support\Facades\Log::info("Monto obtenido de selecciones mixtas", [
                        'request_id' => $purchaseRequest->id,
                        'selections_count' => $mixedSelections->count(),
                        'amount' => $totalAmount
                    ]);
                }
            }
            
            // Como última opción, usar el monto de la primera cotización disponible
            if ($totalAmount <= 0) {
                $firstQuotation = $purchaseRequest->quotations()->first();
                if ($firstQuotation) {
                    $totalAmount = floatval($firstQuotation->total_amount ?? 0);
                    \Illuminate\Support\Facades\Log::info("Monto obtenido de primera cotización disponible", [
                        'request_id' => $purchaseRequest->id,
                        'quotation_id' => $firstQuotation->id,
                        'amount' => $totalAmount
                    ]);
                }
            }

            // Para servicios sin cotización, intentar obtener desde service_budget
            if ($totalAmount <= 0 && $purchaseRequest->type === 'services' && isset($purchaseRequest->service_budget)) {
                $totalAmount = floatval($purchaseRequest->service_budget);
                \Illuminate\Support\Facades\Log::info("Monto obtenido de presupuesto de servicio", [
                    'request_id' => $purchaseRequest->id,
                    'amount' => $totalAmount
                ]);
            }

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error al obtener monto total de solicitud", [
                'request_id' => $purchaseRequest->id ?? 'unknown',
                'error' => $e->getMessage()
            ]);
        }

        return $totalAmount;
    }
}