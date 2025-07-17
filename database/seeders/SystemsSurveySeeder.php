<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\SystemsSurveyResult;
use Carbon\Carbon;

class SystemsSurveySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Datos de ejemplo para diferentes meses
        $sampleData = [
            // Junio 2025
            [
                'response_timestamp' => '2025-06-06 12:12:14',
                'dependencia' => 'Academico',
                'tiempos_respuesta' => 'Buena',
                'efectividad_tecnica' => 'Efectiva',
                'profesionalismo' => 'Excelente',
                'comentarios_personal' => '',
                'estado_equipos' => 'Regular',
                'comentarios_equipos' => '',
                'apoyo_usabilidad' => 'Bueno',
                'plataformas_interaccion' => 'Phidias',
                'otra_plataforma' => '',
                'calidad_internet' => 'Deficiente',
                'problemas_conectividad' => 'Edificio bachillerato 4to piso',
                'intervencion_eventos' => 'Excelente',
                'comentarios_eventos' => '',
                'aspectos_destacados' => 'La cordialidad y la siempre disposición a colaborar y resolver los inconvenientes que surgen',
                'oportunidades_mejora' => 'La actualización y mejora de equipos de la institución es una necesidad tanto como para los estudiantes como para los docentes.',
                'survey_year' => 2025,
                'survey_month' => 6
            ],
            [
                'response_timestamp' => '2025-06-06 12:14:09',
                'dependencia' => 'Academico',
                'tiempos_respuesta' => 'Excelente',
                'efectividad_tecnica' => 'Muy efectiva',
                'profesionalismo' => 'Excelente',
                'comentarios_personal' => '',
                'estado_equipos' => 'Bueno',
                'comentarios_equipos' => '',
                'apoyo_usabilidad' => 'Excelente',
                'plataformas_interaccion' => 'Phidias',
                'otra_plataforma' => '',
                'calidad_internet' => 'Buena',
                'problemas_conectividad' => 'Cuando hay cortes intermitentes de energía.',
                'intervencion_eventos' => 'Excelente',
                'comentarios_eventos' => '',
                'aspectos_destacados' => 'Eficiencia, servicio y amabilidad.',
                'oportunidades_mejora' => 'En ocasiones no se cuentan con suficientes equipos para las lecciones planeadas.',
                'survey_year' => 2025,
                'survey_month' => 6
            ],
            [
                'response_timestamp' => '2025-06-06 13:10:13',
                'dependencia' => 'Administrativo',
                'tiempos_respuesta' => 'Buena',
                'efectividad_tecnica' => 'Efectiva',
                'profesionalismo' => 'Bueno',
                'comentarios_personal' => '',
                'estado_equipos' => 'Bueno',
                'comentarios_equipos' => '',
                'apoyo_usabilidad' => 'Bueno',
                'plataformas_interaccion' => 'Phidias',
                'otra_plataforma' => '',
                'calidad_internet' => 'Regular',
                'problemas_conectividad' => 'Área de enfermería cuando se llama a los padres de familia se finalizan las llamadas',
                'intervencion_eventos' => 'Buenas',
                'comentarios_eventos' => '',
                'aspectos_destacados' => 'Su disposición para resolver situaciones de dificultad y resolución del mismo',
                'oportunidades_mejora' => 'Respuesta en los teléfonos asignados',
                'survey_year' => 2025,
                'survey_month' => 6
            ],
            // Mayo 2025
            [
                'response_timestamp' => '2025-05-15 10:30:00',
                'dependencia' => 'Academico',
                'tiempos_respuesta' => 'Excelente',
                'efectividad_tecnica' => 'Muy efectiva',
                'profesionalismo' => 'Excelente',
                'comentarios_personal' => 'Excelente atención',
                'estado_equipos' => 'Excelente',
                'comentarios_equipos' => 'Equipos en buen estado',
                'apoyo_usabilidad' => 'Excelente',
                'plataformas_interaccion' => 'Phidias',
                'otra_plataforma' => '',
                'calidad_internet' => 'Excelente',
                'problemas_conectividad' => 'Ninguno',
                'intervencion_eventos' => 'Excelente',
                'comentarios_eventos' => 'Muy bien organizados',
                'aspectos_destacados' => 'Profesionalismo y eficiencia',
                'oportunidades_mejora' => 'Ninguna',
                'survey_year' => 2025,
                'survey_month' => 5
            ],
            [
                'response_timestamp' => '2025-05-20 14:45:00',
                'dependencia' => 'Administrativo',
                'tiempos_respuesta' => 'Buena',
                'efectividad_tecnica' => 'Efectiva',
                'profesionalismo' => 'Bueno',
                'comentarios_personal' => 'Buen servicio',
                'estado_equipos' => 'Regular',
                'comentarios_equipos' => 'Algunos equipos necesitan actualización',
                'apoyo_usabilidad' => 'Bueno',
                'plataformas_interaccion' => 'Zeus',
                'otra_plataforma' => '',
                'calidad_internet' => 'Regular',
                'problemas_conectividad' => 'Oficinas administrativas',
                'intervencion_eventos' => 'Buenas',
                'comentarios_eventos' => '',
                'aspectos_destacados' => 'Amabilidad del personal',
                'oportunidades_mejora' => 'Mejorar la conectividad en oficinas',
                'survey_year' => 2025,
                'survey_month' => 5
            ]
        ];

        foreach ($sampleData as $data) {
            SystemsSurveyResult::create($data);
        }
    }
}
