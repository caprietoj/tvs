<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class UpdateDepartmentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'departments:update {--assign : Asignar usuarios a departamentos basado en emails}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Actualizar y asignar usuarios a departamentos expandidos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $shouldAssign = $this->option('assign');
        
        // Definir usuarios por departamento basado en patrones de email y nombres conocidos
        $departmentUsers = [
            'Mantenimiento' => [
                'cjmonterroza@tvs.edu.co' => 'CRISTHIAN JOSE MONTERROZA',
                'tcabezas@tvs.edu.co' => 'TEOFILO CABEZAS TORRES',
                'lamaya@tvs.edu.co' => 'LUIS ALBERTO AMAYA RAMIREZ'
            ],
            'Servicios Generales' => [
                'amayala@tvs.edu.co' => 'Ana Milena Ayala',
                'agomez@tvs.edu.co' => 'VIVIANA ANDREA GOMEZ OSORIO',
                'supervsergenerales@tvs.edu.co' => 'Nancy Vichue Oyola',
                'xsanchezy@tvs.edu.co' => 'Xiomara Isabel Sanchez Yanci',
                'mcobosm@tvs.edu.co' => 'MARTHA ISABEL COBOS MARTINEZ',
                'mtrodriguez@tvs.edu.co' => 'MARIA DEL TRANSITO RICO RODRIGUEZ'
            ],
            'Sistemas' => [
                'jefesistemas@tvs.edu.co' => 'Jefe de Sistemas',
                'sistemas@tvs.edu.co' => 'Sistemas'
            ],
            'Almacen' => [
                'almacen@tvs.edu.co' => 'Almacén',
                'compras@tvs.edu.co' => 'Compras'
            ],
            'Enfermeria' => [
                'enfermeria@tvs.edu.co' => 'Enfermería',
                'jefe.enfermeria@tvs.edu.co' => 'Jefe de Enfermería'
            ],
            'Docentes' => [
                // Los docentes se asignarán automáticamente por patrones de email
            ],
            'EMC' => [
                'emc@tvs.edu.co' => 'EMC'
            ],
            'Biblioteca' => [
                'biblioteca@tvs.edu.co' => 'Biblioteca',
                'bibliotecaria@tvs.edu.co' => 'Bibliotecaria'
            ],
            'Contabilidad' => [
                'contabilidad@tvs.edu.co' => 'Contabilidad',
                'contador@tvs.edu.co' => 'Contador'
            ],
            'Asistentes' => [
                'asistente@tvs.edu.co' => 'Asistente',
                'secretaria@tvs.edu.co' => 'Secretaria'
            ]
        ];

        $this->info('=== ANÁLISIS DE DEPARTAMENTOS ===');
        $this->newLine();

        $totalAssigned = 0;
        $totalUsers = User::count();

        foreach ($departmentUsers as $department => $users) {
            $this->info("--- {$department} ---");
            $found = 0;
            $assigned = 0;

            foreach ($users as $email => $name) {
                $user = User::where('email', $email)->first();
                if ($user) {
                    $found++;
                    $currentDept = $user->department ?? 'Sin departamento';
                    $this->info("✅ {$name} ({$email}) - Actual: {$currentDept}");
                    
                    if ($user->department !== $department) {
                        if ($shouldAssign) {
                            $user->update(['department' => $department]);
                            $this->info("   🔄 Asignado a {$department}");
                            $assigned++;
                            $totalAssigned++;
                        } else {
                            $this->warn("   ⚠️  Necesita ser asignado a {$department}");
                        }
                    } else {
                        $this->info("   ✅ Ya está en {$department}");
                    }
                } else {
                    $this->error("❌ {$name} ({$email}) - NO ENCONTRADO");
                }
            }

            if ($shouldAssign && $assigned > 0) {
                $this->info("✅ {$assigned} usuarios asignados a {$department}");
            }
            $this->newLine();
        }

        // Asignar docentes automáticamente por patrón de email
        if ($shouldAssign) {
            $this->info('--- ASIGNACIÓN AUTOMÁTICA DE DOCENTES ---');
            $possibleTeachers = User::where(function($query) {
                $query->where('email', 'like', '%profesor%')
                      ->orWhere('email', 'like', '%docente%')
                      ->orWhere('email', 'like', '%teacher%')
                      ->orWhere('name', 'like', '%profesor%')
                      ->orWhere('name', 'like', '%docente%');
            })->whereNull('department')->get();

            foreach ($possibleTeachers as $teacher) {
                $teacher->update(['department' => 'Docentes']);
                $this->info("✅ {$teacher->name} ({$teacher->email}) asignado a Docentes");
                $totalAssigned++;
            }
        }

        $this->newLine();
        $this->info('=== RESUMEN FINAL ===');
        $usersWithDept = User::whereNotNull('department')->count();
        $this->info("Total usuarios con departamento: {$usersWithDept}/{$totalUsers}");
        
        if ($shouldAssign) {
            $this->info("Usuarios asignados en esta ejecución: {$totalAssigned}");
        }

        // Mostrar distribución por departamento
        $deptCounts = User::selectRaw('department, COUNT(*) as count')
            ->whereNotNull('department')
            ->groupBy('department')
            ->orderBy('department')
            ->get();
            
        if ($deptCounts->count() > 0) {
            $this->newLine();
            $this->info('--- DISTRIBUCIÓN POR DEPARTAMENTO ---');
            foreach ($deptCounts as $dept) {
                $this->info("{$dept->department}: {$dept->count} usuarios");
            }
        }

        if (!$shouldAssign) {
            $this->newLine();
            $this->warn('Modo de solo lectura. Para aplicar cambios ejecuta:');
            $this->warn('php artisan departments:update --assign');
        }

        return 0;
    }
}
