<?php

namespace App\Console\Commands;

use App\Models\SkillCategory;
use Illuminate\Console\Command;

class ListSkillCategories extends Command
{
    /**
     * El nombre y firma del comando.
     *
     * @var string
     */
    protected $signature = 'skills:list';

    /**
     * La descripción del comando de consola.
     *
     * @var string
     */
    protected $description = 'Lista todas las categorías de habilidades con sus subcategorías';

    /**
     * Ejecuta el comando de consola.
     */
    public function handle()
    {
        $this->info('Lista de categorías y subcategorías de habilidades:');
        
        $categories = SkillCategory::with('subcategories')->get();
        
        foreach ($categories as $category) {
            $this->line("\n<fg=green>Categoría: {$category->name}</>");
            
            $this->info("  Subcategorías activas:");
            $activeSubcategories = $category->subcategories->where('active', true);
            if ($activeSubcategories->isEmpty()) {
                $this->line("    <fg=yellow>No hay subcategorías activas</>");
            } else {
                foreach ($activeSubcategories as $subcategory) {
                    $this->line("    <fg=white>- {$subcategory->name}</>");
                }
            }
            
            $inactiveSubcategories = $category->subcategories->where('active', false);
            if ($inactiveSubcategories->isNotEmpty()) {
                $this->info("  Subcategorías inactivas:");
                foreach ($inactiveSubcategories as $subcategory) {
                    $this->line("    <fg=gray>- {$subcategory->name}</>");
                }
            }
        }
        
        return 0;
    }
}
