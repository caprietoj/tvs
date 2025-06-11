<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SistemasThreshold extends Model
{
    use HasFactory;

    protected $table = 'sistemas_thresholds';

    protected $fillable = [
        'kpi_name',
        'value',
    ];
}
