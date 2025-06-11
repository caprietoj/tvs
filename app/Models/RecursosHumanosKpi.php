<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecursosHumanosKpi extends Model
{
    use HasFactory;

    protected $table = 'recursos_humanos_kpis';

    protected $fillable = [
        'threshold_id',
        'name',
        'type',
        'methodology',
        'frequency',
        'measurement_date',
        'percentage',
        'area',
        'url'
    ];

    public function threshold()
    {
        return $this->belongsTo(RecursosHumanosThreshold::class, 'threshold_id');
    }

    public function getStatusAttribute()
    {
        $thresholdValue = $this->threshold ? $this->threshold->value : 80;
        return ($this->percentage >= $thresholdValue) ? 'Alcanzado' : 'No Alcanzado';
    }
}