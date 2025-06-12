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

    /**
     * Obtener los correos específicos de una sección para notificaciones de pre-aprobación
     *
     * @param string $sectionName Nombre de la sección
     * @return array Lista de correos electrónicos de la sección
     */
    public function getSectionEmails(string $sectionName): array
    {
        $sections = Config::get('section_emails.sections', []);
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
            $default = Config::get('section_emails.default');
            if ($default) {
                $result = is_array($default) ? $default : [$default];
            }
        }
        
        // Asegurarse que compras@tvs.edu.co esté siempre incluido
        if (!in_array('compras@tvs.edu.co', $result)) {
            $result[] = 'compras@tvs.edu.co';
        }
        
        return $result;
    }
}