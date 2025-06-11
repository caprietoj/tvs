<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SpaceBlock extends Model
{
    protected $fillable = [
        'space_id',
        'school_cycle_id',
        'cycle_day',
        'reason',
        'is_weekday_block',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'sunday',
        'start_time',
        'end_time',
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
     * Obtiene el espacio asociado a este bloqueo
     */
    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    /**
     * Obtiene el ciclo escolar asociado a este bloqueo
     */
    public function schoolCycle(): BelongsTo
    {
        return $this->belongsTo(SchoolCycle::class);
    }
    
    /**
     * Obtiene las excepciones asociadas a este bloqueo
     */
    public function exceptions(): HasMany
    {
        return $this->hasMany(SpaceBlockException::class);
    }

    /**
     * Verifica si un espacio está bloqueado para un día específico de ciclo
     */
    public static function isBlocked(int $spaceId, int $schoolCycleId, int $cycleDay): bool
    {
        return self::where('space_id', $spaceId)
            ->where('school_cycle_id', $schoolCycleId)
            ->where('cycle_day', $cycleDay)
            ->exists();
    }
    
    /**
     * Verifica si un espacio está bloqueado para un día de la semana en un horario específico
     * teniendo en cuenta las posibles excepciones
     * 
     * @param int $spaceId ID del espacio
     * @param string $weekday Día de la semana (monday, tuesday, etc.)
     * @param string $startTime Hora de inicio (HH:MM)
     * @param string $endTime Hora de fin (HH:MM)
     * @param string|null $specificDate Fecha específica en formato Y-m-d
     * @return bool
     */
    public static function isBlockedForWeekday(int $spaceId, string $weekday, string $startTime, string $endTime, ?string $specificDate = null): bool
    {
        // Obtener los bloqueos aplicables
        $blocksQuery = self::where('space_id', $spaceId)
            ->where('is_weekday_block', true)
            ->where($weekday, true)
            ->where(function($query) use ($startTime, $endTime) {
                // Bloqueo que se superpone al horario solicitado
                $query->where(function($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
                });
            });
            
        // Si tenemos una fecha específica y existe una excepción, no está bloqueado
        if ($specificDate) {
            $blocks = $blocksQuery->get();
            
            foreach ($blocks as $block) {
                // Verificar si hay una excepción para este bloqueo en esta fecha
                $hasException = SpaceBlockException::where('space_block_id', $block->id)
                    ->where('exception_date', $specificDate)
                    ->exists();
                
                // Si no hay excepción para al menos uno de los bloqueos, está bloqueado
                if (!$hasException) {
                    return true;
                }
            }
            
            // Todos los bloqueos tienen excepciones para esta fecha
            return false;
        }
        
        // Sin fecha específica, simplemente verificamos si hay bloqueos
        return $blocksQuery->exists();
    }
    
    /**
     * Verifica si un espacio está bloqueado para un día de la semana específico
     * 
     * @param int $spaceId ID del espacio
     * @param int $schoolCycleId ID del ciclo escolar
     * @param string $dayOfWeek Día de la semana en inglés (Monday, Tuesday, etc.)
     * @return bool
     */
    public static function isBlockedByDay(int $spaceId, int $schoolCycleId, string $dayOfWeek): bool
    {
        $dayColumn = strtolower($dayOfWeek);
        
        return self::where('space_id', $spaceId)
            ->where('school_cycle_id', $schoolCycleId)
            ->where('is_weekday_block', true)
            ->where($dayColumn, true)
            ->exists();
    }
    
    /**
     * Obtiene los bloqueos semanales para un espacio y día de la semana específicos
     * 
     * @param int $spaceId ID del espacio
     * @param string $weekday Día de la semana (monday, tuesday, etc.)
     * @param string|null $specificDate Fecha específica en formato Y-m-d
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getWeekdayBlocksForSpace(int $spaceId, string $weekday, ?string $specificDate = null)
    {
        $blocksQuery = self::where('space_id', $spaceId)
            ->where('is_weekday_block', true)
            ->where($weekday, true)
            ->orderBy('start_time');
        
        $blocks = $blocksQuery->get();
        
        // Si tenemos una fecha específica, filtrar por excepciones
        if ($specificDate && $blocks->isNotEmpty()) {
            foreach ($blocks as $key => $block) {
                $hasException = SpaceBlockException::where('space_block_id', $block->id)
                    ->where('exception_date', $specificDate)
                    ->exists();
                
                if ($hasException) {
                    // Si hay una excepción, marcar el bloqueo como excepción
                    $block->has_exception = true;
                    $blocks[$key] = $block;
                }
            }
        }
        
        return $blocks;
    }
}
