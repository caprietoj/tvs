<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpaceBlockException extends Model
{
    protected $fillable = [
        'space_block_id',
        'exception_date',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'exception_date' => 'date',
    ];

    /**
     * Obtiene el bloqueo semanal asociado a esta excepción
     */
    public function spaceBlock(): BelongsTo
    {
        return $this->belongsTo(SpaceBlock::class);
    }

    /**
     * Obtiene el usuario que creó esta excepción
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Verifica si existe una excepción para un bloqueo específico en una fecha dada
     *
     * @param int $spaceBlockId ID del bloqueo
     * @param string $date Fecha en formato Y-m-d
     * @return bool
     */
    public static function hasException(int $spaceBlockId, string $date): bool
    {
        return self::where('space_block_id', $spaceBlockId)
            ->where('exception_date', $date)
            ->exists();
    }
}
