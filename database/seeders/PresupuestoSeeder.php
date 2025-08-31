<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PresupuestoAprobado;
use App\Models\PresupuestoSeccion;

class PresupuestoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear presupuestos aprobados para cada sección
        $currentYear = date('Y');
        
        $presupuestosAprobados = [
            // Preescolar y Primaria
            ['seccion' => 'PREESCOLAR Y PRIMARIA', 'concepto' => 'Materiales Educativos', 'monto_aprobado' => 150000000, 'year' => $currentYear],
            ['seccion' => 'PREESCOLAR Y PRIMARIA', 'concepto' => 'Actividades Didácticas', 'monto_aprobado' => 100000000, 'year' => $currentYear],
            ['seccion' => 'PREESCOLAR Y PRIMARIA', 'concepto' => 'Recursos Tecnológicos', 'monto_aprobado' => 75000000, 'year' => $currentYear],
            
            // Escuela Media
            ['seccion' => 'ESCUELA MEDIA', 'concepto' => 'Material Deportivo', 'monto_aprobado' => 200000000, 'year' => $currentYear],
            ['seccion' => 'ESCUELA MEDIA', 'concepto' => 'Laboratorios', 'monto_aprobado' => 300000000, 'year' => $currentYear],
            ['seccion' => 'ESCUELA MEDIA', 'concepto' => 'Biblioteca', 'monto_aprobado' => 150000000, 'year' => $currentYear],
            
            // Escuela Alta
            ['seccion' => 'ALTA', 'concepto' => 'Equipos Científicos', 'monto_aprobado' => 400000000, 'year' => $currentYear],
            ['seccion' => 'ALTA', 'concepto' => 'Material Bibliográfico', 'monto_aprobado' => 200000000, 'year' => $currentYear],
            ['seccion' => 'ALTA', 'concepto' => 'Proyecto de Grado', 'monto_aprobado' => 300000000, 'year' => $currentYear],
            
            // PAI
            ['seccion' => 'PAI', 'concepto' => 'Recursos Metodológicos', 'monto_aprobado' => 180000000, 'year' => $currentYear],
            ['seccion' => 'PAI', 'concepto' => 'Formación Docente', 'monto_aprobado' => 120000000, 'year' => $currentYear],
            ['seccion' => 'PAI', 'concepto' => 'Evaluación IB', 'monto_aprobado' => 100000000, 'year' => $currentYear],
            
            // PEP
            ['seccion' => 'PEP', 'concepto' => 'Material Lúdico', 'monto_aprobado' => 150000000, 'year' => $currentYear],
            ['seccion' => 'PEP', 'concepto' => 'Capacitación', 'monto_aprobado' => 100000000, 'year' => $currentYear],
            ['seccion' => 'PEP', 'concepto' => 'Recursos Digitales', 'monto_aprobado' => 100000000, 'year' => $currentYear],
            
            // Deportes
            ['seccion' => 'DEPORTES', 'concepto' => 'Equipamiento', 'monto_aprobado' => 120000000, 'year' => $currentYear],
            ['seccion' => 'DEPORTES', 'concepto' => 'Torneos', 'monto_aprobado' => 80000000, 'year' => $currentYear],
            ['seccion' => 'DEPORTES', 'concepto' => 'Mantenimiento', 'monto_aprobado' => 50000000, 'year' => $currentYear],
            
            // Biblioteca
            ['seccion' => 'BIBLIOTECA', 'concepto' => 'Libros y Revistas', 'monto_aprobado' => 100000000, 'year' => $currentYear],
            ['seccion' => 'BIBLIOTECA', 'concepto' => 'Base de Datos', 'monto_aprobado' => 50000000, 'year' => $currentYear],
            ['seccion' => 'BIBLIOTECA', 'concepto' => 'Mobiliario', 'monto_aprobado' => 30000000, 'year' => $currentYear],
            
            // Psicología Institucional
            ['seccion' => 'PSICOLOGÍA INSTITUCIONAL', 'concepto' => 'Tests y Evaluaciones', 'monto_aprobado' => 60000000, 'year' => $currentYear],
            ['seccion' => 'PSICOLOGÍA INSTITUCIONAL', 'concepto' => 'Material Terapéutico', 'monto_aprobado' => 40000000, 'year' => $currentYear],
            ['seccion' => 'PSICOLOGÍA INSTITUCIONAL', 'concepto' => 'Programas Especiales', 'monto_aprobado' => 20000000, 'year' => $currentYear],
        ];

        foreach ($presupuestosAprobados as $presupuesto) {
            PresupuestoAprobado::create($presupuesto);
        }

        // Crear configuraciones de presupuesto por sección
        $configuracionesSecciones = [
            ['seccion' => 'PREESCOLAR Y PRIMARIA', 'presupuesto_total' => 325000000, 'year' => $currentYear],
            ['seccion' => 'ESCUELA MEDIA', 'presupuesto_total' => 650000000, 'year' => $currentYear],
            ['seccion' => 'ALTA', 'presupuesto_total' => 900000000, 'year' => $currentYear],
            ['seccion' => 'PAI', 'presupuesto_total' => 400000000, 'year' => $currentYear],
            ['seccion' => 'PEP', 'presupuesto_total' => 350000000, 'year' => $currentYear],
            ['seccion' => 'DEPORTES', 'presupuesto_total' => 250000000, 'year' => $currentYear],
            ['seccion' => 'BIBLIOTECA', 'presupuesto_total' => 180000000, 'year' => $currentYear],
            ['seccion' => 'PSICOLOGÍA INSTITUCIONAL', 'presupuesto_total' => 120000000, 'year' => $currentYear],
        ];

        foreach ($configuracionesSecciones as $configuracion) {
            PresupuestoSeccion::create($configuracion);
        }

        $this->command->info('Presupuestos aprobados y configuraciones de secciones creados exitosamente.');
    }
}
