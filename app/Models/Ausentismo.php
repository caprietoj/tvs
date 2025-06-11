<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ausentismo extends Model
{
    protected $fillable = [
        'persona',
        'fecha_de_creacion',
        'dependencia',
        'fecha_ausencia_desde',
        'fecha_hasta',
        'asistencia',
        'duracion_ausencia',
        'motivo_de_ausencia',
        'mes'
    ];
}
