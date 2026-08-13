<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Holiday;

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
    public static function getCycleDayForDate(string $date, ?int $schoolCycleId = null)
    {
        $target = Carbon::parse($date);

        // Los fines de semana y festivos no son días lectivos
        if ($target->isWeekend()) {
            return null;
        }

        if (Holiday::where('date', $target->format('Y-m-d'))->exists()) {
            return null;
        }

        // Si no se indica el ciclo, usar el ciclo activo
        if (!$schoolCycleId) {
            $activeCycle = SchoolCycle::where('active', true)->first();
            $schoolCycleId = $activeCycle?->id;
        }

        $query = self::where('date', $date);

        if ($schoolCycleId) {
            $query->where('school_cycle_id', $schoolCycleId);
        }

        $record = $query->first();

        // Si la tabla de días generados no tiene la fecha (desactualizada),
        // calcular dinámicamente el día del ciclo.
        if (!$record) {
            $cycleDay = self::calculateCycleDay($date, $schoolCycleId);
            if ($cycleDay === null) {
                return null;
            }

            $record = new self();
            $record->date = $target;
            $record->cycle_day = $cycleDay;
            if ($schoolCycleId) {
                $record->school_cycle_id = $schoolCycleId;
            }
        }

        return $record;
    }

    /**
     * Calcula dinámicamente el día del ciclo para una fecha específica
     * usando el ciclo escolar activo. No depende de datos pre-generados.
     * 
     * @param string|Carbon $targetDate Fecha para la cual calcular
     * @param int|null $schoolCycleId ID del ciclo escolar (usa el activo si es null)
     * @return int|null Día del ciclo (1-6) o null si no es día lectivo
     */
    public static function calculateCycleDay(string|Carbon $targetDate, ?int $schoolCycleId = null): ?int
    {
        if ($targetDate instanceof Carbon) {
            $targetDate = $targetDate->format('Y-m-d');
        }

        $target = Carbon::parse($targetDate);

        if ($target->isWeekend()) {
            return null;
        }

        if (Holiday::where('date', $target->format('Y-m-d'))->exists()) {
            return null;
        }

        $cycle = $schoolCycleId
            ? SchoolCycle::find($schoolCycleId)
            : SchoolCycle::where('active', true)->first();

        if (!$cycle) {
            return null;
        }

        return $cycle->calculateCycleDayForDate($targetDate);
    }
}