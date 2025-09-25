<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PresupuestoItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'fuente', 'documento', 'fecha', 'cuenta', 'seccion', 'rubro',
        'descripcion', 'valor', 'valor_moneda', 'cliente_proveedor',
        'nombre_cliente_proveedor', 'tercero', 'nombre_tercero',
        'auxiliar', 'centro_costo', 'is_valid_center', 'es_total'
    ];

    protected $casts = [
        'fecha' => 'date',
        'valor' => 'decimal:2',
        'valor_moneda' => 'decimal:2',
        'es_total' => 'boolean',
        'is_valid_center' => 'boolean',
    ];

    // Scopes para filtrar datos
    public function scopePorSeccion($query, $seccion)
    {
        return $query->where('seccion', $seccion);
    }

    public function scopeSinTotales($query)
    {
        return $query->where('es_total', false);
    }

    public function scopeSoloTotales($query)
    {
        return $query->where('es_total', true);
    }

    public function scopePorRubro($query, $rubro)
    {
        return $query->where('rubro', $rubro);
    }

    public function scopePorFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha', [$fechaInicio, $fechaFin]);
    }

    // NUEVO: Scope para filtrar solo centros válidos (para Detallado Secciones 1)
    public function scopeSoloCentrosValidos($query)
    {
        return $query->where('is_valid_center', true);
    }

    // NUEVO: Scope para incluir todos los centros (para otras vistas)
    public function scopeTodosCentros($query)
    {
        return $query; // No aplica filtro
    }

    // Relaciones con las tablas de referencia
    public function centroCostoModel()
    {
        return $this->belongsTo(CentroCosto::class, 'centro_costo', 'codigo');
    }

    public function cuentaModel()
    {
        return $this->belongsTo(Cuenta::class, 'cuenta', 'codigo');
    }

    public function seccionModel()
    {
        return $this->belongsTo(Seccion::class, 'seccion', 'nombre');
    }

    public function rubroModel()
    {
        return $this->belongsTo(Rubro::class, 'rubro', 'nombre');
    }

    // Accessor para formatear el valor
    public function getValorFormateadoAttribute()
    {
        return '$' . number_format($this->valor, 0, ',', '.');
    }

    // Accessor para determinar el color de la sección
    public function getColorSeccionAttribute()
    {
        $colores = [
            'PREESCOLAR Y PRIMARIA' => 'primary',
            'ESCUELA MEDIA' => 'success',
            'ALTA' => 'warning',
            'ADMINISTRACION' => 'info',
            'DIRECCION GENERAL' => 'secondary',
            'BIBLIOTECA' => 'danger',
            'DEPORTES' => 'dark',
            'CAS' => 'primary',
            'PAI' => 'success',
            'PEP' => 'warning',
            'PSICOLOGIA INSTITUCIONAL' => 'info',
            'TECNOLOGIA INSTITUCIONAL' => 'secondary',
            'OTROS' => 'light',
            'SIN_ASIGNAR' => 'muted'
        ];

        return $colores[$this->seccion] ?? 'light';
    }

    /**
     * Get the formatted value attribute.
     *
     * @return string
     */
    public function getValorFormattedAttribute()
    {
        if (!$this->valor) {
            return '$ 0,00 ';
        }
        
        return '$ ' . number_format($this->valor, 2, ',', '.') . ' ';
    }
}
