<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IngresoColaborador extends Model
{
    protected $table = 'ingreso_colaboradores';

    protected $fillable = [
        'empleado_id',
        'fecha',
        'hora',
        'nombre_completo',
        'email',
        'documento_colaborador',
        'area_colaborador',
        'eps_colaborador',
        'sexo_colaborador',
        'tipo_sangre_colaborador',
        'motivo',
        'descripcion_evento',
        'accion_enfermeria',
        'seguimiento',
        'derivacion_colaborador',
        'encuesta',
        'encuesta_observaciones',
        'user_id'
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora' => 'datetime:H:i',
    ];

    // Relación con empleado
    public function empleado()
    {
        return $this->belongsTo(Empleado::class);
    }

    // Relación con usuario que registra
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
