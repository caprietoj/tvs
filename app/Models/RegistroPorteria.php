<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class RegistroPorteria extends Model
{
    protected $table = 'registro_porteria';

    protected $fillable = [
        'documento',
        'nombre',
        'apellido',
        'tipo_persona',
        'fecha',
        'hora_entrada',
        'hora_salida',
        'user_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora_entrada' => 'datetime:H:i:s',
        'hora_salida' => 'datetime:H:i:s',
    ];

    /**
     * Relación con el usuario que registró
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Obtener el nombre completo
     */
    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombre} {$this->apellido}";
    }

    /**
     * Verificar si ya tiene hora de salida
     */
    public function tieneSalida(): bool
    {
        return !is_null($this->hora_salida);
    }

    /**
     * Buscar el ÚLTIMO registro de hoy por documento
     * Esto permite múltiples entradas/salidas en el mismo día
     */
    public static function registroHoy(string $documento)
    {
        return self::where('documento', $documento)
            ->where('fecha', Carbon::today())
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
