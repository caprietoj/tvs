<?php

namespace App\Helpers;

class BudgetHelper
{
    /**
     * Get the available budget options for purchase requests
     */
    public static function getBudgetOptions()
    {
        return [
            'Capacitación',
            'Academia',
            'Administración',
            'Material Importado',
            'Material deportivo',
            'Musicales',
            'Part time teacher- reemplazos',
            'Monografía',
            'Proyecto Comunitario',
            'MUN TVS-Otros Colegios- GLY',
            'Preparación Pruebas saber',
            'Apoyo Institucional',
            'Eventos Académicos y Sociales',
            'Insumos tecnológicos',
            'Salidas académicas sección',
            'Alimentación',
            'Transporte',
            'Insumos de la sección / Material para clase',
            'Personal Project PAI',
            'Exhibición PEP',
            'CAS / Intercas',
            'Biblioteca institucional',
        ];
    }

    /**
     * Get the hierarchical budget structure for forms
     */
    public static function getBudgetHierarchy()
    {
        return [
            'Preescolar y Prim.' => [
                'Capacitación',
                'Material Importado',
                'Material deportivo',
                'Musicales',
                'Part time teacher- reemplazos',
                'Proyecto Comunitario',
                'Apoyo Institucional',
                'Eventos Académicos y Sociales',
                'Insumos tecnológicos',
                'Salidas académicas sección',
                'Alimentación',
                'Transporte',
                'Insumos de la sección / Material para clase'
            ],
            'Esc. Media' => [
                'Capacitación',
                'Material Importado',
                'Material deportivo',
                'Musicales',
                'Part time teacher- reemplazos',
                'Proyecto Comunitario',
                'MUN TVS-Otros Colegios- GLY',
                'Preparación Pruebas saber',
                'Apoyo Institucional',
                'Eventos Académicos y Sociales',
                'Insumos tecnológicos',
                'Salidas académicas sección',
                'Alimentación',
                'Transporte',
                'Insumos de la sección / Material para clase'
            ],
            'Escuela Alta / DP' => [
                'Capacitación',
                'Material Importado',
                'Material deportivo',
                'Musicales',
                'Part time teacher- reemplazos',
                'Monografía',
                'MUN TVS-Otros Colegios- GLY',
                'Preparación Pruebas saber',
                'Apoyo Institucional',
                'Eventos Académicos y Sociales',
                'Insumos tecnológicos',
                'Salidas académicas sección',
                'Alimentación',
                'Transporte',
                'Insumos de la sección / Material para clase'
            ],
            'PAI' => [
                'Capacitación',
                'Material Importado',
                'Proyecto Comunitario',
                'Proyecto Personal'
            ],
            'PEP' => [
                'Capacitación',
                'Material Importado',
                'Exhibición PEP'
            ],
            'Deportes' => [
                'Dotación - Deportes',
                'Transporte',
                'Alimentación',
                'Participación en eventos'
            ],
            'Psicología Institucional' => [
                'Psicología Institucional'
            ],
            'Biblioteca' => [
                'Biblioteca Institucional'
            ],
            'Dirección General' => [
                'Gastos Dirección General'
            ],
            'CAS' => [
                'Gastos CAS trip',
                'Salidas Académicas',
                'Eventos Institucionales'
            ],
            'Administración' => [
                'Capacitación Admin',
                'Capacitación EMC/Docentes',
                'Capacitación COPASST (brigadas Cruz Roja, Bomberos)',
                'Indemnizaciones',
                'Equipos y Dotación Salones/Oficinas',
                'Exámenes Médicos',
                'Tecnología Institucional',
                'Seguro Escolar y Carné',
                'Elementos de Enfermería',
                'Mercadeo',
                'Publicidad',
                'Eventos (Día de la Familia y Xmas Show)',
                'Equipos de Dotación y Oficina',
                'Mantenimiento',
                'Reparaciones Mayores',
                'Reparación de Muebles',
                'Útiles de Oficina y Papelería',
                'Elementos de Aseo y Cafetería',
                'Dotación de Trabajo',
                'Gastos de Agasajos',
                'Bienestar Institucional',
                'Gastos de Contratación',
                'Afiliaciones e Inscripciones',
                'Alimentación',
                'Transporte',
                'Honorarios',
                'Gastos Administrativos'
            ],
            'Tecnología Institucional' => [
                'Equipos Tecnológicos Institucionales',
                'Mantenimientos Correctivos',
                'Mantenimientos Preventivos',
                'Licencias',
                'Plataformas',
                'Insumos Tecnológicos'
            ]
        ];
    }

    /**
     * Get budget options as a string for validation rules
     */
    public static function getBudgetValidationRule()
    {
        return 'in:' . implode(',', self::getBudgetOptions());
    }

    /**
     * Get budget options with parent section format for dropdowns
     * Returns array with format "Section - Budget Item"
     */
    public static function getBudgetOptionsWithParent()
    {
        $hierarchy = self::getBudgetHierarchy();
        $options = [];
        
        foreach ($hierarchy as $section => $budgets) {
            foreach ($budgets as $budget) {
                $options[$budget] = $section . ' - ' . $budget;
            }
        }
        
        return $options;
    }
}
