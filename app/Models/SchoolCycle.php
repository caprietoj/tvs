<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolCycle extends Model
{
    protected $fillable = [
        'name',
        'description',
        'start_date',
        'cycle_length',
        'start_cycle_day',
        'active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'active' => 'boolean',
        'start_cycle_day' => 'integer',
    ];

    public function cycleDays(): HasMany
    {
        return $this->hasMany(CycleDay::class);
    }

    public function spaceBlocks(): HasMany
    {
        return $this->hasMany(SpaceBlock::class);
    }

    public function calculateCycleDayForDate(string|Carbon $targetDate): ?int
    {
        if ($targetDate instanceof Carbon) {
            $targetDate = $targetDate->format('Y-m-d');
        }

        $target = Carbon::parse($targetDate);

        if ($target->isWeekend()) {
            return null;
        }

        // Pre-cargar festivos una sola vez para evitar una consulta por día
        $holidayDates = Holiday::pluck('date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->flip();

        if (isset($holidayDates[$targetDate])) {
            return null;
        }

        $start = Carbon::parse($this->start_date);
        if ($target->lt($start)) {
            return null;
        }

        // start_cycle_day indica en qué número del ciclo (1–N) comienza el primer
        // día lectivo. Por defecto es 1. El offset traslada la secuencia para que
        // el ciclo continúe correctamente desde el ciclo escolar anterior.
        $startOffset = max(1, (int) ($this->start_cycle_day ?? 1)) - 1;

        $schoolDaysCount = 0;
        $current = $start->copy();

        while ($current->lte($target)) {
            if (!$current->isWeekend() && !isset($holidayDates[$current->format('Y-m-d')])) {
                $schoolDaysCount++;
            }
            $current->addDay();
        }

        return (($schoolDaysCount - 1 + $startOffset) % $this->cycle_length) + 1;
    }

    public function generateCycleDays(?Carbon $endDate = null): array
    {
        if (!$endDate) {
            $endDate = Carbon::parse($this->start_date)->addYear();
        }

        $startDate = Carbon::parse($this->start_date);
        $currentDate = $startDate->copy();
        $cycleDaysCreated = [];

        // start_cycle_day define en qué número del ciclo empieza el primer día lectivo.
        // Por defecto es 1. Usar un valor > 1 permite continuar la secuencia del ciclo anterior.
        $dayCounter = max(1, (int) ($this->start_cycle_day ?? 1));

        while ($currentDate->lte($endDate)) {
            if ($currentDate->isWeekend()) {
                $currentDate->addDay();
                continue;
            }

            $isHoliday = Holiday::where('date', $currentDate->format('Y-m-d'))->exists();
            if ($isHoliday) {
                $currentDate->addDay();
                continue;
            }

            $cycleDay = $this->cycleDays()->updateOrCreate(
                ['date' => $currentDate->format('Y-m-d')],
                ['cycle_day' => $dayCounter],
            );

            $cycleDaysCreated[] = $cycleDay;

            $dayCounter++;
            if ($dayCounter > $this->cycle_length) {
                $dayCounter = 1;
            }

            $currentDate->addDay();
        }

        return $cycleDaysCreated;
    }

    public function migrateSpaceBlocksFrom(?SchoolCycle $fromCycle = null): int
    {
        if (!$fromCycle) {
            $fromCycle = SchoolCycle::where('active', true)
                ->where('id', '!=', $this->id)
                ->orderBy('created_at', 'desc')
                ->first();
        }

        if (!$fromCycle) {
            return 0;
        }

        $migratedCount = SpaceBlock::where('school_cycle_id', $fromCycle->id)
            ->update(['school_cycle_id' => $this->id]);

        return $migratedCount;
    }
}
