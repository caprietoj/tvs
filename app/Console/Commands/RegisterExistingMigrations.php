<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class RegisterExistingMigrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:register-existing
                            {--dry-run : Mostrar qué migraciones se registrarían sin hacerlo}
                            {--force : Forzar la operación sin confirmación}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Registra migraciones cuyas tablas ya existen en la base de datos';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Verificando migraciones pendientes contra tablas existentes...');
        $this->newLine();

        // Obtener migraciones pendientes
        $pendingMigrations = $this->getPendingMigrations();
        
        if (empty($pendingMigrations)) {
            $this->info('✅ No hay migraciones pendientes. Todo está sincronizado.');
            return 0;
        }

        $this->info("📋 Encontradas " . count($pendingMigrations) . " migraciones pendientes:");
        $this->newLine();

        $toRegister = [];

        foreach ($pendingMigrations as $migration) {
            $tableName = $this->extractTableName($migration);
            
            if ($tableName && Schema::hasTable($tableName)) {
                $toRegister[] = [
                    'migration' => $migration,
                    'table' => $tableName,
                ];
                $this->line("  ⚠️  <fg=yellow>{$migration}</> → Tabla '<fg=cyan>{$tableName}</>' <fg=green>YA EXISTE</>");
            } else {
                $status = $tableName ? "<fg=red>NO EXISTE</>" : "<fg=gray>N/A</>";
                $this->line("  ℹ️  <fg=gray>{$migration}</> → {$status}");
            }
        }

        $this->newLine();

        if (empty($toRegister)) {
            $this->warn('⚠️  No hay tablas existentes para registrar.');
            $this->info('💡 Ejecuta "php artisan migrate" para crear las tablas faltantes.');
            return 0;
        }

        $this->info("🎯 Migraciones a registrar: " . count($toRegister));
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn('🔍 MODO DRY-RUN: No se realizarán cambios');
            $this->table(
                ['Migración', 'Tabla'],
                array_map(fn($item) => [$item['migration'], $item['table']], $toRegister)
            );
            return 0;
        }

        // Confirmación
        if (!$this->option('force')) {
            if (!$this->confirm('¿Deseas registrar estas migraciones sin ejecutarlas?', false)) {
                $this->warn('❌ Operación cancelada.');
                return 1;
            }
        }

        // Registrar migraciones
        $this->newLine();
        $this->info('📝 Registrando migraciones...');
        
        $nextBatch = (int) DB::table('migrations')->max('batch') + 1;
        $registered = 0;

        foreach ($toRegister as $item) {
            try {
                DB::table('migrations')->insert([
                    'migration' => $item['migration'],
                    'batch' => $nextBatch,
                ]);
                $this->line("  ✅ <fg=green>{$item['migration']}</>");
                $registered++;
            } catch (\Exception $e) {
                $this->error("  ❌ Error al registrar {$item['migration']}: " . $e->getMessage());
            }
        }

        $this->newLine();
        $this->info("✨ Se registraron exitosamente {$registered} de " . count($toRegister) . " migraciones en el batch #{$nextBatch}");
        
        if ($registered < count($toRegister)) {
            $this->warn('⚠️  Algunas migraciones no pudieron ser registradas. Verifica los errores arriba.');
        }

        $this->newLine();
        $this->info('💡 Ahora puedes ejecutar "php artisan migrate" para ejecutar las migraciones restantes.');

        return 0;
    }

    /**
     * Obtener lista de migraciones pendientes
     */
    protected function getPendingMigrations(): array
    {
        $migrationFiles = $this->getMigrationFiles();
        $ranMigrations = $this->getRanMigrations();

        return array_diff($migrationFiles, $ranMigrations);
    }

    /**
     * Obtener todos los archivos de migración
     */
    protected function getMigrationFiles(): array
    {
        $path = database_path('migrations');
        $files = File::files($path);

        return array_map(function ($file) {
            return str_replace('.php', '', $file->getFilename());
        }, $files);
    }

    /**
     * Obtener migraciones ya ejecutadas
     */
    protected function getRanMigrations(): array
    {
        return DB::table('migrations')
            ->orderBy('batch')
            ->orderBy('migration')
            ->pluck('migration')
            ->all();
    }

    /**
     * Extraer nombre de tabla desde el nombre de la migración
     */
    protected function extractTableName(string $migration): ?string
    {
        // Patrones comunes en nombres de migraciones
        $patterns = [
            '/create_(\w+)_table/',           // create_users_table
            '/add_\w+_to_(\w+)_table/',       // add_column_to_users_table
            '/modify_\w+_in_(\w+)/',          // modify_column_in_users
            '/update_(\w+)_table/',           // update_users_table
            '/alter_(\w+)_table/',            // alter_users_table
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $migration, $matches)) {
                return $matches[1];
            }
        }

        // Para migraciones tipo "create" sin sufijo _table
        if (preg_match('/create_(\w+)$/', $migration, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
