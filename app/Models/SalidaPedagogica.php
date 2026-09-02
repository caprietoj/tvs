<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalidaPedagogica extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'salidas_pedagogicas';

    // Variable estática para almacenar cambios temporalmente durante la actualización
    protected static $pendingChanges = [];

    // Flag para evitar logging duplicado cuando el controller crea entrada manual
    protected static $skipUpdatedHistory = false;

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
        'transporte_confirmado_por',
        'transporte_confirmado_at',
        'hora_salida_bus',
        'hora_regreso_bus',
        'requiere_alimentacion',
        'cantidad_snacks',
        'cantidad_almuerzos',
        'hora_entrega_alimentos',
        'menu_sugerido',
        'observaciones_dieteticas',
        'alimentacion_confirmada',
        'alimentacion_confirmada_por',
        'alimentacion_confirmada_at',
        'hora_apertura_puertas',
        'accesos_confirmados',
        'accesos_confirmados_por',
        'accesos_confirmados_at',
        'requiere_enfermeria',
        'enfermeria_confirmada',
        'enfermeria_confirmada_por',
        'enfermeria_confirmada_at',
        'observaciones_medicas',
        'requiere_comunicaciones',
        'comunicaciones_confirmada',
        'comunicaciones_confirmado_por',
        'comunicaciones_confirmado_at',
        'observaciones_comunicaciones',
        'requiere_arl',
        'arl_confirmado',
        'arl_confirmado_por',
        'arl_confirmado_at',
        'estado',
        'motivo_cancelacion'
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'fecha_salida' => 'datetime',
        'fecha_regreso' => 'datetime',
        'transporte_confirmado_at' => 'datetime',
        'alimentacion_confirmada_at' => 'datetime',
        'accesos_confirmados_at' => 'datetime',
        'enfermeria_confirmada_at' => 'datetime',
        'comunicaciones_confirmado_at' => 'datetime',
        'arl_confirmado_at' => 'datetime',
        'calendario_general' => 'boolean',
        'visita_inspeccion' => 'boolean',
        'transporte_confirmado' => 'boolean',
        'requiere_alimentacion' => 'boolean',
        'alimentacion_confirmada' => 'boolean',
        'accesos_confirmados' => 'boolean',
        'requiere_enfermeria' => 'boolean',
        'enfermeria_confirmada' => 'boolean',
        'requiere_comunicaciones' => 'boolean',
        'comunicaciones_confirmada' => 'boolean',
        'requiere_arl' => 'boolean',
        'arl_confirmado' => 'boolean'
    ];

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    // Relaciones con usuarios que confirmaron cada servicio
    public function transporteConfirmadoPor()
    {
        return $this->belongsTo(User::class, 'transporte_confirmado_por');
    }

    public function alimentacionConfirmadaPor()
    {
        return $this->belongsTo(User::class, 'alimentacion_confirmada_por');
    }

    public function accesosConfirmadosPor()
    {
        return $this->belongsTo(User::class, 'accesos_confirmados_por');
    }

    public function enfermeriaConfirmadaPor()
    {
        return $this->belongsTo(User::class, 'enfermeria_confirmada_por');
    }

    public function comunicacionesConfirmadoPor()
    {
        return $this->belongsTo(User::class, 'comunicaciones_confirmado_por');
    }

    public function arlConfirmadoPor()
    {
        return $this->belongsTo(User::class, 'arl_confirmado_por');
    }

    /**
     * Relación con el historial de cambios
     */
    public function history()
    {
        return $this->hasMany(SalidaPedagogicaHistory::class, 'salida_pedagogica_id');
    }

    /**
     * Actualizar el estado automáticamente basado en la fecha de salida
     */
    public function updateEstadoAutomatico()
    {
        if ($this->estado === 'Programada' && $this->fecha_salida && $this->fecha_salida->isPast()) {
            $this->update(['estado' => 'Realizada']);
        }
    }

    /**
     * Accessor para obtener el estado actualizado automáticamente
     */
    public function getEstadoAttribute($value)
    {
        // Si el estado es 'Programada' y la fecha ya pasó, actualizar automáticamente
        if ($value === 'Programada' && $this->fecha_salida && $this->fecha_salida->isPast()) {
            $this->updateQuietly(['estado' => 'Realizada']);
            return 'Realizada';
        }
        
        return $value;
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

        // Registrar cuando se crea una nueva salida pedagógica
        static::created(function ($salida) {
            SalidaPedagogicaHistory::logAction(
                $salida,
                'created',
                null,
                'Salida pedagógica creada'
            );
        });

        // Registrar los cambios antes de actualizar
        static::updating(function ($salida) {
            $changes = [];
            $original = $salida->getOriginal();
            $dirty = $salida->getDirty();

            // Campos que queremos trackear específicamente
            $trackedFields = [
                'grados', 'lugar', 'responsable_id', 'fecha_salida', 'fecha_regreso',
                'cantidad_pasajeros', 'observaciones', 'calendario_general',
                'visita_inspeccion', 'detalles_inspeccion', 'contacto_lugar',
                'requiere_alimentacion', 'cantidad_snacks', 'cantidad_almuerzos',
                'hora_entrega_alimentos', 'menu_sugerido', 'observaciones_dieteticas',
                'hora_apertura_puertas', 'requiere_enfermeria',
                'requiere_comunicaciones', 'requiere_arl', 'observaciones_comunicaciones',
                'estado'
            ];

            foreach ($trackedFields as $field) {
                if (array_key_exists($field, $dirty) && $dirty[$field] !== $original[$field]) {
                    $changes[$field] = [
                        'old' => $original[$field],
                        'new' => $dirty[$field]
                    ];
                }
            }

            // Guardar los cambios en la variable estática usando el ID del modelo
            if (!empty($changes)) {
                static::$pendingChanges[$salida->id] = $changes;
            }
        });

        // Registrar los cambios después de actualizar
        static::updated(function ($salida) {
            // Si el controller va a crear una entrada manual, skip este logging
            if (static::$skipUpdatedHistory) {
                return;
            }

            if (isset(static::$pendingChanges[$salida->id]) && !empty(static::$pendingChanges[$salida->id])) {
                SalidaPedagogicaHistory::logAction(
                    $salida,
                    'updated',
                    static::$pendingChanges[$salida->id],
                    'Salida pedagógica actualizada'
                );
                
                // Limpiar los cambios pendientes para este modelo
                unset(static::$pendingChanges[$salida->id]);
            }
        });

        // Registrar cuando se elimina una salida pedagógica
        static::deleted(function ($salida) {
            SalidaPedagogicaHistory::logAction(
                $salida,
                'deleted',
                null,
                'Salida pedagógica eliminada'
            );
        });

        // Actualizar estado automáticamente al cargar el modelo
        static::retrieved(function ($salida) {
            $salida->updateEstadoAutomatico();
        });
    }

    /**
     * Obtener color según el estado para mostrar en calendario
     */
    public static function skipUpdatedHistory($skip = true)
    {
        static::$skipUpdatedHistory = $skip;
    }

    /**
     * Obtener los cambios pendientes de la última actualización
     */
    public static function getPendingChanges($salidaId)
    {
        return static::$pendingChanges[$salidaId] ?? null;
    }

    /**
     * Limpiar los cambios pendientes
     */
    public static function clearPendingChanges($salidaId)
    {
        unset(static::$pendingChanges[$salidaId]);
    }

    /**
     * Obtener color según el estado para mostrar en calendario
     */
    public function getStatusColor()
    {
        switch ($this->estado) {
            case 'Programada':
                return '#007bff'; // Azul para programadas
            case 'Realizada':
                return '#28a745'; // Verde para realizadas
            case 'Cancelada':
            case 'cancelada':
                return '#dc3545'; // Rojo para canceladas
            case 'En Proceso':
                return '#ffc107'; // Amarillo para en proceso
            default:
                return '#6c757d'; // Gris por defecto
        }
    }
}
