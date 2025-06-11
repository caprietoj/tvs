<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SkillSubcategory extends Model
{
    protected $fillable = [
        'skill_category_id',
        'name',
        'description',
        'active',
    ];

    /**
     * Obtiene la categoría a la que pertenece esta subcategoría
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(SkillCategory::class, 'skill_category_id');
    }

    /**
     * Obtiene las habilidades asociadas a esta subcategoría
     */
    public function skills(): HasMany
    {
        return $this->hasMany(Skill::class, 'skill_subcategory_id');
    }
}
