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
        $academicSections = Config::get('section_emails.section_types.academic', []);
        $administrativeSections = Config::get('section_emails.section_types.administrative', []);

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
            return Config::get('section_emails.directors.academic');
        }
        
        if ($classification == 'administrative') {
            return Config::get('section_emails.directors.administrative');
        }
        
        // Si no se pudo clasificar, usar el correo administrativo por defecto
        return Config::get('section_emails.directors.administrative');
    }

    /**
     * Obtener los correos de aprobación para solicitudes de materiales según la sección
     *
     * @param string $sectionName Nombre de la sección
     * @return array Lista de correos electrónicos para aprobación
     */
    public function getMaterialsApprovalEmails(string $sectionName): array
    {
        $materialsEmails = Config::get('section_emails.materials_approval_emails', []);
        
        // Buscar coincidencia exacta primero
        if (isset($materialsEmails[$sectionName])) {
            return (array) $materialsEmails[$sectionName];
        }
        
        // Buscar coincidencias parciales
        foreach ($materialsEmails as $section => $emails) {
            if (stripos($sectionName, $section) !== false || stripos($section, $sectionName) !== false) {
                return (array) $emails;
            }
        }
        
        // Si no se encuentra configuración específica, devolver array vacío
        return [];
    }
}