<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SpaceReservation extends Model
{
    protected $fillable = [
        'space_id',
        'user_id',
        'date',
        'start_time',
        'end_time',
        'purpose',
        'status',
        'comments',
        'notes',
        'requires_librarian',
        'selected_electronic_resources',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
    ];

    protected $casts = [
        'date' => 'date',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    /**
     * Obtiene el espacio asociado a esta reserva
     */
    public function space(): BelongsTo
    {
        return $this->belongsTo(Space::class);
    }

    /**
     * Obtiene el usuario que realizó la reserva
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene el usuario que aprobó la reserva
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Obtiene el usuario que rechazó la reserva
     */
    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    /**
     * Obtiene los implementos solicitados en esta reserva
     */
    public function items()
    {
        return $this->hasMany(SpaceReservationItem::class);
    }

    /**
     * Obtiene la fecha formateada
     */
    public function getFormattedDateAttribute()
    {
        if ($this->date instanceof \Carbon\Carbon) {
            return $this->date->format('d/m/Y');
        }
        
        return is_string($this->date) ? date('d/m/Y', strtotime($this->date)) : '';
    }

    /**
     * Obtiene la hora de inicio formateada
     */
    public function getFormattedStartTimeAttribute()
    {
        if ($this->start_time instanceof \Carbon\Carbon) {
            return $this->start_time->format('H:i');
        }
        
        return is_string($this->start_time) ? substr($this->start_time, 0, 5) : '';
    }

    /**
     * Obtiene la hora de fin formateada
     */
    public function getFormattedEndTimeAttribute()
    {
        if ($this->end_time instanceof \Carbon\Carbon) {
            return $this->end_time->format('H:i');
        }
        
        return is_string($this->end_time) ? substr($this->end_time, 0, 5) : '';
    }

    /**
     * Obtiene la fecha de aprobación formateada
     */
    public function getFormattedApprovedAtAttribute()
    {
        if ($this->approved_at instanceof \Carbon\Carbon) {
            return $this->approved_at->format('d/m/Y H:i');
        }
        
        return is_string($this->approved_at) && !empty($this->approved_at) 
            ? date('d/m/Y H:i', strtotime($this->approved_at)) 
            : '';
    }

    /**
     * Obtiene la fecha de rechazo formateada
     */
    public function getFormattedRejectedAtAttribute()
    {
        if ($this->rejected_at instanceof \Carbon\Carbon) {
            return $this->rejected_at->format('d/m/Y H:i');
        }
        
        return is_string($this->rejected_at) && !empty($this->rejected_at) 
            ? date('d/m/Y H:i', strtotime($this->rejected_at)) 
            : '';
    }

    /**
     * Verifica si hay conflictos de horario para una reserva
     */
    public static function hasTimeConflict(int $spaceId, string $date, string $startTime, string $endTime, int $exceptId = null): bool
    {
        // Verificar conflictos con otras reservas
        $query = self::where('space_id', $spaceId)
            ->where('date', $date)
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'rejected')
            ->where(function ($q) use ($startTime, $endTime) {
                // Verifica si el nuevo horario interfiere con algún horario existente
                $q->where(function ($query) use ($startTime, $endTime) {
                    $query->where('start_time', '<', $endTime)
                          ->where('end_time', '>', $startTime);
                });
            });

        // Excluir la reserva actual en caso de edición
        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        // Si hay conflictos con otras reservas, retornar true
        if ($query->exists()) {
            return true;
        }

        // Verificar conflictos con bloqueos semanales y por ciclo
        $dateObj = Carbon::parse($date);
        $dayOfWeek = strtolower($dateObj->format('l')); // obtener el día de la semana en inglés y en minúsculas
        
        // Obtener el ciclo escolar activo
        $activeCycle = SchoolCycle::where('active', true)->first();
        
        if ($activeCycle) {
            // Verificar si hay bloqueos semanales para este día y horario
            // teniendo en cuenta posibles excepciones para la fecha específica
            $hasWeekdayBlock = SpaceBlock::isBlockedForWeekday(
                $spaceId, 
                $dayOfWeek, 
                $startTime, 
                $endTime,
                $date // Pasar la fecha específica para verificar excepciones
            );
            
            if ($hasWeekdayBlock) {
                return true;
            }
            
            // Verificar si hay bloqueos por día de ciclo para este horario específico
            $cycleDay = CycleDay::getCycleDayForDate($date, $activeCycle->id);
            if ($cycleDay) {
                $hasCycleDayBlock = SpaceBlock::isBlockedForCycleDayTime(
                    $spaceId,
                    $activeCycle->id,
                    $cycleDay->cycle_day,
                    $startTime,
                    $endTime
                );
                
                if ($hasCycleDayBlock) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Verifica si una reserva puede realizarse en un día específico
     * 
     * @param int $spaceId ID del espacio
     * @param string $date Fecha de la reserva
     * @return array [bool $canReserve, string $message]
     */
    public static function canReserveOnDate(int $spaceId, string $date): array
    {
        $dateObj = Carbon::parse($date);
        
        // Verificar si es fin de semana
        if ($dateObj->isWeekend()) {
            return [false, 'No se pueden realizar reservas en fines de semana.'];
        }
        
        // Verificar si es día festivo
        if (Holiday::isHoliday($date)) {
            return [false, 'No se pueden realizar reservas en días festivos.'];
        }
        
        // Verificar si existe un ciclo escolar activo
        $activeCycle = SchoolCycle::where('active', true)->first();
        if (!$activeCycle) {
            return [false, 'No hay ciclo escolar activo.'];
        }
        
        // Obtener el día de ciclo para la fecha
        $cycleDay = CycleDay::getCycleDayForDate($date, $activeCycle->id);
        if (!$cycleDay) {
            return [false, 'La fecha seleccionada no está dentro del ciclo escolar.'];
        }
        
        // Ya no verificamos bloqueos por días de ciclo aquí de forma general,
        // esto se hará en la función hasTimeConflict según el horario específico.
        // Esto permite que un espacio pueda ser reservado en horarios no bloqueados
        // del mismo día de ciclo.
        
        return [true, ''];
    }

    /**
     * Obtiene detalles específicos del conflicto para mostrar un mensaje más útil al usuario
     * 
     * @param int $spaceId ID del espacio
     * @param string $date Fecha de la reserva
     * @param string $startTime Hora de inicio
     * @param string $endTime Hora de fin
     * @return string Mensaje descriptivo del conflicto
     */
    public static function getConflictDetails(int $spaceId, string $date, string $startTime, string $endTime): string
    {
        // Verificar conflictos con otras reservas primero
        $conflictingReservations = self::where('space_id', $spaceId)
            ->where('date', $date)
            ->where('status', '!=', 'cancelled')
            ->where('status', '!=', 'rejected')
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($query) use ($startTime, $endTime) {
                    $query->where('start_time', '<', $endTime)
                          ->where('end_time', '>', $startTime);
                });
            })->get();

        if ($conflictingReservations->count() > 0) {
            $reservation = $conflictingReservations->first();
            return "Ya existe una reserva para este espacio el {$reservation->formatted_date} de {$reservation->formatted_start_time} a {$reservation->formatted_end_time}.";
        }

        // Si no hay reservas conflictivas, verificar bloqueos
        $dateObj = Carbon::parse($date);
        $dayOfWeek = strtolower($dateObj->format('l'));
        
        $activeCycle = SchoolCycle::where('active', true)->first();
        if (!$activeCycle) {
            return 'No hay un ciclo escolar activo.';
        }

        // Verificar bloqueos semanales
        $weekdayBlocks = SpaceBlock::where('space_id', $spaceId)
            ->where('is_weekday_block', true)
            ->where($dayOfWeek, true)
            ->where(function($query) use ($startTime, $endTime) {
                $query->where(function($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>', $startTime);
                });
            })->get();

        foreach ($weekdayBlocks as $block) {
            // Verificar si hay excepción para esta fecha
            $hasException = SpaceBlockException::where('space_block_id', $block->id)
                ->where('exception_date', $date)
                ->exists();
            
            if (!$hasException) {
                return "El espacio está bloqueado los " . ucfirst($dayOfWeek) . "s de {$block->start_time} a {$block->end_time} para: {$block->reason}. Tu horario solicitado ({$startTime} - {$endTime}) se solapa con este bloqueo.";
            }
        }

        // Verificar bloqueos de día de ciclo
        $cycleDay = CycleDay::getCycleDayForDate($date, $activeCycle->id);
        if ($cycleDay) {
            $cycleDayBlocks = SpaceBlock::where('space_id', $spaceId)
                ->where('school_cycle_id', $activeCycle->id)
                ->where('cycle_day', $cycleDay->cycle_day)
                ->where(function($query) use ($startTime, $endTime) {
                    $query->where(function($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<', $endTime)
                          ->where('end_time', '>', $startTime);
                    });
                })->get();

            foreach ($cycleDayBlocks as $block) {
                // Generar sugerencias de horarios alternativos
                $suggestions = self::getSuggestedTimes($spaceId, $date, $startTime, $endTime);
                $suggestionText = '';
                if (!empty($suggestions)) {
                    $suggestionText = " Horarios disponibles: " . implode(', ', $suggestions);
                }
                
                return "El espacio está bloqueado en el día de ciclo {$cycleDay->cycle_day} de {$block->start_time} a {$block->end_time} para: {$block->reason}. Tu horario solicitado ({$startTime} - {$endTime}) se solapa con este bloqueo.{$suggestionText}";
            }
        }

        return 'Ya existe una reserva para este espacio en el horario seleccionado.';
    }

    /**
     * Genera sugerencias de horarios alternativos disponibles
     * 
     * @param int $spaceId ID del espacio
     * @param string $date Fecha de la reserva
     * @param string $requestedStart Hora de inicio solicitada
     * @param string $requestedEnd Hora de fin solicitada
     * @return array Array de horarios sugeridos
     */
    private static function getSuggestedTimes(int $spaceId, string $date, string $requestedStart, string $requestedEnd): array
    {
        $suggestions = [];
        $duration = (strtotime($requestedEnd) - strtotime($requestedStart)) / 3600; // Duración en horas
        
        // Generar posibles horarios (de 7:00 a 17:00, cada 30 minutos)
        $startHour = 7;
        $endHour = 17;
        $interval = 30; // minutos
        
        for ($hour = $startHour; $hour < $endHour; $hour++) {
            for ($minute = 0; $minute < 60; $minute += $interval) {
                $testStart = sprintf('%02d:%02d', $hour, $minute);
                $testEnd = date('H:i', strtotime($testStart) + ($duration * 3600));
                
                // No sugerir si el horario de fin excede las 17:00
                if (strtotime($testEnd) > strtotime('17:00')) {
                    continue;
                }
                
                // Verificar si este horario está disponible
                if (!self::hasTimeConflict($spaceId, $date, $testStart, $testEnd)) {
                    $suggestions[] = "{$testStart}-{$testEnd}";
                    
                    // Limitar a 3 sugerencias para no sobrecargar el mensaje
                    if (count($suggestions) >= 3) {
                        break 2;
                    }
                }
            }
        }
        
        return $suggestions;
    }
}
