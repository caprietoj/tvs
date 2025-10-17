<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Persona extends Model
{
    use HasFactory;

    /**
     * Nombre de la tabla
     */
    protected $table = 'personas';

    /**
     * Campos asignables en masa
     */
    protected $fillable = [
        'documento',
        'nombre',
        'tipo_persona',
        'email',
        'telefono',
        'grado',
        'observaciones',
        'activo',
    ];

    /**
     * Campos de tipo cast
     */
    protected $casts = [
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Accessor para grado
     */
    public function getGradoAttribute($value)
    {
        return $value;
    }

    /**
     * Accessor para tipo_persona - Determina automáticamente según el grado
     */
    public function getTipoPersonaAttribute($value)
    {
        $grado = $this->grado; // Usa el accessor de grado
        
        if (!empty($grado)) {
            $cargosEmpleado = ['Administracion', 'Docente', 'Coordinacion', 'Asistente',
                'Servicios', 'Biblioteca', 'Enfermeria', 'Mantenimiento', 'Sistemas', 
                'Contabilidad', 'Rectoria', 'Rectoría', 'Secretaria', 'Pastoral'];
            
            foreach ($cargosEmpleado as $cargo) {
                if (stripos($grado, $cargo) !== false) {
                    return 'empleado';
                }
            }
            
            // Si contiene números, es estudiante
            if (preg_match('/\d+/', $grado)) {
                return 'estudiante';
            }
        }
        
        // Retornar el valor original de la BD
        return $value;
    }

    /**
     * Accessor para nombre completo
     */
    public function getNombreCompletoAttribute(): string
    {
        // El nombre ya contiene el nombre completo
        return trim($this->nombre);
    }

    /**
     * Scope para personas activas
     */
    public function scopeActivas($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para empleados
     */
    public function scopeEmpleados($query)
    {
        return $query->where('tipo_persona', 'empleado');
    }

    /**
     * Scope para estudiantes
     */
    public function scopeEstudiantes($query)
    {
        return $query->where('tipo_persona', 'estudiante');
    }

    /**
     * Scope para búsqueda por documento
     */
    public function scopeBuscarPorDocumento($query, string $documento)
    {
        return $query->where('documento', $documento);
    }

    /**
     * Scope para búsqueda general
     */
    public function scopeBuscar($query, string $termino)
    {
        return $query->where(function($q) use ($termino) {
            $q->where('documento', 'LIKE', "%{$termino}%")
              ->orWhere('nombre', 'LIKE', "%{$termino}%")
              ->orWhere('apellido', 'LIKE', "%{$termino}%")
              ->orWhere('email', 'LIKE', "%{$termino}%");
        });
    }

    /**
     * Relación con registros de portería
     */
    public function registrosPorteria()
    {
        return $this->hasMany(RegistroPorteria::class, 'documento', 'documento');
    }

    /**
     * Obtener badge HTML según tipo de persona
     */
    public function getTipoBadgeAttribute(): string
    {
        $badges = [
            'empleado' => '<span class="badge badge-primary">Empleado</span>',
            'estudiante' => '<span class="badge badge-success">Estudiante</span>',
        ];
        
        return $badges[$this->tipo_persona] ?? '';
    }

    /**
     * Obtener badge de estado
     */
    public function getEstadoBadgeAttribute(): string
    {
        return $this->activo 
            ? '<span class="badge badge-success">Activo</span>'
            : '<span class="badge badge-secondary">Inactivo</span>';
    }
}
