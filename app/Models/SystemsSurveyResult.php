<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SystemsSurveyResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'response_timestamp',
        'dependencia',
        'tiempos_respuesta',
        'efectividad_tecnica',
        'profesionalismo',
        'comentarios_personal',
        'estado_equipos',
        'comentarios_equipos',
        'apoyo_usabilidad',
        'plataformas_interaccion',
        'otra_plataforma',
        'calidad_internet',
        'problemas_conectividad',
        'intervencion_eventos',
        'comentarios_eventos',
        'aspectos_destacados',
        'oportunidades_mejora',
        'survey_year',
        'survey_month'
    ];

    protected $casts = [
        'response_timestamp' => 'datetime',
        'survey_year' => 'integer',
        'survey_month' => 'integer'
    ];

    public function getScoreValue($field)
    {
        $scaleMapping = [
            'Excelente' => 5,
            'Muy efectiva' => 5,
            'Buena' => 4,
            'Bueno' => 4,
            'Efectiva' => 4,
            'Regular' => 3,
            'Malo' => 2,
            'Deficiente' => 1
        ];

        return $scaleMapping[$this->$field] ?? 0;
    }

    public function getMonthName()
    {
        $months = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        return $months[$this->survey_month] ?? '';
    }
}
