<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class RegistroPorteria extends Model
{
    /**
     * Hora a partir de la cual, si un empleado no tiene registros previos
     * en el día, se asume que está marcando la salida y no la entrada.
     */
    public const HORA_CORTE_SALIDA = 12;

    protected $table = 'registro_porteria';

    protected $fillable = [
        'documento',
        'nombre',
        'tipo_persona',
        'fecha',
        'hora_entrada',
        'hora_salida',
        'observaciones',
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
        // El nombre ya contiene el nombre completo
        return $this->nombre;
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

    /**
     * Registro abierto del día (tiene entrada pero aún no tiene salida).
     */
    public static function registroAbiertoHoy(string $documento, ?Carbon $momento = null)
    {
        $momento = $momento ?: Carbon::now();

        return self::where('documento', $documento)
            ->whereDate('fecha', $momento->toDateString())
            ->whereNotNull('hora_entrada')
            ->whereNull('hora_salida')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    /**
     * Determinar si el escaneo corresponde a una ENTRADA o a una SALIDA.
     *
     * Reglas:
     *  1. Si existe un registro abierto (entrada sin salida) => SALIDA.
     *  2. Si ya hubo movimientos hoy y el último ya está cerrado => ENTRADA (reingreso).
     *  3. Si es un empleado y no tiene ningún registro previo hoy y la hora actual
     *     es igual o posterior a la hora de corte => SALIDA (marca salida sin entrada).
     *  4. En cualquier otro caso => ENTRADA.
     */
    public static function determinarAccion(
        string $documento,
        ?Carbon $momento = null,
        ?string $tipoPersona = null
    ): string {
        $momento = $momento ?: Carbon::now();

        if (self::registroAbiertoHoy($documento, $momento)) {
            return 'salida';
        }

        $tieneRegistrosHoy = self::where('documento', $documento)
            ->whereDate('fecha', $momento->toDateString())
            ->exists();

        if (!$tieneRegistrosHoy
            && $tipoPersona === 'empleado'
            && $momento->hour >= self::HORA_CORTE_SALIDA) {
            return 'salida';
        }

        return 'entrada';
    }

    /**
     * Tipo de registro inicial: 'entrada' o 'salida'.
     * Un registro es de salida cuando no tiene hora de entrada pero sí de salida.
     */
    public function getTipoRegistroAttribute(): string
    {
        if (is_null($this->hora_entrada) && !is_null($this->hora_salida)) {
            return 'salida';
        }

        return 'entrada';
    }
}
