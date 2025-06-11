<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecursosHumanosThreshold extends Model
{
    use HasFactory;

    protected $table = 'recursos_humanos_thresholds';

    protected $fillable = [
        'kpi_name',
        'value',
        'area'
    ];
}