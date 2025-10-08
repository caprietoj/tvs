<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class MotivoEnfermeria extends Model
{
    use HasFactory;

    protected $table = 'motivos_enfermeria';

    protected $fillable = [
        'nombre',
        'codigo_cie10',
        'categoria',
        'descripcion',
        'icono',
        'activo',
        'orden'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'orden' => 'integer'
    ];

    // Scope para obtener solo motivos activos
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('activo', true);
    }

    // Scope para ordenar por campo orden
    public function scopeOrdenados(Builder $query): Builder
    {
        return $query->orderBy('orden')->orderBy('nombre');
    }

    // Accessor para mostrar nombre con icono
    public function getNombreCompletoAttribute(): string
    {
        return $this->icono ? $this->icono . ' ' . $this->nombre : $this->nombre;
    }

    // Método estático para obtener motivos activos para select
    public static function paraSelect(): array
    {
        return self::activos()
            ->ordenados()
            ->get()
            ->pluck('nombre_completo', 'nombre')
            ->toArray();
    }
}
