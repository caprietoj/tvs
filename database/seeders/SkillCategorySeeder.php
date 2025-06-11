<?php

namespace Database\Seeders;

use App\Models\SkillCategory;
use App\Models\SkillSubcategory;
use Illuminate\Database\Seeder;

class SkillCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Estructura de datos para las categorías y sus subcategorías
        $skillCategories = [
                    [
                        'name' => 'Habilidades de investigación',
                        'subcategories' => [
                            'Habilidades de alfabetización informacional (Encontrar, interpretar, juzgar y crear información)',
                            'Habilidades de alfabetización mediática (Interactuar con los medios para usar y crear ideas e información)',
                        ],
                    ],
                    [
                        'name' => 'Habilidades sociales',
                        'subcategories' => [
                            'Habilidades de colaboración (Trabajar eficazmente con otros)',
                        ],
                    ],
                    [
                        'name' => 'Habilidades de comunicación',
                        'subcategories' => [
                            'Habilidades de comunicación a través de la interacción (Intercambiar pensamientos, mensajes e información de manera efectiva a través de la interacción)',
                            'Habilidades de comunicación a través del lenguaje (Leer, escribir y usar el lenguaje para recopilar y comunicar información)',
                        ],
                    ],
                    [
                        'name' => 'Habilidades de autogestión',
                        'subcategories' => [
                            'Habilidades de organización (Manejar el tiempo y las tareas de manera eficaz)',
                            'Habilidades afectivas (Gestionar el estado de ánimo)',
                            'Habilidades reflexivas ((Re) considerar el proceso de aprendizaje; elegir y usar habilidades ATL)',
                        ],
                    ],
                    [
                        'name' => 'Habilidades de pensamiento',
                        'subcategories' => [
                            'Habilidades de pensamiento crítico (Analizar y evaluar temas e ideas)',
                            'Habilidades de pensamiento creativo (Generar ideas nuevas y considerar nuevas perspectivas)',
                            'Habilidades de transferencia (Usar habilidades y conocimientos en múltiples contextos)',
                        ],
                    ],
                ];
        // Crear las categorías y subcategorías, verificando si ya existen
        foreach ($skillCategories as $categoryData) {
            // Verificar si la categoría ya existe por nombre
            $category = SkillCategory::firstOrCreate(
                ['name' => $categoryData['name']],
                [
                    'description' => 'Categoría para ' . $categoryData['name'],
                    'active' => true,
                ]
            );

            foreach ($categoryData['subcategories'] as $subcategoryName) {
                // Verificar si la subcategoría ya existe para esta categoría
                SkillSubcategory::firstOrCreate(
                    [
                        'skill_category_id' => $category->id,
                        'name' => $subcategoryName
                    ],
                    [
                        'description' => 'Subcategoría ' . $subcategoryName . ' para ' . $categoryData['name'],
                        'active' => true,
                    ]
                );
            }
        }
    }
}
