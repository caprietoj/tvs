<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Space extends Model
{
    protected $fillable = [
        'name',
        'description',
        'capacity',
        'location',
        'image_path',
        'active',
        'is_library',
    ];

    /**
     * Obtiene las reservas asociadas a este espacio
     */
    public function reservations(): HasMany
    {
        return $this->hasMany(SpaceReservation::class);
    }

    /**
     * Obtiene los bloqueos asociados a este espacio
     */
    public function blocks(): HasMany
    {
        return $this->hasMany(SpaceBlock::class);
    }

    /**
     * Verifica si un espacio está bloqueado para un día específico de ciclo
     */
    public function isBlockedForCycleDay(int $schoolCycleId, int $cycleDay): bool
    {
        return $this->blocks()
            ->where('school_cycle_id', $schoolCycleId)
            ->where('cycle_day', $cycleDay)
            ->exists();
    }
    
    /**
     * Obtiene los ítems para préstamo asociados a este espacio
     */
    public function items(): HasMany
    {
        return $this->hasMany(SpaceItem::class);
    }

    /**
     * Obtiene las habilidades asociadas a este espacio
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'space_skill', 'space_id', 'skill_id')
            ->withPivot('description')
            ->withTimestamps();
    }
}
