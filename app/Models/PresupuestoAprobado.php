<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresupuestoAprobado extends Model
{
    use HasFactory;

    protected $table = 'presupuesto_aprobado';
    
    protected $fillable = [
        'seccion',
        'concepto',
        'monto_aprobado',
        'year',
        'observaciones',
        'activo'
    ];

    protected $casts = [
        'monto_aprobado' => 'decimal:2',
        'activo' => 'boolean'
    ];

    /**
     * Scope para obtener presupuestos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para obtener presupuestos por año
     */
    public function scopePorYear($query, $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope para obtener presupuestos por sección
     */
    public function scopePorSeccion($query, $seccion)
    {
        return $query->where('seccion', $seccion);
    }

    /**
     * Obtener presupuesto específico para un concepto y sección
     */
    public static function obtenerPresupuesto($seccion, $concepto, $year = null)
    {
        $year = $year ?? date('Y');
        
        $presupuesto = self::where('seccion', $seccion)
                          ->where('concepto', $concepto)
                          ->where('year', $year)
                          ->where('activo', true)
                          ->first();
        
        return $presupuesto ? $presupuesto->monto_aprobado : 0;
    }

    /**
     * Obtener todos los presupuestos por sección para un año
     */
    public static function obtenerPresupuestosPorSeccion($seccion, $year = null)
    {
        $year = $year ?? date('Y');
        
        return self::where('seccion', $seccion)
                  ->where('year', $year)
                  ->where('activo', true)
                  ->pluck('monto_aprobado', 'concepto')
                  ->toArray();
    }
}
