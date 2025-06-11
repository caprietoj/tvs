<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EvaluacionProveedor extends Model
{
    use HasFactory;

    protected $table = 'evaluacion_proveedores';

    protected $fillable = [
        'proveedor_id',
        'numero_contrato',
        'fecha_evaluacion',
        'lugar_evaluacion',
        'cumplimiento_entrega',
        'calidad_especificaciones',
        'documentacion_garantias',
        'servicio_postventa',
        'precio',
        'capacidad_instalada',
        'soporte_tecnico',
        'puntaje_total',
        'observaciones',
        'evaluado_por'
    ];

    protected $casts = [
        'fecha_evaluacion' => 'date',
    ];

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function calcularPuntajeTotal()
    {
        return ($this->cumplimiento_entrega + 
                $this->calidad_especificaciones + 
                $this->documentacion_garantias + 
                $this->servicio_postventa + 
                $this->precio + 
                $this->capacidad_instalada + 
                $this->soporte_tecnico) / 7;
    }
}
