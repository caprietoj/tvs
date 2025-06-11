<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SkillCategory extends Model
{
    protected $fillable = [
        'name',
        'description',
        'active',
    ];

    /**
     * Obtiene las subcategorías asociadas a esta categoría de habilidad
     */
    public function subcategories(): HasMany
    {
        return $this->hasMany(SkillSubcategory::class);
    }
}
