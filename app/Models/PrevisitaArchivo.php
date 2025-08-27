<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PrevisitaArchivo extends Model
{
    use HasFactory;

    protected $table = 'previsita_archivos';

    protected $fillable = [
        'previsita_consolidado_id',
        'nombre_original',
        'nombre_archivo',
        'ruta_archivo',
        'tipo_archivo',
        'mime_type',
        'tamaño_archivo'
    ];

    /**
     * Relación con PrevisitaConsolidado
     */
    public function previsitaConsolidado()
    {
        return $this->belongsTo(PrevisitaConsolidado::class);
    }

    /**
     * Verificar si el archivo es una imagen
     */
    public function esImagen()
    {
        return $this->tipo_archivo === 'image';
    }

    /**
     * Verificar si el archivo es un PDF
     */
    public function esPdf()
    {
        return $this->tipo_archivo === 'pdf';
    }

    /**
     * Obtener el tamaño formateado
     */
    public function getTamañoFormateadoAttribute()
    {
        $bytes = $this->tamaño_archivo;
        
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return round($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' B';
        }
    }
}
