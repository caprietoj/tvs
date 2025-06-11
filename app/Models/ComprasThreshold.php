<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComprasThreshold extends Model
{
    use HasFactory;

    protected $table = 'compras_thresholds';

    protected $fillable = [
        'area',
        'kpi_name',
        'value',
        'description'
    ];

    protected $casts = [
        'value' => 'decimal:2'
    ];

    public function kpis()
    {
        return $this->hasMany(ComprasKpi::class, 'threshold_id');
    }
}
