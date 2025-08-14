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
                'Insumos de la sección / Material para clase'
            ],
            'Esc. Media' => [
                'Capacitación',
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
                'Insumos de la sección / Material para clase'
            ],
            'Escuela Alta' => [
                'Capacitación',
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
                'Insumos de la sección / Material para clase'
            ],
            'PAI' => [
                'Capacitación',
                'Material Importado',
                'Material deportivo',
                'Musicales',
                'Part time teacher- reemplazos',
                'Monografía',
                'Personal Project PAI',
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
            'PEP' => [
                'Capacitación',
                'Material Importado',
                'Material deportivo',
                'Musicales',
                'Part time teacher- reemplazos',
                'Exhibición PEP',
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
                'Insumos de la sección / Material para clase'
            ],
            'Deportes' => [
                'Capacitación',
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
                'Insumos de la sección / Material para clase'
            ],
            'Psicología Institucional' => [
                'Capacitación',
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
                'Insumos de la sección / Material para clase'
            ],
            'Biblioteca' => [
                'Capacitación',
                'Material Importado',
                'Biblioteca institucional',
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
                'Insumos de la sección / Material para clase'
            ],
            'Dirección General' => [
                'Capacitación',
                'Material Importado',
                'Biblioteca institucional',
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
                'Insumos de la sección / Material para clase'
            ],
            'Diploma' => [
                'Capacitación',
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
                'Insumos de la sección / Material para clase'
            ],
            'CAS' => [
                'Capacitación',
                'Material Importado',
                'Material deportivo',
                'Musicales',
                'Part time teacher- reemplazos',
                'Monografía',
                'Proyecto Comunitario',
                'CAS / Intercas',
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
            'Departamento de apoyo' => [
                'Capacitación',
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
                'Insumos de la sección / Material para clase'
            ],
            'Administración' => [
                'Capacitación',
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
                'Insumos de la sección / Material para clase'
            ],
            'Tecnología Institucional' => [
                'Capacitación',
                'Material Importado',
                'MUN TVS-Otros Colegios- GLY',
                'Apoyo Institucional',
                'Eventos Académicos y Sociales',
                'Insumos tecnológicos',
                'Alimentación',
                'Transporte',
                'Insumos de la sección / Material para clase'
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
