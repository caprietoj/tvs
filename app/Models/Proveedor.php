<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'proveedors';

    protected $fillable = [
        'nombre',
        'nit',
        'direccion',
        'ciudad',
        'telefono',
        'email',
        'persona_contacto',
        'market_segment',  // Agregar este campo
        'servicio_producto',
        'proveedor_critico',
        'alto_riesgo',
        'camara_comercio',
        'rut',
        'cedula_representante',
        'certificacion_bancaria',
        'seguridad_social',
        'certificacion_alturas',
        'matriz_peligros',
        'matriz_epp',
        'estadisticas',
        'forma_pago',
        'descuento',
        'cobertura',
        'referencias_comerciales',
        'nivel_precios',
        'valores_agregados',
        'puntaje_forma_pago',
        'puntaje_referencias',
        'puntaje_descuento',
        'puntaje_cobertura',
        'puntaje_valores_agregados',
        'puntaje_precios',
        'puntaje_criterios_tecnicos',
        'puntaje_total',
        'estado',
        'camara_comercio_path',
        'rut_path',
        'cedula_representante_path',
        'certificacion_bancaria_path',
        'seguridad_social_path',
        'certificacion_alturas_path',
        'matriz_peligros_path',
        'matriz_epp_path',
        'estadisticas_path'
    ];

    public function calcularPuntajeTotal()
    {
        $puntajes = [
            $this->puntaje_forma_pago * 0.20,        // 20%
            $this->puntaje_referencias * 0.20,        // 20%
            $this->puntaje_descuento * 0.10,         // 10%
            $this->puntaje_cobertura * 0.10,         // 10%
            $this->puntaje_valores_agregados * 0.10,  // 10%
            $this->puntaje_precios * 0.10,           // 10%
            $this->puntaje_criterios_tecnicos * 0.20  // 20%
        ];

        $this->puntaje_total = array_sum($puntajes);
        $this->estado = $this->puntaje_total >= 60 ? 'Seleccionado' : 'No Seleccionado';
        $this->save();

        return $this->puntaje_total;
    }
}
