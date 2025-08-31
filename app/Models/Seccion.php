<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seccion extends Model
{
    use HasFactory;

    protected $table = 'secciones';
    
    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'activa'
    ];

    protected $casts = [
        'activa' => 'boolean'
    ];

    /**
     * Relación con CentrosCosto
     */
    public function centrosCosto()
    {
        return $this->hasMany(CentroCosto::class);
    }

    /**
     * Relación con PresupuestoItems
     */
    public function presupuestoItems()
    {
        return $this->hasMany(PresupuestoItem::class, 'seccion', 'nombre');
    }

    /**
     * Scope para obtener secciones activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }
}
