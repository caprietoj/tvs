<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CycleDay extends Model
{
    protected $fillable = [
        'school_cycle_id',
        'date',
        'cycle_day',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Obtiene el ciclo escolar al que pertenece este día
     */
    public function schoolCycle(): BelongsTo
    {
        return $this->belongsTo(SchoolCycle::class);
    }

    /**
     * Obtener el día de ciclo para una fecha específica
     */
    public static function getCycleDayForDate(string $date, int $schoolCycleId = null)
    {
        $query = self::where('date', $date);
        
        if ($schoolCycleId) {
            $query->where('school_cycle_id', $schoolCycleId);
        }

        return $query->first();
    }
}
