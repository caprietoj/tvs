<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresupuestoSeccion extends Model
{
    use HasFactory;

    protected $table = 'presupuesto_secciones';
    
    protected $fillable = [
        'seccion',
        'presupuesto_total',
        'year',
        'observaciones',
        'activo'
    ];

    protected $casts = [
        'presupuesto_total' => 'decimal:2',
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
     * Obtener presupuesto total para una sección
     */
    public static function obtenerPresupuestoTotal($seccion, $year = null)
    {
        $year = $year ?? date('Y');
        
        $presupuesto = self::where('seccion', $seccion)
                          ->where('year', $year)
                          ->where('activo', true)
                          ->first();
        
        return $presupuesto ? $presupuesto->presupuesto_total : 0;
    }

    /**
     * Obtener todos los presupuestos de secciones para un año
     */
    public static function obtenerTodosPresupuestos($year = null)
    {
        $year = $year ?? date('Y');
        
        $presupuestos = self::where('year', $year)
                          ->where('activo', true)
                          ->get();
        
        $result = [];
        foreach ($presupuestos as $presupuesto) {
            $result[$presupuesto->seccion] = (int) $presupuesto->presupuesto_total;
        }
        
        return $result;
    }
}
