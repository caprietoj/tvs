<?php

namespace App\Services;

class AllowedSectionsService
{
    /**
     * Lista de secciones permitidas en los filtros
     * 
     * @return array
     */
    public static function getAllowedSections(): array
    {
        return [
            'Administracion',
            'Direccion General',
            'Escuela Alta / DP',
            'Escuela Media',
            'PAI',
            'PEP',
            'Preescolar y Primaria',
            'Tecnología Institucional'
        ];
    }

    /**
     * Filtrar secciones para mostrar solo las permitidas
     * 
     * @param \Illuminate\Support\Collection|array $allSections
     * @return \Illuminate\Support\Collection
     */
    public static function filterSections($allSections): \Illuminate\Support\Collection
    {
        $allowedSections = self::getAllowedSections();
        
        // Convertir a collection si es array
        if (is_array($allSections)) {
            $allSections = collect($allSections);
        }
        
        // Filtrar secciones usando coincidencias exactas
        return $allSections->filter(function ($section) use ($allowedSections) {
            return in_array($section, $allowedSections);
        })->sort()->values();
    }

    /**
     * Normalizar texto para comparación
     * 
     * @param string $text
     * @return string
     */
    private static function normalizeText(string $text): string
    {
        // Convertir a minúsculas y eliminar acentos
        $text = strtolower(trim($text));
        $text = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $text);
        
        return $text;
    }

    /**
     * Verificar coincidencias especiales para variaciones comunes de nombres de secciones
     * 
     * @param string $section
     * @param string $allowed
     * @return bool
     */
    private static function isSpecialMatch(string $section, string $allowed): bool
    {
        $specialMatches = [
            // Administración con o sin acento
            'administracion' => ['administracion'],
            
            // Direccion General con variaciones
            'direccion general' => ['direccion general'],
            
            // Variaciones de Preescolar y Primaria
            'prescolar y primaria' => ['preescolar y primaria', 'prescolar y primaria', 'pre escolar', 'preescolar', 'primaria'],
            'preescolar y primaria' => ['preescolar y primaria', 'prescolar y primaria', 'pre escolar', 'preescolar', 'primaria'],
            
            // Escuela Alta/DP y variaciones
            'escuela alta/dp' => ['escuela alta', 'alta', 'dp', 'diploma'],
            'escuela alta' => ['escuela alta/dp', 'alta', 'dp'],
            'alta' => ['escuela alta', 'escuela alta/dp'],
            
            // Psicologia con o sin acento
            'psicologia institucional' => ['psicologia institucional'],
            
            // Tecnologia con o sin acento
            'tecnologia institucional' => ['tecnologia institucional', 'sistemas', 'ti'],
        ];

        if (isset($specialMatches[$allowed])) {
            return in_array($section, $specialMatches[$allowed]);
        }

        if (isset($specialMatches[$section])) {
            return in_array($allowed, $specialMatches[$section]);
        }

        return false;
    }

    /**
     * Verificar si una sección está permitida
     * 
     * @param string $section
     * @return bool
     */
    public static function isSectionAllowed(string $section): bool
    {
        $allowedSections = self::getAllowedSections();
        $normalizedSection = self::normalizeText($section);
        
        foreach ($allowedSections as $allowedSection) {
            $normalizedAllowed = self::normalizeText($allowedSection);
            
            if ($normalizedSection === $normalizedAllowed || 
                self::isSpecialMatch($normalizedSection, $normalizedAllowed)) {
                return true;
            }
        }
        
        return false;
    }
}
