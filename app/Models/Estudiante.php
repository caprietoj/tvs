<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    use HasFactory;

    protected $fillable = [
        'curso',
        'nombre',
        'apellido_1',
        'apellido_2',
        'codigo',
        'documento',
        'eps',
        'sexo',
        'tipo_sangre',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    /**
     * Accessor para nombre completo
     */
    public function getNombreCompletoAttribute()
    {
        return trim($this->nombre . ' ' . $this->apellido_1 . ' ' . ($this->apellido_2 ?? ''));
    }

    /**
     * Scope para estudiantes activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para buscar por curso
     */
    public function scopePorCurso($query, $curso)
    {
        return $query->where('curso', $curso);
    }

    /**
     * Scope para ordenar por nombre
     */
    public function scopeOrdenadosPorNombre($query)
    {
        return $query->orderBy('apellido_1')->orderBy('apellido_2')->orderBy('nombre');
    }

    /**
     * Método para normalizar el sexo
     */
    public static function normalizarSexo($sexo)
    {
        $sexo = strtolower(trim($sexo));
        
        if (in_array($sexo, ['m', 'masculino', 'hombre', 'male'])) {
            return 'M';
        } elseif (in_array($sexo, ['f', 'femenino', 'mujer', 'female'])) {
            return 'F';
        }
        
        return $sexo;
    }

    /**
     * Método para validar tipo de sangre
     */
    public static function validarTipoSangre($tipo)
    {
        $tiposValidos = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        $tipo = strtoupper(trim($tipo));
        
        return in_array($tipo, $tiposValidos) ? $tipo : null;
    }
}
