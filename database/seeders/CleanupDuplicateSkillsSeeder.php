<?php

namespace Database\Seeders;

use App\Models\SkillCategory;
use App\Models\SkillSubcategory;
use App\Models\Skill;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CleanupDuplicateSkillsSeeder extends Seeder
{
    /**
     * Limpia las categorías, subcategorías y habilidades duplicadas.
     */
    public function run(): void
    {
        // Activar modo de mantenimiento antes de comenzar
        $this->command->info('Activando modo de mantenimiento...');
        $this->command->call('down');
        
        // Comienza la transacción para asegurar consistencia
        DB::beginTransaction();
        
        try {
            // Desactivar verificación de claves foráneas temporalmente para evitar problemas
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            $this->command->info('Limpiando categorías duplicadas...');
            $this->cleanupDuplicateCategories();
            
            $this->command->info('Limpiando subcategorías duplicadas...');
            $this->cleanupDuplicateSubcategories();
            
            // Volver a activar verificación de claves foráneas
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            DB::commit();
            
            $this->command->info('Limpieza de duplicados completada con éxito.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('¡Error durante la limpieza! ' . $e->getMessage());
            Log::error('Error en CleanupDuplicateSkillsSeeder: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
        
        // Desactivar modo de mantenimiento
        $this->command->info('Desactivando modo de mantenimiento...');
        $this->command->call('up');
    }
    
    /**
     * Limpia las categorías duplicadas
     */
    private function cleanupDuplicateCategories(): void
    {
        // Obtener categorías agrupadas por nombre
        $categoriesByName = SkillCategory::get()->groupBy('name');
        
        foreach ($categoriesByName as $name => $categories) {
            // Si hay más de una categoría con el mismo nombre, mantener solo la más antigua
            if ($categories->count() > 1) {
                $this->command->info("  Encontradas {$categories->count()} categorías duplicadas para: {$name}");
                
                // La categoría a mantener (la más antigua)
                $keepCategory = $categories->sortBy('id')->first();
                $this->command->info("  Manteniendo la categoría ID: {$keepCategory->id}");
                
                // Categorías a eliminar (todas menos la más antigua)
                $deleteCategories = $categories->filter(function ($category) use ($keepCategory) {
                    return $category->id !== $keepCategory->id;
                });
                
                // Mover todas las subcategorías a la categoría que se mantiene
                foreach ($deleteCategories as $deleteCategory) {
                    $this->command->info("  Moviendo subcategorías de ID: {$deleteCategory->id} a ID: {$keepCategory->id}");
                    
                    // Actualizar las subcategorías para que pertenezcan a la categoría que se mantiene
                    SkillSubcategory::where('skill_category_id', $deleteCategory->id)
                        ->update(['skill_category_id' => $keepCategory->id]);
                    
                    // Eliminar la categoría duplicada
                    $deleteCategory->delete();
                    $this->command->info("  Categoría ID: {$deleteCategory->id} eliminada");
                }
            }
        }
    }
    
    /**
     * Limpia las subcategorías duplicadas
     */
    private function cleanupDuplicateSubcategories(): void
    {
        // Para cada categoría
        $categories = SkillCategory::all();
        
        foreach ($categories as $category) {
            // Obtener subcategorías agrupadas por nombre para esta categoría
            $subcategoriesByName = $category->subcategories()->get()->groupBy('name');
            
            foreach ($subcategoriesByName as $name => $subcategories) {
                // Si hay más de una subcategoría con el mismo nombre en la misma categoría
                if ($subcategories->count() > 1) {
                    $this->command->info("  Encontradas {$subcategories->count()} subcategorías duplicadas para: {$name} en categoría ID: {$category->id}");
                    
                    // La subcategoría a mantener (la más antigua)
                    $keepSubcategory = $subcategories->sortBy('id')->first();
                    $this->command->info("  Manteniendo la subcategoría ID: {$keepSubcategory->id}");
                    
                    // Subcategorías a eliminar (todas menos la más antigua)
                    $deleteSubcategories = $subcategories->filter(function ($subcategory) use ($keepSubcategory) {
                        return $subcategory->id !== $keepSubcategory->id;
                    });
                    
                    foreach ($deleteSubcategories as $deleteSubcategory) {
                        $this->command->info("  Moviendo habilidades de subcategoría ID: {$deleteSubcategory->id} a ID: {$keepSubcategory->id}");
                        
                        // Actualizar las habilidades para que pertenezcan a la subcategoría que se mantiene
                        if (Schema::hasTable('skills')) {
                            Skill::where('skill_subcategory_id', $deleteSubcategory->id)
                                ->update(['skill_subcategory_id' => $keepSubcategory->id]);
                        }
                        
                        // Eliminar la subcategoría duplicada
                        $deleteSubcategory->delete();
                        $this->command->info("  Subcategoría ID: {$deleteSubcategory->id} eliminada");
                    }
                }
            }
        }
    }
}
