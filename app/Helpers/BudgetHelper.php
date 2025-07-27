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
            'Gastos Importación/Material Importado',
            'Biblioteca institucional',
            'Biblioteca',
            'Materiales',
            'Deportes-Dotación',
            'Musicales',
            'Part time teacher- reemplazos',
            'Dotación',
            'Exhibición PEP',
            'Monografia',
            'Personal Project PAI',
            'Proyecto Comunitario',
            'CAS / Intercas',
            'MUN TVS-Otros Colegios- GLY',
            'Preparación Pruebas saber',
            'Psicología Institucional',
            'Eventos Académicos y Sociales',
            'material para clases',
            'insumos tecnologicos',
            'salidas academicas sección',
            'Reemplazos docentes',
            'Alimentación',
            'Transporte',
            'Insumos de la sección',
        ];
    }

    /**
     * Get budget options as a string for validation rules
     */
    public static function getBudgetValidationRule()
    {
        return 'in:' . implode(',', self::getBudgetOptions());
    }
}
