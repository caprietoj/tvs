<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_id',
        'nombre_apellidos',
        'fecha',
        'entrada',
        'salida',
        'departamento',
        'mes'
    ];

    protected $dates = ['fecha'];

    // Mutadores para asegurar formato correcto
    public function setFechaAttribute($value)
    {
        $this->attributes['fecha'] = $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    public function getFechaAttribute($value)
    {
        return $value ? Carbon::parse($value)->format('d-m-Y') : null;
    }

    public function setEntradaAttribute($value)
    {
        $this->attributes['entrada'] = !empty($value) ? trim($value) : null;
    }

    public function setSalidaAttribute($value)
    {
        $this->attributes['salida'] = !empty($value) ? trim($value) : null;
    }
}