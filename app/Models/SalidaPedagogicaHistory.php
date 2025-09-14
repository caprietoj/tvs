<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalidaPedagogicaHistory extends Model
{
    use HasFactory;

    protected $table = 'salida_pedagogica_histories';

    protected $fillable = [
        'salida_pedagogica_id',
        'user_id',
        'action',
        'changes',
        'notes',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    protected $appends = [
        'readable_changes'
    ];

    /**
     * Relación con la salida pedagógica
     */
    public function salidaPedagogica()
    {
        return $this->belongsTo(SalidaPedagogica::class, 'salida_pedagogica_id');
    }

    /**
     * Relación con el usuario que realizó la acción
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Crear un registro de historial
     */
    public static function logAction($salidaPedagogica, $action, $changes = null, $notes = null, $user = null)
    {
        $user = $user ?: auth()->user();
        
        return self::create([
            'salida_pedagogica_id' => $salidaPedagogica->id,
            'user_id' => $user ? $user->id : null,
            'action' => $action,
            'changes' => $changes,
            'notes' => $notes,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Obtener una descripción legible del cambio
     */
    public function getReadableChangesAttribute()
    {
        return $this->formatReadableChanges();
    }

    /**
     * Método público para formatear cambios legibles
     */
    public function formatReadableChanges()
    {
        $changes = $this->getAttribute('changes');
        
        if (!$changes || !is_array($changes) || empty($changes)) {
            return null;
        }

        $fieldLabels = [
            'grados' => 'Grados',
            'lugar' => 'Lugar',
            'responsable_id' => 'Responsable',
            'fecha_salida' => 'Fecha de salida',
            'fecha_regreso' => 'Fecha de regreso',
            'cantidad_pasajeros' => 'Cantidad de pasajeros',
            'observaciones' => 'Observaciones',
            'calendario_general' => 'Calendario general',
            'visita_inspeccion' => 'Visita de inspección',
            'detalles_inspeccion' => 'Detalles de inspección',
            'contacto_lugar' => 'Contacto del lugar',
            'requiere_alimentacion' => 'Requiere alimentación',
            'cantidad_snacks' => 'Cantidad de snacks',
            'cantidad_almuerzos' => 'Cantidad de almuerzos',
            'hora_entrega_alimentos' => 'Hora de entrega de alimentos',
            'menu_sugerido' => 'Menú sugerido',
            'observaciones_dieteticas' => 'Observaciones dietéticas',
            'hora_apertura_puertas' => 'Hora de apertura de puertas',
            'requiere_enfermeria' => 'Requiere enfermería',
            'requiere_comunicaciones' => 'Requiere comunicaciones',
            'requiere_arl' => 'Requiere ARL',
            'observaciones_comunicaciones' => 'Observaciones de comunicaciones',
            'estado' => 'Estado',
        ];

        $descriptions = [];
        
        foreach ($changes as $field => $change) {
            if (isset($change['old']) && isset($change['new'])) {
                $fieldName = $fieldLabels[$field] ?? $field;
                $oldValue = $change['old'] ?? '(vacío)';
                $newValue = $change['new'] ?? '(vacío)';
                
                // Formatear campos específicos
                if ($field === 'hora_entrega_alimentos' || $field === 'hora_apertura_puertas') {
                    $oldValue = $oldValue ? date('H:i', strtotime($oldValue)) : '(vacío)';
                    $newValue = $newValue ? date('H:i', strtotime($newValue)) : '(vacío)';
                }
                
                if (in_array($field, ['requiere_alimentacion', 'requiere_enfermeria', 'requiere_comunicaciones', 'requiere_arl', 'calendario_general', 'visita_inspeccion'])) {
                    $oldValue = $oldValue ? 'Sí' : 'No';
                    $newValue = $newValue ? 'Sí' : 'No';
                }
                
                if ($oldValue !== $newValue) {
                    $descriptions[] = "{$fieldName}: {$oldValue} → {$newValue}";
                }
            }
        }

        return implode(', ', $descriptions);
    }

    /**
     * Formatear valores para mostrar
     */
    private function formatValue($value, $field)
    {
        // Campos booleanos
        if (in_array($field, ['calendario_general', 'visita_inspeccion', 'requiere_alimentacion', 'requiere_enfermeria', 'requiere_comunicaciones', 'requiere_arl'])) {
            return $value ? 'Sí' : 'No';
        }

        // Campos de fecha/hora
        if (in_array($field, ['fecha_salida', 'fecha_regreso']) && $value) {
            try {
                return \Carbon\Carbon::parse($value)->format('d/m/Y H:i');
            } catch (\Exception $e) {
                return $value;
            }
        }

        // Campos de hora
        if (in_array($field, ['hora_entrega_alimentos', 'hora_apertura_puertas']) && $value) {
            try {
                return date('H:i', strtotime($value));
            } catch (\Exception $e) {
                return $value;
            }
        }

        // Responsable (buscar nombre del usuario)
        if ($field === 'responsable_id' && $value) {
            $user = \App\Models\User::find($value);
            return $user ? $user->name : "Usuario ID: {$value}";
        }

        // Valores nulos o vacíos
        if (is_null($value) || $value === '') {
            return '(vacío)';
        }

        return $value;
    }

    /**
     * Scope para filtrar por acción
     */
    public function scopeByAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope para filtrar por usuario
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope para ordenar por fecha más reciente
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
