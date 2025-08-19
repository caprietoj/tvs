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
     *
     * @param string $sectionName Nombre de la sección
     * @return string Correo electrónico del director
     */
    public function getDirectorEmail(string $sectionName): string
    {
        $classification = $this->classifySection($sectionName);
        
        if ($classification == 'academic') {
            return DynamicSectionEmailsService::getConfig('directors.academic');
        }
        
        if ($classification == 'administrative') {
            return DynamicSectionEmailsService::getConfig('directors.administrative');
        }
        
        // Si no se pudo clasificar, usar el correo administrativo por defecto
        return DynamicSectionEmailsService::getConfig('directors.administrative');
    }

    /**
     * Obtener los correos de aprobación para solicitudes de materiales según la sección
     * RESTRINGIDO: Solo para administradores + compras@tvs.edu.co + auxiliaralmacen@tvs.edu.co
     *
     * @param string $sectionName Nombre de la sección
     * @return array Lista de correos electrónicos para aprobación
     */
    public function getMaterialsApprovalEmails(string $sectionName): array
    {
        // Para solicitudes de materiales y fotocopias, restringir a usuarios autorizados únicamente
        $restrictedEmails = [];
        
        // Obtener correos de usuarios con rol admin
        $adminUsers = \App\Models\User::role('admin')->pluck('email')->toArray();
        $restrictedEmails = array_merge($restrictedEmails, $adminUsers);
        
        // Agregar correos específicos autorizados
        $authorizedEmails = ['compras@tvs.edu.co', 'auxiliaralmacen@tvs.edu.co'];
        $restrictedEmails = array_merge($restrictedEmails, $authorizedEmails);
        
        // Eliminar duplicados
        $restrictedEmails = array_unique($restrictedEmails);
        
        \Log::info("Notificaciones de materiales/fotocopias restringidas para sección {$sectionName}: " . implode(', ', $restrictedEmails));
        
        return $restrictedEmails;
    }

    /**
     * Obtener los correos específicos de una sección para notificaciones de pre-aprobación
     *
     * @param string $sectionName Nombre de la sección
     * @return array Lista de correos electrónicos de la sección
     */
    public function getSectionEmails(string $sectionName): array
    {
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
        
        // Asegurarse que el correo de compras esté siempre incluido según la configuración activa
        $alwaysNotify = DynamicSectionEmailsService::getConfig('always_notify', []);
        foreach ($alwaysNotify as $email) {
            if (!in_array($email, $result)) {
                $result[] = $email;
            }
        }
        
        return $result;
    }
}