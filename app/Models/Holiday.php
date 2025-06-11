<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = [
        'date',
        'name',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Verifica si una fecha es un día festivo
     */
    public static function isHoliday(string|Carbon $date): bool
    {
        if ($date instanceof Carbon) {
            $date = $date->format('Y-m-d');
        }

        return self::where('date', $date)->exists();
    }

    /**
     * Importa días festivos desde un archivo Excel
     */
    public static function importFromExcel($file): array
    {
        // Esta función se implementaría utilizando una librería como maatwebsite/excel
        // para procesar el archivo Excel e importar los días festivos
        // Por ahora solo retornamos un array vacío
        // Se implementará completamente en una funcionalidad posterior
        return [];
    }
}
