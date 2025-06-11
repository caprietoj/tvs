<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kpi extends Model
{
    use HasFactory;
    
    protected $table = 'kpis';

    protected $fillable = [
        'threshold_id',
        'area',
        'name',
        'methodology',
        'frequency',
        'measurement_date',
        'percentage',
        'analysis', // Add this line
        'type',
        'url',
    ];

    // Constantes para los tipos de KPI
    const TYPE_MEASUREMENT = 'measurement';
    const TYPE_INFORMATIVE = 'informative';

    // Relación con el threshold
    public function threshold()
    {
        return $this->belongsTo(Threshold::class);
    }

    // Accesor para determinar el estado del KPI en función del umbral configurado
    public function getStatusAttribute()
    {
        // Asegurarse de que threshold está cargado
    if (!$this->relationLoaded('threshold')) {
        $this->load('threshold');
    }
    
    // Obtener el valor del umbral, por defecto 80 si no existe
    $threshold_value = $this->threshold ? $this->threshold->value : 80;
    
    // Comparar el porcentaje con el umbral y retornar el estado
    return ($this->percentage >= $threshold_value) ? 'Alcanzado' : 'No Alcanzado';
    }

    // Método para obtener los tipos de KPI disponibles
    public static function getTypes()
    {
        return [
            self::TYPE_MEASUREMENT => 'Medición',
            self::TYPE_INFORMATIVE => 'Informativo'
        ];
    }

    // Helpers para verificar el tipo
    public function isMeasurement()
    {
        return $this->type === self::TYPE_MEASUREMENT;
    }

    public function isInformative()
    {
        return $this->type === self::TYPE_INFORMATIVE;
    }

}