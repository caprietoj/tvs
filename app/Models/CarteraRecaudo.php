<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarteraRecaudo extends Model
{
    protected $fillable = [
        'mes',
        'valor_recaudado',
        'valor_facturado',
    ];

    // Calculate percentage of collection
    public function getPorcentajeRecaudoAttribute()
    {
        if ($this->valor_facturado > 0) {
            return round(($this->valor_recaudado / $this->valor_facturado) * 100, 2);
        }
        return 0;
    }
}
