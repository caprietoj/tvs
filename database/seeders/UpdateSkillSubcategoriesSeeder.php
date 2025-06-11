<?php

namespace Database\Seeders;

use App\Models\SkillCategory;
use App\Models\SkillSubcategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateSkillSubcategoriesSeeder extends Seeder
{
    /**
     * Actualiza las subcategorías de habilidades según la nueva estructura.
     */
    public function run(): void
    {
        // Desactivar verificación de claves foráneas temporalmente
        DB::statement('SET FOREIGN_KEY_CHECKS=0');        // Estructura nueva de categorías y subcategorías
        $skillCategories = [
            [
                'name' => 'Habilidades de investigación',
                'subcategories' => [
                    'Habilidades de alfabetización informacional',
                    'Habilidades de alfabetización mediática',
                ],
            ],
            [
                'name' => 'Habilidades sociales',
                'subcategories' => [
                    'Habilidades de colaboración',
                ],
            ],
            [
                'name' => 'Habilidades de comunicación',
                'subcategories' => [
                    'Habilidades de comunicación a través de la interacción',
                    'Habilidades de comunicación a través del lenguaje',
                ],
            ],
            [
                'name' => 'Habilidades de autogestión',
                'subcategories' => [
                    'Habilidades de organización',
                    'Habilidades afectivas',
                    'Habilidades reflexivas',
                ],
            ],
            [
                'name' => 'Habilidades de pensamiento',
                'subcategories' => [
                    'Habilidades de pensamiento crítico',
                    'Habilidades de pensamiento creativo',
                    'Habilidades de transferencia',
                ],
            ],
        ];

        // Para cada categoría existente, actualizamos sus subcategorías
        foreach ($skillCategories as $categoryData) {
            $category = SkillCategory::where('name', $categoryData['name'])->first();
            
            // Si la categoría no existe, la creamos
            if (!$category) {
                $category = SkillCategory::create([
                    'name' => $categoryData['name'],
                    'description' => 'Categoría para ' . $categoryData['name'],
                    'active' => true,
                ]);
                $this->command->info("Categoría creada: {$categoryData['name']}");
            } else {
                $this->command->info("Categoría encontrada: {$categoryData['name']}");
            }
            
            // Obtenemos las subcategorías actuales
            $existingSubcategories = $category->subcategories()->pluck('name')->toArray();
            
            // Procesamos las subcategorías de esta categoría
            foreach ($categoryData['subcategories'] as $subcategoryName) {
                // Verificamos si ya existe
                $subcategory = $category->subcategories()->where('name', $subcategoryName)->first();
                
                if (!$subcategory) {
                    // Creamos la subcategoría si no existe
                    SkillSubcategory::create([
                        'skill_category_id' => $category->id,
                        'name' => $subcategoryName,
                        'description' => 'Subcategoría ' . $subcategoryName . ' para ' . $categoryData['name'],
                        'active' => true,
                    ]);
                    $this->command->info("  - Subcategoría creada: {$subcategoryName}");
                } else {
                    $this->command->info("  - Subcategoría encontrada: {$subcategoryName}");
                }
            }
            
            // Identificamos subcategorías que ya no están en la nueva estructura
            $subcategoriesToRemove = array_diff($existingSubcategories, $categoryData['subcategories']);
            
            if (!empty($subcategoriesToRemove)) {
                $this->command->info("  - Subcategorías a desactivar: " . implode(', ', $subcategoriesToRemove));
                
                // Desactivamos las subcategorías en lugar de eliminarlas (para mantener integridad referencial)
                $category->subcategories()
                    ->whereIn('name', $subcategoriesToRemove)
                    ->update(['active' => false]);
            }
        }

        // Reactivar verificación de claves foráneas
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        $this->command->info('Subcategorías de habilidades actualizadas correctamente.');
    }
}
