<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CentroCosto extends Model
{
    use HasFactory;

    protected $table = 'centros_costo';
    
    protected $fillable = [
        'codigo',
        'nombre',
        'seccion_id',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    /**
     * Relación con Sección
     */
    public function seccion()
    {
        return $this->belongsTo(Seccion::class);
    }

    /**
     * Relación con PresupuestoItems
     */
    public function presupuestoItems()
    {
        return $this->hasMany(PresupuestoItem::class, 'centro_costo', 'codigo');
    }

    /**
     * Scope para obtener centros de costo activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Obtener sección por código de centro de costo
     */
    public static function obtenerSeccionPorCodigo($codigo)
    {
        $centroCosto = self::where('codigo', $codigo)->first();
        return $centroCosto ? $centroCosto->seccion : null;
    }
}
