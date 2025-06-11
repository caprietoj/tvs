<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends Model
{
    protected $fillable = [
        'skill_subcategory_id',
        'name',
        'description',
        'active',
    ];

    /**
     * Obtiene la subcategoría a la que pertenece esta habilidad
     */
    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(SkillSubcategory::class, 'skill_subcategory_id');
    }

    /**
     * Obtiene los espacios asociados a esta habilidad
     */
    public function spaces(): BelongsToMany
    {
        return $this->belongsToMany(Space::class, 'space_skill', 'skill_id', 'space_id')->withPivot('description')->withTimestamps();
    }

    /**
     * Obtiene las reservas de espacios asociadas a esta habilidad
     */
    public function spaceReservations(): BelongsToMany
    {
        return $this->belongsToMany(SpaceReservation::class, 'space_reservation_skill')->withTimestamps();
    }
}
