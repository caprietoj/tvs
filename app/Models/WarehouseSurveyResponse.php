<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseSurveyResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_period',
        'timestamp',
        'dependencia',
        'califica_experiencia',
        'califica_tiempos',
        'requerimiento_oportuno',
        'materiales_disponibles',
        'comentarios_disponibilidad',
        'califica_servicio_persona',
        'califica_calidad_materiales',
        'comentarios_calidad',
        'opciones_cotizaciones',
        'comentarios_cotizaciones',
        'proveedores_cumplen',
        'comentarios_proveedores',
        'aspectos_destacados',
        'oportunidades_mejora',
        'uploaded_by'
    ];

    protected $casts = [
        'timestamp' => 'datetime',
    ];

    /**
     * Relación con el usuario que subió la encuesta
     */
    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Obtener el puntaje numérico para una respuesta de calificación
     */
    public static function getScoreForRating($rating)
    {
        $scores = [
            'Deficiente' => 1,
            'Regular' => 2,
            'Bueno' => 3,
            'Excelente' => 4
        ];
        
        return $scores[$rating] ?? 0;
    }

    /**
     * Obtener el puntaje numérico para una respuesta sí/no
     */
    public static function getScoreForYesNo($response)
    {
        return $response === 'Sí' ? 4 : 1;
    }

    /**
     * Calcular el promedio de satisfacción para una respuesta
     */
    public function calculateSatisfactionAverage()
    {
        $scores = [
            self::getScoreForRating($this->califica_experiencia),
            self::getScoreForRating($this->califica_tiempos),
            self::getScoreForYesNo($this->requerimiento_oportuno),
            self::getScoreForYesNo($this->materiales_disponibles),
            self::getScoreForRating($this->califica_servicio_persona),
            self::getScoreForRating($this->califica_calidad_materiales),
            self::getScoreForYesNo($this->opciones_cotizaciones),
            self::getScoreForYesNo($this->proveedores_cumplen),
        ];

        return array_sum($scores) / count($scores);
    }

    /**
     * Obtener estadísticas por período
     */
    public static function getStatisticsByPeriod($period)
    {
        return self::where('survey_period', $period)
            ->selectRaw('
                COUNT(*) as total_responses,
                AVG(
                    (CASE califica_experiencia
                        WHEN "Deficiente" THEN 1
                        WHEN "Regular" THEN 2
                        WHEN "Bueno" THEN 3
                        WHEN "Excelente" THEN 4
                        ELSE 0
                    END +
                    CASE califica_tiempos
                        WHEN "Deficiente" THEN 1
                        WHEN "Regular" THEN 2
                        WHEN "Bueno" THEN 3
                        WHEN "Excelente" THEN 4
                        ELSE 0
                    END +
                    CASE requerimiento_oportuno WHEN "Sí" THEN 4 ELSE 1 END +
                    CASE materiales_disponibles WHEN "Sí" THEN 4 ELSE 1 END +
                    CASE califica_servicio_persona
                        WHEN "Deficiente" THEN 1
                        WHEN "Regular" THEN 2
                        WHEN "Bueno" THEN 3
                        WHEN "Excelente" THEN 4
                        ELSE 0
                    END +
                    CASE califica_calidad_materiales
                        WHEN "Deficiente" THEN 1
                        WHEN "Regular" THEN 2
                        WHEN "Bueno" THEN 3
                        WHEN "Excelente" THEN 4
                        ELSE 0
                    END +
                    CASE opciones_cotizaciones WHEN "Sí" THEN 4 ELSE 1 END +
                    CASE proveedores_cumplen WHEN "Sí" THEN 4 ELSE 1 END) / 8
                ) * 25 as satisfaction_percentage
            ')
            ->first();
    }
}
