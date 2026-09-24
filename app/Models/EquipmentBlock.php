<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class EquipmentBlock extends Model
{
    protected $fillable = [
        'equipment_id',
        'school_cycle_id',
        'cycle_day',
        'start_time',
        'end_time',
        'blocked_units',
        'reason',
        'is_weekday_block',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday'
    ];

    protected $casts = [
        'is_weekday_block' => 'boolean',
        'monday' => 'boolean',
        'tuesday' => 'boolean',
        'wednesday' => 'boolean',
        'thursday' => 'boolean',
        'friday' => 'boolean',
        'saturday' => 'boolean',
        'sunday' => 'boolean',
    ];

    /**
     * Obtiene el equipo asociado a este bloqueo
     */
    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    /**
     * Obtiene el ciclo escolar asociado a este bloqueo
     */
    public function schoolCycle(): BelongsTo
    {
        return $this->belongsTo(SchoolCycle::class);
    }

    /**
     * Obtiene las excepciones (cesiones) por fecha asociadas a este bloqueo
     */
    public function overrides(): HasMany
    {
        return $this->hasMany(EquipmentBlockOverride::class, 'equipment_block_id');
    }

    /**
     * IDs de los bloqueos que fueron cedidos/liberados para una fecha específica.
     * Estos bloqueos NO deben contar como ocupados en esa fecha, ya que la
     * asignación se representa con el préstamo del nuevo docente.
     *
     * @param string $date Fecha (Y-m-d)
     * @return array<int>
     */
    public static function overriddenBlockIdsForDate(string $date): array
    {
        return EquipmentBlockOverride::where('override_date', $date)
            ->pluck('equipment_block_id')
            ->all();
    }

    /**
     * Verifica si un equipo está bloqueado para un día específico de ciclo
     * 
     * @param int $equipmentId ID del equipo
     * @param int $schoolCycleId ID del ciclo escolar
     * @param int $cycleDay Día del ciclo
     * @return int Cantidad de unidades bloqueadas
     */
    public static function getBlockedUnitsForCycleDay(int $equipmentId, int $schoolCycleId, int $cycleDay): int
    {
        return self::where('equipment_id', $equipmentId)
            ->where('school_cycle_id', $schoolCycleId)
            ->where('cycle_day', $cycleDay)
            ->where('is_weekday_block', false)
            ->sum('blocked_units');
    }

    /**
     * Verifica si un equipo está bloqueado para un día específico de ciclo en un horario específico
     * 
     * @param int $equipmentId ID del equipo
     * @param int $schoolCycleId ID del ciclo escolar
     * @param int $cycleDay Día del ciclo
     * @param string $startTime Hora de inicio (HH:MM)
     * @param string $endTime Hora de fin (HH:MM)
     * @return int Cantidad de unidades bloqueadas para ese horario
     */
    public static function getBlockedUnitsForCycleDayTime(int $equipmentId, int $schoolCycleId, int $cycleDay, string $startTime, string $endTime): int
    {
        return self::where('equipment_id', $equipmentId)
            ->where('school_cycle_id', $schoolCycleId)
            ->where('cycle_day', $cycleDay)
            ->where('is_weekday_block', false)
            ->where(function($query) use ($startTime, $endTime) {
                // Verificar si hay superposición de horarios
                $query->where(function($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
                });
            })
            ->sum('blocked_units');
    }

    /**
     * Verifica si un equipo está bloqueado para un día de la semana específico
     * 
     * @param int $equipmentId ID del equipo
     * @param string $dayOfWeek Día de la semana (monday, tuesday, etc.)
     * @param string $startTime Hora de inicio (HH:MM)
     * @param string $endTime Hora de fin (HH:MM)
     * @param string|null $date Fecha específica para verificar excepciones
     * @return int Cantidad de unidades bloqueadas
     */
    public static function getBlockedUnitsForWeekday(int $equipmentId, string $dayOfWeek, string $startTime, string $endTime, ?string $date = null): int
    {
        // Obtener el ciclo escolar activo
        $activeCycle = SchoolCycle::where('active', true)->first();
        if (!$activeCycle) {
            return 0;
        }

        return self::where('equipment_id', $equipmentId)
            ->where('school_cycle_id', $activeCycle->id)
            ->where('is_weekday_block', true)
            ->where($dayOfWeek, true)
            ->where(function($query) use ($startTime, $endTime) {
                $query->where(function($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
                });
            })
            ->sum('blocked_units');
    }

    /**
     * Obtiene todas las unidades bloqueadas para un equipo en una fecha y horario específicos
     * 
     * @param int $equipmentId ID del equipo
     * @param string $date Fecha (Y-m-d)
     * @param string $startTime Hora de inicio (HH:MM)
     * @param string $endTime Hora de fin (HH:MM)
     * @return int Total de unidades bloqueadas
     */
    public static function getTotalBlockedUnits(int $equipmentId, string $date, string $startTime, string $endTime): int
    {
        $dateObj = Carbon::parse($date);
        $dayOfWeek = strtolower($dateObj->format('l'));
        
        // Obtener el ciclo escolar activo
        $activeCycle = SchoolCycle::where('active', true)->first();
        if (!$activeCycle) {
            return 0;
        }

        // Bloqueos cedidos/liberados para esta fecha: no cuentan como ocupados
        $overriddenIds = self::overriddenBlockIdsForDate($date);

        $totalBlocked = 0;

        // 1. Verificar bloqueos semanales
        $weeklyQuery = self::where('equipment_id', $equipmentId)
            ->where('school_cycle_id', $activeCycle->id)
            ->where('is_weekday_block', true)
            ->where($dayOfWeek, true)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime);

        if (!empty($overriddenIds)) {
            $weeklyQuery->whereNotIn('id', $overriddenIds);
        }

        $totalBlocked += (int) $weeklyQuery->sum('blocked_units');

        // 2. Verificar bloqueos por día de ciclo
        $cycleDay = \App\Models\CycleDay::getCycleDayForDate($date, $activeCycle->id);
        if ($cycleDay) {
            $cycleQuery = self::where('equipment_id', $equipmentId)
                ->where('school_cycle_id', $activeCycle->id)
                ->where('cycle_day', $cycleDay->cycle_day)
                ->where('is_weekday_block', false)
                ->where('start_time', '<', $endTime)
                ->where('end_time', '>', $startTime);

            if (!empty($overriddenIds)) {
                $cycleQuery->whereNotIn('id', $overriddenIds);
            }

            $totalBlocked += (int) $cycleQuery->sum('blocked_units');
        }

        return $totalBlocked;
    }

    /**
     * Calcula las unidades disponibles para un equipo en una fecha y horario específicos
     * 
     * @param Equipment $equipment Modelo del equipo
     * @param string $date Fecha (Y-m-d)
     * @param string $startTime Hora de inicio (HH:MM)
     * @param string $endTime Hora de fin (HH:MM)
     * @return int Unidades disponibles después de restar bloqueos y préstamos activos
     */
    public static function getAvailableUnits(Equipment $equipment, string $date, string $startTime, string $endTime): int
    {
        // Obtener unidades totales del equipo
        $totalUnits = $equipment->total_units;

        // Obtener unidades bloqueadas
        $blockedUnits = self::getTotalBlockedUnits($equipment->id, $date, $startTime, $endTime);

        // Obtener préstamos activos para el mismo horario
        $activeLoans = EquipmentLoan::where('equipment_id', $equipment->id)
            ->where('loan_date', $date)
            ->where('status', '!=', 'returned')
            ->where('status', '!=', 'cancelled')
            ->where(function($query) use ($startTime, $endTime) {
                $query->where(function($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
                });
            })
            ->sum('units_requested');

        return max(0, $totalUnits - $blockedUnits - $activeLoans);
    }

    /**
     * Crea un bloqueo semanal para un equipo
     * 
     * @param int $equipmentId ID del equipo
     * @param array $weekdays Días de la semana ['monday', 'tuesday', ...]
     * @param string $startTime Hora de inicio
     * @param string $endTime Hora de fin
     * @param int $blockedUnits Cantidad de unidades a bloquear
     * @param string|null $reason Razón del bloqueo
     * @return EquipmentBlock
     */
    public static function createWeeklyBlock(int $equipmentId, array $weekdays, string $startTime, string $endTime, int $blockedUnits, ?string $reason = null): EquipmentBlock
    {
        $activeCycle = SchoolCycle::where('active', true)->firstOrFail();

        $blockData = [
            'equipment_id' => $equipmentId,
            'school_cycle_id' => $activeCycle->id,
            'cycle_day' => 0, // 0 para bloqueos semanales
            'start_time' => $startTime,
            'end_time' => $endTime,
            'blocked_units' => $blockedUnits,
            'reason' => $reason,
            'is_weekday_block' => true,
        ];

        // Establecer los días de la semana
        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            $blockData[$day] = in_array($day, $weekdays);
        }

        return self::create($blockData);
    }

    /**
     * Crea un bloqueo para días específicos del ciclo escolar
     * 
     * @param int $equipmentId ID del equipo
     * @param array $cycleDays Días del ciclo escolar [1, 2, 3, ...]
     * @param string $startTime Hora de inicio
     * @param string $endTime Hora de fin
     * @param int $blockedUnits Cantidad de unidades a bloquear
     * @param string|null $reason Razón del bloqueo
     * @return array Array de EquipmentBlock creados
     */
    public static function createCycleDayBlocks(int $equipmentId, array $cycleDays, string $startTime, string $endTime, int $blockedUnits, ?string $reason = null): array
    {
        $activeCycle = SchoolCycle::where('active', true)->firstOrFail();
        $createdBlocks = [];

        foreach ($cycleDays as $cycleDay) {
            $block = self::create([
                'equipment_id' => $equipmentId,
                'school_cycle_id' => $activeCycle->id,
                'cycle_day' => $cycleDay,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'blocked_units' => $blockedUnits,
                'reason' => $reason,
                'is_weekday_block' => false,
            ]);

            $createdBlocks[] = $block;
        }

        return $createdBlocks;
    }
}
