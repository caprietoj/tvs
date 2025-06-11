<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SistemasKpi extends Model
{
    use HasFactory;

    protected $table = 'sistemas_kpis';

    protected $fillable = [
        'threshold_id',
        'name',
        'type',
        'methodology',
        'frequency',
        'measurement_date',
        'percentage',
        'area',
        'url',
    ];

    public function threshold()
    {
        return $this->belongsTo(SistemasThreshold::class, 'threshold_id');
    }

    public function getStatusAttribute()
    {
        if ($this->type === 'informative') {
            return $this->percentage >= 50 ? 'Favorable' : 'Desfavorable';
        }
        $thresholdValue = $this->threshold ? $this->threshold->value : 80;
        return ($this->percentage >= $thresholdValue) ? 'Alcanzado' : 'No Alcanzado';
    }
}