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
        'active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'active' => 'boolean',
    ];

    /**
     * Obtiene los días del ciclo escolar
     */
    public function cycleDays(): HasMany
    {
        return $this->hasMany(CycleDay::class);
    }

    /**
     * Obtiene los bloqueos de espacios para este ciclo escolar
     */
    public function spaceBlocks(): HasMany
    {
        return $this->hasMany(SpaceBlock::class);
    }

    /**
     * Genera los días del ciclo escolar
     * 
     * @param Carbon $endDate Fecha de fin opcional para la generación
     * @return array
     */
    public function generateCycleDays(?Carbon $endDate = null): array
    {
        // Si no se especifica fecha de fin, generamos para 1 año
        if (!$endDate) {
            $endDate = Carbon::parse($this->start_date)->addYear();
        }

        $startDate = Carbon::parse($this->start_date);
        $currentDate = $startDate->copy();
        $cycleDaysCreated = [];
        $dayCounter = 1;

        while ($currentDate->lte($endDate)) {
            // Saltar fines de semana (sábado y domingo)
            if ($currentDate->isWeekend()) {
                $currentDate->addDay();
                continue;
            }

            // Verificar si es un día festivo
            $isHoliday = Holiday::where('date', $currentDate->format('Y-m-d'))->exists();
            if ($isHoliday) {
                $currentDate->addDay();
                continue;
            }

            // Crear el día de ciclo
            $cycleDay = $this->cycleDays()->create([
                'date' => $currentDate->format('Y-m-d'),
                'cycle_day' => $dayCounter,
            ]);

            $cycleDaysCreated[] = $cycleDay;

            // Incrementamos el contador del día de ciclo y lo reiniciamos si alcanza el límite
            $dayCounter++;
            if ($dayCounter > $this->cycle_length) {
                $dayCounter = 1;
            }

            $currentDate->addDay();
        }

        return $cycleDaysCreated;
    }
}
