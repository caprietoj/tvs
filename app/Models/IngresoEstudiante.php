<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IngresoEstudiante extends Model
{
    use HasFactory;

    protected $fillable = [
        'fecha',
        'hora',
        'estudiante',
        'codigo_estudiante',
        'documento_estudiante',
        'apellidos_estudiante',
        'eps_estudiante',
        'sexo_estudiante',
        'tipo_sangre_estudiante',
        'estudiante_id',
        'curso',
        'viene_de',
        'motivo',
        'descripcion_evento',
        'accion_enfermeria',
        'seguimiento',
        'derivacion_estudiante',
        'encuesta',
        'encuesta_observaciones',
        'reporte_direccion_educacion',
        'user_id'
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    /**
     * Relación con el usuario que registra
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relación con el estudiante
     */
    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Estudiante::class, 'estudiante_id');
    }

    /**
     * Scope para ordenar por fecha y hora descendente
     */
    public function scopeRecientes($query)
    {
        return $query->orderBy('fecha', 'desc')->orderBy('hora', 'desc');
    }

    /**
     * Accessor para mostrar fecha y hora formateada
     */
    public function getFechaHoraFormateadaAttribute()
    {
        return $this->fecha->format('d/m/Y') . ' ' . $this->hora->format('H:i');
    }
}
