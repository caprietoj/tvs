<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PresupuestoAprobado;

class PresupuestoAprobadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $year = date('Y');
        
        $presupuestos = [
            // PREESCOLAR Y PRIMARIA
            ['seccion' => 'PREESCOLAR Y PRIMARIA', 'concepto' => 'Capacitación', 'monto_aprobado' => 500000, 'year' => $year],
            ['seccion' => 'PREESCOLAR Y PRIMARIA', 'concepto' => 'Material Importado', 'monto_aprobado' => 800000, 'year' => $year],
            ['seccion' => 'PREESCOLAR Y PRIMARIA', 'concepto' => 'Material Deportivo', 'monto_aprobado' => 300000, 'year' => $year],
            ['seccion' => 'PREESCOLAR Y PRIMARIA', 'concepto' => 'Musicales', 'monto_aprobado' => 400000, 'year' => $year],
            ['seccion' => 'PREESCOLAR Y PRIMARIA', 'concepto' => 'Part time teacher - reemplazos', 'monto_aprobado' => 1200000, 'year' => $year],
            ['seccion' => 'PREESCOLAR Y PRIMARIA', 'concepto' => 'Apoyo Institucional', 'monto_aprobado' => 600000, 'year' => $year],
            ['seccion' => 'PREESCOLAR Y PRIMARIA', 'concepto' => 'Eventos Académicos y Sociales', 'monto_aprobado' => 700000, 'year' => $year],
            ['seccion' => 'PREESCOLAR Y PRIMARIA', 'concepto' => 'Insumos Tecnológicos', 'monto_aprobado' => 450000, 'year' => $year],
            ['seccion' => 'PREESCOLAR Y PRIMARIA', 'concepto' => 'Salidas Académicas Sección', 'monto_aprobado' => 350000, 'year' => $year],
            ['seccion' => 'PREESCOLAR Y PRIMARIA', 'concepto' => 'Alimentación', 'monto_aprobado' => 250000, 'year' => $year],
            ['seccion' => 'PREESCOLAR Y PRIMARIA', 'concepto' => 'Transporte', 'monto_aprobado' => 200000, 'year' => $year],
            ['seccion' => 'PREESCOLAR Y PRIMARIA', 'concepto' => 'Insumos de la Sección / Material para Clase', 'monto_aprobado' => 750000, 'year' => $year],

            // ESCUELA MEDIA
            ['seccion' => 'ESCUELA MEDIA', 'concepto' => 'Capacitación', 'monto_aprobado' => 600000, 'year' => $year],
            ['seccion' => 'ESCUELA MEDIA', 'concepto' => 'Material Importado', 'monto_aprobado' => 900000, 'year' => $year],
            ['seccion' => 'ESCUELA MEDIA', 'concepto' => 'Material Deportivo', 'monto_aprobado' => 350000, 'year' => $year],
            ['seccion' => 'ESCUELA MEDIA', 'concepto' => 'Musicales', 'monto_aprobado' => 450000, 'year' => $year],
            ['seccion' => 'ESCUELA MEDIA', 'concepto' => 'Part time teacher - reemplazos', 'monto_aprobado' => 1300000, 'year' => $year],
            ['seccion' => 'ESCUELA MEDIA', 'concepto' => 'Proyecto Comunitario', 'monto_aprobado' => 400000, 'year' => $year],
            ['seccion' => 'ESCUELA MEDIA', 'concepto' => 'MUN TVS - Otros Colegios - GLY', 'monto_aprobado' => 500000, 'year' => $year],
            ['seccion' => 'ESCUELA MEDIA', 'concepto' => 'Apoyo Institucional', 'monto_aprobado' => 650000, 'year' => $year],
            ['seccion' => 'ESCUELA MEDIA', 'concepto' => 'Eventos Académicos y Sociales', 'monto_aprobado' => 750000, 'year' => $year],
            ['seccion' => 'ESCUELA MEDIA', 'concepto' => 'Insumos Tecnológicos', 'monto_aprobado' => 500000, 'year' => $year],
            ['seccion' => 'ESCUELA MEDIA', 'concepto' => 'Salidas Académicas Sección', 'monto_aprobado' => 400000, 'year' => $year],
            ['seccion' => 'ESCUELA MEDIA', 'concepto' => 'Alimentación', 'monto_aprobado' => 300000, 'year' => $year],
            ['seccion' => 'ESCUELA MEDIA', 'concepto' => 'Transporte', 'monto_aprobado' => 250000, 'year' => $year],
            ['seccion' => 'ESCUELA MEDIA', 'concepto' => 'Insumos de la Sección / Material para Clase', 'monto_aprobado' => 800000, 'year' => $year],

            // ALTA
            ['seccion' => 'ALTA', 'concepto' => 'Capacitación', 'monto_aprobado' => 700000, 'year' => $year],
            ['seccion' => 'ALTA', 'concepto' => 'Material Importado', 'monto_aprobado' => 1000000, 'year' => $year],
            ['seccion' => 'ALTA', 'concepto' => 'Material Deportivo', 'monto_aprobado' => 400000, 'year' => $year],
            ['seccion' => 'ALTA', 'concepto' => 'Musicales', 'monto_aprobado' => 500000, 'year' => $year],
            ['seccion' => 'ALTA', 'concepto' => 'Part time teacher - reemplazos', 'monto_aprobado' => 1400000, 'year' => $year],
            ['seccion' => 'ALTA', 'concepto' => 'Monografía', 'monto_aprobado' => 350000, 'year' => $year],
            ['seccion' => 'ALTA', 'concepto' => 'MUN TVS - Otros Colegios - GLY', 'monto_aprobado' => 550000, 'year' => $year],
            ['seccion' => 'ALTA', 'concepto' => 'Preparación Pruebas Saber', 'monto_aprobado' => 600000, 'year' => $year],
            ['seccion' => 'ALTA', 'concepto' => 'Apoyo Institucional', 'monto_aprobado' => 700000, 'year' => $year],
            ['seccion' => 'ALTA', 'concepto' => 'Eventos Académicos y Sociales', 'monto_aprobado' => 800000, 'year' => $year],
            ['seccion' => 'ALTA', 'concepto' => 'Insumos Tecnológicos', 'monto_aprobado' => 550000, 'year' => $year],
            ['seccion' => 'ALTA', 'concepto' => 'Salidas Académicas Sección', 'monto_aprobado' => 450000, 'year' => $year],
            ['seccion' => 'ALTA', 'concepto' => 'Alimentación', 'monto_aprobado' => 350000, 'year' => $year],
            ['seccion' => 'ALTA', 'concepto' => 'Transporte', 'monto_aprobado' => 300000, 'year' => $year],
            ['seccion' => 'ALTA', 'concepto' => 'Insumos de la Sección / Material para Clase', 'monto_aprobado' => 850000, 'year' => $year],
        ];

        foreach ($presupuestos as $presupuesto) {
            PresupuestoAprobado::firstOrCreate(
                [
                    'seccion' => $presupuesto['seccion'],
                    'concepto' => $presupuesto['concepto'],
                    'year' => $presupuesto['year']
                ],
                $presupuesto
            );
        }
    }
}
