<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IngresoEstudiante;
use App\Models\IngresoColaborador;
use App\Models\User;
use Carbon\Carbon;

class EnfermeriaInformeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener usuarios para asignar como enfermeros (usuarios con rol de enfermería)
        $enfermero = User::whereHas('roles', function($query) {
            $query->where('name', 'like', '%enferm%');
        })->first() ?? User::first();

        if (!$enfermero) {
            $this->command->warn('No se encontró un usuario para asignar como enfermero');
            return;
        }

        // Array de motivos comunes
        $motivosEstudiantes = [
            'Dolor de cabeza',
            'Dolor abdominal',
            'Fiebre',
            'Mareo',
            'Náuseas',
            'Golpe/Traumatismo',
            'Dificultad respiratoria',
            'Sangrado nasal',
            'Dolor muscular',
            'Malestar general'
        ];

        $motivosColaboradores = [
            'Dolor de cabeza',
            'Hipertensión',
            'Dolor lumbar',
            'Estrés laboral',
            'Accidente de trabajo',
            'Dolor muscular',
            'Malestar general',
            'Control de signos vitales'
        ];

        $cursos = [
            '1° Primaria',
            '2° Primaria',
            '3° Primaria',
            '4° Primaria',
            '5° Primaria',
            '6° Bachillerato',
            '7° Bachillerato',
            '8° Bachillerato',
            '9° Bachillerato',
            '10° Bachillerato',
            '11° Bachillerato'
        ];

        $areas = [
            'Administrativa',
            'Académica',
            'Servicios Generales',
            'Mantenimiento',
            'Cocina',
            'Biblioteca',
            'Deportes',
            'Coordinación'
        ];

        $derivaciones = [
            'Retorno al salón',
            'Derivación al médico',
            'Salida a casa',
            'Seguimiento',
            'Derivación a psicología'
        ];

        $derivacionesColaboradores = [
            'Retorno al trabajo',
            'Derivación al médico',
            'Incapacidad',
            'Seguimiento'
        ];

        $estadosSeguimiento = [
            'En observación',
            'Alta',
            'Requiere seguimiento'
        ];

        $vieneDe = [
            'Clase Magistral',
            'Educacion Fisica',
            'Extracurricular',
            'Descanso'
        ];

        $this->command->info('Creando registros de ejemplo para estudiantes...');

        // Crear 50 registros de estudiantes distribuidos en los últimos 30 días
        for ($i = 0; $i < 50; $i++) {
            $fechaIngreso = Carbon::now()->subDays(rand(0, 30));
            $motivo = $motivosEstudiantes[array_rand($motivosEstudiantes)];
            $curso = $cursos[array_rand($cursos)];
            $derivacion = $derivaciones[array_rand($derivaciones)];
            $vieneDeValue = $vieneDe[array_rand($vieneDe)];

            IngresoEstudiante::create([
                'fecha' => $fechaIngreso->format('Y-m-d'),
                'hora' => $fechaIngreso->format('H:i:s'),
                'estudiante' => 'Estudiante ' . ($i + 1) . ' Ejemplo',
                'documento_estudiante' => '100000' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'curso' => $curso,
                'viene_de' => $vieneDeValue,
                'motivo' => $motivo,
                'descripcion_evento' => 'Descripción detallada del motivo de ingreso: ' . $motivo,
                'accion_enfermeria' => $this->getProcedimientoAleatorio($motivo) . '. Medicamento: ' . $this->getMedicamentoAleatorio($motivo),
                'seguimiento' => 'Observaciones del caso',
                'derivacion_estudiante' => $derivacion,
                'user_id' => $enfermero->id,
                'created_at' => $fechaIngreso,
                'updated_at' => $fechaIngreso,
            ]);
        }

        $this->command->info('Creando registros de ejemplo para colaboradores...');

        // Crear 30 registros de colaboradores distribuidos en los últimos 30 días
        for ($i = 0; $i < 30; $i++) {
            $fechaIngreso = Carbon::now()->subDays(rand(0, 30));
            $motivo = $motivosColaboradores[array_rand($motivosColaboradores)];
            $area = $areas[array_rand($areas)];
            $derivacion = $derivacionesColaboradores[array_rand($derivacionesColaboradores)];
            $estadoSeguimiento = $estadosSeguimiento[array_rand($estadosSeguimiento)];

            IngresoColaborador::create([
                'fecha' => $fechaIngreso->format('Y-m-d'),
                'hora' => $fechaIngreso->format('H:i:s'),
                'nombre_completo' => 'Colaborador ' . ($i + 1) . ' Ejemplo',
                'documento_colaborador' => '200000' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'area_colaborador' => $area,
                'email' => 'colaborador' . ($i + 1) . '@ejemplo.com',
                'motivo' => $motivo,
                'descripcion_evento' => 'Descripción detallada del motivo de ingreso: ' . $motivo . '. Área: ' . $area,
                'accion_enfermeria' => $this->getProcedimientoAleatorio($motivo) . '. Medicamento: ' . $this->getMedicamentoAleatorio($motivo),
                'seguimiento' => $estadoSeguimiento,
                'derivacion_colaborador' => $derivacion,
                'user_id' => $enfermero->id,
                'created_at' => $fechaIngreso,
                'updated_at' => $fechaIngreso,
            ]);
        }

        $this->command->info('✅ Se crearon 50 registros de estudiantes y 30 de colaboradores para el informe de enfermería');
    }

    private function getProcedimientoAleatorio($motivo)
    {
        $procedimientos = [
            'Dolor de cabeza' => 'Reposo, aplicación de compresas frías',
            'Dolor abdominal' => 'Valoración, reposo, observación',
            'Fiebre' => 'Toma de temperatura, hidratación, reposo',
            'Mareo' => 'Reposo en camilla, hidratación',
            'Náuseas' => 'Reposo, hidratación',
            'Golpe/Traumatismo' => 'Aplicación de hielo, valoración, limpieza de herida',
            'Dificultad respiratoria' => 'Nebulización, oxigenoterapia si es necesario',
            'Sangrado nasal' => 'Compresión nasal, aplicación de hielo',
            'Dolor muscular' => 'Aplicación de compresas frías/calientes',
            'Malestar general' => 'Reposo, observación',
            'Hipertensión' => 'Toma de presión arterial, reposo',
            'Dolor lumbar' => 'Valoración, recomendaciones posturales',
            'Estrés laboral' => 'Escucha activa, técnicas de relajación',
            'Accidente de trabajo' => 'Curación de herida, reporte de accidente',
            'Control de signos vitales' => 'Toma de signos vitales, registro',
        ];

        return $procedimientos[$motivo] ?? 'Valoración general';
    }

    private function getMedicamentoAleatorio($motivo)
    {
        $medicamentos = [
            'Dolor de cabeza' => 'Acetaminofén 500mg',
            'Dolor abdominal' => 'Buscapina 10mg',
            'Fiebre' => 'Acetaminofén 500mg',
            'Mareo' => 'Dimenhidrinato 50mg',
            'Náuseas' => 'Dimenhidrinato 50mg',
            'Golpe/Traumatismo' => 'Ibuprofeno 400mg (si es necesario)',
            'Dificultad respiratoria' => 'Salbutamol nebulizado',
            'Sangrado nasal' => 'No aplica',
            'Dolor muscular' => 'Ibuprofeno 400mg',
            'Malestar general' => 'Según valoración',
            'Hipertensión' => 'Según indicación médica',
            'Dolor lumbar' => 'Ibuprofeno 400mg',
            'Estrés laboral' => 'No aplica',
            'Accidente de trabajo' => 'Según necesidad',
            'Control de signos vitales' => 'No aplica',
        ];

        return $medicamentos[$motivo] ?? 'No administrado';
    }
}
