<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cuenta extends Model
{
    use HasFactory;

    protected $table = 'cuentas';
    
    protected $fillable = [
        'codigo',
        'nombre',
        'rubro_id',
        'activa'
    ];

    protected $casts = [
        'activa' => 'boolean'
    ];

    /**
     * Relación con Rubro
     */
    public function rubro()
    {
        return $this->belongsTo(Rubro::class);
    }

    /**
     * Relación con PresupuestoItems
     */
    public function presupuestoItems()
    {
        return $this->hasMany(PresupuestoItem::class, 'cuenta', 'codigo');
    }

    /**
     * Scope para obtener cuentas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }

    /**
     * Obtener rubro por código de cuenta
     */
    public static function obtenerRubroPorCodigo($codigo)
    {
        $cuenta = self::where('codigo', $codigo)->first();
        return $cuenta ? $cuenta->rubro : null;
    }
}
