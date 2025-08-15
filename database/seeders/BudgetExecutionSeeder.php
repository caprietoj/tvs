<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BudgetExecution;

class BudgetExecutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Limpiar datos existentes
        BudgetExecution::truncate();

        // Datos de ejemplo para diferentes departamentos
        $budgetData = [
            [
                'department' => 'Prescolar y Primaria',
                'month' => 'Enero',
                'budget_amount' => 50000000.00,
                'executed_amount' => 35000000.00,
            ],
            [
                'department' => 'Prescolar y Primaria',
                'month' => 'Febrero',
                'budget_amount' => 50000000.00,
                'executed_amount' => 42000000.00,
            ],
            [
                'department' => 'Escuela Media',
                'month' => 'Enero',
                'budget_amount' => 75000000.00,
                'executed_amount' => 68000000.00,
            ],
            [
                'department' => 'Escuela Media',
                'month' => 'Febrero',
                'budget_amount' => 75000000.00,
                'executed_amount' => 71000000.00,
            ],
            [
                'department' => 'Escuela Alta',
                'month' => 'Enero',
                'budget_amount' => 80000000.00,
                'executed_amount' => 72000000.00,
            ],
            [
                'department' => 'Escuela Alta',
                'month' => 'Febrero',
                'budget_amount' => 80000000.00,
                'executed_amount' => 78000000.00,
            ],
            [
                'department' => 'PAI',
                'month' => 'Enero',
                'budget_amount' => 30000000.00,
                'executed_amount' => 28000000.00,
            ],
            [
                'department' => 'PEP',
                'month' => 'Enero',
                'budget_amount' => 25000000.00,
                'executed_amount' => 20000000.00,
            ],
            [
                'department' => 'Deportes',
                'month' => 'Enero',
                'budget_amount' => 15000000.00,
                'executed_amount' => 12000000.00,
            ],
            [
                'department' => 'Biblioteca Institucional',
                'month' => 'Enero',
                'budget_amount' => 10000000.00,
                'executed_amount' => 7500000.00,
            ],
            [
                'department' => 'Psicologia Institucional',
                'month' => 'Enero',
                'budget_amount' => 20000000.00,
                'executed_amount' => 18000000.00,
            ],
        ];

        // Crear registros con cálculo automático de porcentajes
        foreach ($budgetData as $data) {
            BudgetExecution::create($data);
        }

        echo "Seeder ejecutado correctamente. Se crearon " . count($budgetData) . " registros de ejecución presupuestal.\n";
    }
}
