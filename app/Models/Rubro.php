<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rubro extends Model
{
    use HasFactory;

    protected $table = 'rubros';
    
    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    /**
     * Relación con Cuentas
     */
    public function cuentas()
    {
        return $this->hasMany(Cuenta::class);
    }

    /**
     * Relación con PresupuestoItems
     */
    public function presupuestoItems()
    {
        return $this->hasMany(PresupuestoItem::class, 'rubro', 'nombre');
    }

    /**
     * Scope para obtener rubros activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
