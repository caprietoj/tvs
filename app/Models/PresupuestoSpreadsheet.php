<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresupuestoSpreadsheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'tabla_nombre',
        'concepto', 
        'columna',
        'valor',
        'fila_orden',
        'columna_orden',
        'es_total',
        'tipo_dato'
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'es_total' => 'boolean',
        'fila_orden' => 'integer',
        'columna_orden' => 'integer',
    ];

    // Scopes para filtrar datos
    public function scopePorTabla($query, $tabla)
    {
        return $query->where('tabla_nombre', $tabla);
    }

    public function scopePorConcepto($query, $concepto)
    {
        return $query->where('concepto', $concepto);
    }

    public function scopePorColumna($query, $columna)
    {
        return $query->where('columna', $columna);
    }

    public function scopeSinTotales($query)
    {
        return $query->where('es_total', false);
    }

    public function scopeSoloTotales($query)
    {
        return $query->where('es_total', true);
    }

    public function scopeOrdenadoPorFila($query)
    {
        return $query->orderBy('fila_orden');
    }

    public function scopeOrdenadoPorColumna($query)
    {
        return $query->orderBy('columna_orden');
    }

    // Método estático para obtener o crear una celda
    public static function obtenerOCrearCelda($tablaNombre, $concepto, $columna, $esTotal = false)
    {
        return self::firstOrCreate(
            [
                'tabla_nombre' => $tablaNombre,
                'concepto' => $concepto,
                'columna' => $columna
            ],
            [
                'valor' => 0,
                'es_total' => $esTotal,
                'tipo_dato' => $esTotal ? 'calculado' : 'manual'
            ]
        );
    }
}
