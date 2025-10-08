<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Empleado extends Model
{
    protected $fillable = [
        'nombre_completo',
        'documento',
        'email',
        'area',
        'eps',
        'sexo',
        'tipo_sangre',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    // Scope para empleados activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    // Scope para ordenar por nombre
    public function scopeOrdenadosPorNombre($query)
    {
        return $query->orderBy('nombre_completo');
    }

    // Normalizar sexo
    public static function normalizarSexo($sexo)
    {
        if (in_array($sexo, ['M', 'F'])) {
            return $sexo;
        }
        
        $sexoUpper = strtoupper($sexo);
        if (strpos($sexoUpper, 'M') !== false || strpos($sexoUpper, 'MASC') !== false) {
            return 'M';
        }
        if (strpos($sexoUpper, 'F') !== false || strpos($sexoUpper, 'FEM') !== false) {
            return 'F';
        }
        
        return null;
    }

    // Validar tipo de sangre
    public static function validarTipoSangre($tipoSangre)
    {
        $tiposValidos = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        $tipoSangre = strtoupper(trim($tipoSangre));
        
        return in_array($tipoSangre, $tiposValidos) ? $tipoSangre : null;
    }

    // Relación con ingresos de colaboradores
    public function ingresos()
    {
        return $this->hasMany(IngresoColaborador::class, 'empleado_id');
    }
}
