<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WarehouseSurveySeeder extends Seeder
{
    public function run()
    {
        DB::table('warehouse_survey_responses')->insert([
            [
                'survey_period' => '2025-06',
                'timestamp' => '2025-06-06 12:10:42',
                'dependencia' => 'Docente',
                'califica_experiencia' => 'Excelente',
                'califica_tiempos' => 'Excelente',
                'requerimiento_oportuno' => 'Sí',
                'materiales_disponibles' => 'Sí',
                'califica_servicio_persona' => 'Excelente',
                'califica_calidad_materiales' => 'Bueno',
                'opciones_cotizaciones' => 'Sí',
                'proveedores_cumplen' => 'Sí',
                'aspectos_destacados' => 'El servicio y amabilidad.',
                'oportunidades_mejora' => 'N/A',
                'uploaded_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'survey_period' => '2025-06',
                'timestamp' => '2025-06-06 12:19:32',
                'dependencia' => 'Docente',
                'califica_experiencia' => 'Bueno',
                'califica_tiempos' => 'Bueno',
                'requerimiento_oportuno' => 'Sí',
                'materiales_disponibles' => 'Sí',
                'califica_servicio_persona' => 'Excelente',
                'califica_calidad_materiales' => 'Excelente',
                'opciones_cotizaciones' => 'Sí',
                'proveedores_cumplen' => 'Sí',
                'aspectos_destacados' => 'Amabilidad y oportunidad',
                'oportunidades_mejora' => 'Autonomía en la consecución de proveedores especializados',
                'uploaded_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'survey_period' => '2025-06',
                'timestamp' => '2025-06-06 13:05:55',
                'dependencia' => 'Administrativo',
                'califica_experiencia' => 'Excelente',
                'califica_tiempos' => 'Bueno',
                'requerimiento_oportuno' => 'Sí',
                'materiales_disponibles' => 'Sí',
                'califica_servicio_persona' => 'Excelente',
                'califica_calidad_materiales' => 'Excelente',
                'opciones_cotizaciones' => 'Sí',
                'proveedores_cumplen' => 'Sí',
                'aspectos_destacados' => 'Su oportunidad y eficacia durante las entregas de los insumos',
                'oportunidades_mejora' => 'Conservar stock para situaciones necesarias',
                'uploaded_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'survey_period' => '2025-06',
                'timestamp' => '2025-06-06 13:50:02',
                'dependencia' => 'Administrativo',
                'califica_experiencia' => 'Excelente',
                'califica_tiempos' => 'Bueno',
                'requerimiento_oportuno' => 'Sí',
                'materiales_disponibles' => 'Sí',
                'califica_servicio_persona' => 'Excelente',
                'califica_calidad_materiales' => 'Bueno',
                'opciones_cotizaciones' => 'Sí',
                'proveedores_cumplen' => 'Sí',
                'aspectos_destacados' => 'Amabilidad, Disposición para ayudar',
                'oportunidades_mejora' => 'Quizá contar con más materiales de uso común de las secciones',
                'uploaded_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'survey_period' => '2025-06',
                'timestamp' => '2025-06-06 16:55:02',
                'dependencia' => 'Docente',
                'califica_experiencia' => 'Excelente',
                'califica_tiempos' => 'Bueno',
                'requerimiento_oportuno' => 'Sí',
                'materiales_disponibles' => 'No',
                'califica_servicio_persona' => 'Excelente',
                'califica_calidad_materiales' => 'Deficiente',
                'opciones_cotizaciones' => 'Sí',
                'proveedores_cumplen' => 'No',
                'aspectos_destacados' => 'La amabilidad, colaboración y continua prestación del servicio',
                'oportunidades_mejora' => 'Hay un gran margen de mejora en la calidad de los elementos básico que se ofrecen',
                'uploaded_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'survey_period' => '2025-05',
                'timestamp' => '2025-05-15 10:30:00',
                'dependencia' => 'Docente',
                'califica_experiencia' => 'Bueno',
                'califica_tiempos' => 'Regular',
                'requerimiento_oportuno' => 'Sí',
                'materiales_disponibles' => 'Sí',
                'califica_servicio_persona' => 'Bueno',
                'califica_calidad_materiales' => 'Bueno',
                'opciones_cotizaciones' => 'Sí',
                'proveedores_cumplen' => 'Sí',
                'aspectos_destacados' => 'Atención personalizada',
                'oportunidades_mejora' => 'Mejorar tiempos de respuesta',
                'uploaded_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'survey_period' => '2025-05',
                'timestamp' => '2025-05-16 14:20:00',
                'dependencia' => 'Administrativo',
                'califica_experiencia' => 'Regular',
                'califica_tiempos' => 'Bueno',
                'requerimiento_oportuno' => 'No',
                'materiales_disponibles' => 'Sí',
                'califica_servicio_persona' => 'Bueno',
                'califica_calidad_materiales' => 'Regular',
                'opciones_cotizaciones' => 'No',
                'proveedores_cumplen' => 'Sí',
                'aspectos_destacados' => 'Disponibilidad del personal',
                'oportunidades_mejora' => 'Mejorar organización del inventario',
                'uploaded_by' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
