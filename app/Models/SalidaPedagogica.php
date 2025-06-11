<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalidaPedagogica extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'salidas_pedagogicas';

    protected $fillable = [
        'consecutivo',
        'fecha_solicitud',
        'calendario_general',
        'grados',
        'lugar',
        'responsable_id',
        'fecha_salida',
        'fecha_regreso',
        'cantidad_pasajeros',
        'observaciones',
        'visita_inspeccion',
        'detalles_inspeccion',
        'contacto_lugar',
        'transporte_confirmado',
        'hora_salida_bus',
        'hora_regreso_bus',
        'requiere_alimentacion',
        'cantidad_snacks',
        'cantidad_almuerzos',
        'hora_entrega_alimentos',
        'menu_sugerido',
        'observaciones_dieteticas',
        'alimentacion_confirmada',
        'hora_apertura_puertas',
        'accesos_confirmados',
        'requiere_enfermeria',
        'enfermeria_confirmada',
        'observaciones_medicas',
        'requiere_comunicaciones',
        'observaciones_comunicaciones',
        'requiere_arl',
        'estado',
        'motivo_cancelacion'
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'fecha_salida' => 'datetime',
        'fecha_regreso' => 'datetime',
        'calendario_general' => 'boolean',
        'visita_inspeccion' => 'boolean',
        'transporte_confirmado' => 'boolean',
        'requiere_alimentacion' => 'boolean',
        'alimentacion_confirmada' => 'boolean',
        'accesos_confirmados' => 'boolean',
        'requiere_enfermeria' => 'boolean',
        'enfermeria_confirmada' => 'boolean',
        'requiere_comunicaciones' => 'boolean',
        'requiere_arl' => 'boolean'
    ];

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($salida) {
            if (!$salida->consecutivo) {
                $lastId = static::withTrashed()->max('id') ?? 0;
                $salida->consecutivo = 'S-' . str_pad($lastId + 1, 3, '0', STR_PAD_LEFT);
            }
            if (!$salida->fecha_solicitud) {
                $salida->fecha_solicitud = now();
            }
        });
    }
}
