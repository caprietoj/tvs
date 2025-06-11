<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetExecution extends Model
{
    protected $fillable = [
        'department',
        'month',
        'budget_amount',
        'executed_amount',
        'execution_percentage',
        'analysis'
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->budget_amount > 0) {
                $model->execution_percentage = ($model->executed_amount / $model->budget_amount) * 100;
                $model->analysis = self::generateAnalysis($model->execution_percentage);
            } else {
                $model->execution_percentage = 0;
                $model->analysis = 'No se puede calcular el porcentaje de ejecución sin presupuesto asignado.';
            }
        });
    }

    private static function generateAnalysis($percentage)
    {
        if ($percentage > 100) {
            return "ALERTA: Sobreejecución del presupuesto. Se ha excedido en " . 
                   number_format($percentage - 100, 2) . "% del presupuesto asignado.";
        }

        if ($percentage >= 95) {
            return "CRÍTICO: La ejecución está cerca o en el límite del presupuesto (" . 
                   number_format($percentage, 2) . "%). Se recomienda revisar la planeación.";
        }

        if ($percentage >= 85) {
            return "PRECAUCIÓN: Ejecución alta del presupuesto (" . 
                   number_format($percentage, 2) . "%). Se sugiere monitorear gastos futuros.";
        }

        if ($percentage >= 70) {
            return "NORMAL: Ejecución dentro de rangos esperados (" . 
                   number_format($percentage, 2) . "%). Continuar con el plan establecido.";
        }

        if ($percentage >= 50) {
            return "MODERADO: Ejecución en nivel medio (" . 
                   number_format($percentage, 2) . "%). Evaluar si el ritmo de ejecución es adecuado.";
        }

        return "BAJO: Ejecución por debajo de lo esperado (" . 
               number_format($percentage, 2) . "%). Se recomienda revisar la planificación y acelerar la ejecución.";
    }

    public static function getDepartmentOptions()
    {
        return [
            'Prescolar y Primaria',
            'Escuela Media',
            'Escuela Alta',
            'PAI',
            'PEP',
            'Deportes',
            'Biblioteca Institucional',
            'Psicologia Institucional'
        ];
    }
}
