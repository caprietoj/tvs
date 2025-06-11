<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComprasKpi extends Model
{
    use HasFactory;

    protected $table = 'compras_kpis';

    protected $fillable = [
        'threshold_id',
        'name',
        'type',
        'methodology',
        'frequency',
        'measurement_date',
        'percentage',
        'analysis', // Make sure this field is included
        'status',
        'url',
    ];

    public function threshold()
    {
        return $this->belongsTo(ComprasThreshold::class, 'threshold_id');
    }

    public function getStatusAttribute()
    {
        if (!$this->relationLoaded('threshold')) {
            $this->load('threshold');
        }
        $thresholdValue = $this->threshold ? $this->threshold->value : 80;
        return ($this->percentage >= $thresholdValue) ? 'Alcanzado' : 'No Alcanzado';
    }
}