<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ParentStudentSurvey;
use Carbon\Carbon;

class ParentStudentSurveySeeder extends Seeder
{
    public function run()
    {
        $period = '2024-12';
        $year = 2024;
        $month = 12;
        
        $grades = ['Preescolar', 'Primero', 'Segundo', 'Tercero', 'Cuarto', 'Quinto'];
        $providers = ['Sapore', 'Aldimark'];
        
        // Generar 150 registros de ejemplo
        for ($i = 1; $i <= 150; $i++) {
            $usesCafeteria = $this->randomWeighted(['Sí' => 70, 'No' => 30]);
            $usesTransport = $this->randomWeighted(['Sí' => 60, 'No' => 40]);
            
            $data = [
                'timestamp' => Carbon::now()->subDays(rand(1, 30)),
                'student_grade' => $grades[array_rand($grades)],
                'period' => $period,
                'year' => $year,
                'month' => $month,
                'provider' => $providers[array_rand($providers)],
                'survey_type' => 'complete',
                'uploaded_by' => 1,
                'uploaded_at' => Carbon::now(),
                'source_file' => 'datos_prueba.xlsx',
                
                // Datos de cafetería
                'uses_cafeteria' => $usesCafeteria,
                'food_quality' => $usesCafeteria === 'Sí' ? $this->randomWeighted(['Excelente' => 30, 'Bueno' => 40, 'Regular' => 25, 'Malo' => 5]) : null,
                'portion_satisfaction' => $usesCafeteria === 'Sí' ? $this->randomWeighted(['Muy satisfecho' => 25, 'Satisfecho' => 45, 'Regular' => 25, 'Insatisfecho' => 5]) : null,
                'menu_offered' => $usesCafeteria === 'Sí' ? $this->randomWeighted(['Excelente' => 35, 'Bueno' => 40, 'Regular' => 20, 'Malo' => 5]) : null,
                'menu_variety' => $usesCafeteria === 'Sí' ? $this->randomWeighted(['Sí' => 60, 'Algunas veces' => 30, 'No' => 10]) : null,
                'food_temperature' => $usesCafeteria === 'Sí' ? $this->randomWeighted(['Sí' => 70, 'Algunas veces' => 25, 'No' => 5]) : null,
                'dining_cleanliness' => $usesCafeteria === 'Sí' ? $this->randomWeighted(['Limpio y ordenado' => 80, 'Regular' => 15, 'Sucio' => 5]) : null,
                'store_service' => $usesCafeteria === 'Sí' ? $this->randomWeighted(['Excelente' => 30, 'Bueno' => 45, 'Regular' => 20, 'Malo' => 5]) : null,
                'staff_treatment_cafeteria' => $usesCafeteria === 'Sí' ? $this->randomWeighted(['Excelente' => 40, 'Bueno' => 35, 'Regular' => 20, 'Malo' => 5]) : null,
                'positive_aspects_cafeteria' => $usesCafeteria === 'Sí' ? 'Buena comida y ambiente agradable' : null,
                'improvement_opportunities_cafeteria' => $usesCafeteria === 'Sí' ? 'Más variedad en el menú' : null,
                'withdrawal_reason_cafeteria' => $usesCafeteria === 'No' ? 'Prefiere llevar comida casera' : null,
                
                // Datos de transporte
                'uses_transport' => $usesTransport,
                'route_number' => $usesTransport === 'Sí' ? 'Ruta ' . rand(1, 15) : null,
                'punctuality' => $usesTransport === 'Sí' ? $this->randomWeighted(['Excelente' => 35, 'Bueno' => 40, 'Regular' => 20, 'Malo' => 5]) : null,
                'vehicle_cleanliness' => $usesTransport === 'Sí' ? $this->randomWeighted(['Excelente' => 45, 'Bueno' => 35, 'Regular' => 15, 'Malo' => 5]) : null,
                'staff_treatment_transport' => $usesTransport === 'Sí' ? $this->randomWeighted(['Excelente' => 50, 'Bueno' => 30, 'Regular' => 15, 'Malo' => 5]) : null,
                'communication' => $usesTransport === 'Sí' ? $this->randomWeighted(['Excelente' => 40, 'Bueno' => 35, 'Regular' => 20, 'Malo' => 5]) : null,
                'positive_aspects_transport' => $usesTransport === 'Sí' ? 'Puntual y conductor amable' : null,
                'improvement_opportunities_transport' => $usesTransport === 'Sí' ? 'Mejor comunicación sobre horarios' : null,
                'withdrawal_reason_transport' => $usesTransport === 'No' ? 'Vive cerca del colegio' : null,
                
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ];
            
            ParentStudentSurvey::create($data);
        }
    }
    
    private function randomWeighted($options)
    {
        $rand = rand(1, 100);
        $cumulative = 0;
        
        foreach ($options as $option => $weight) {
            $cumulative += $weight;
            if ($rand <= $cumulative) {
                return $option;
            }
        }
        
        return array_key_first($options);
    }
}
