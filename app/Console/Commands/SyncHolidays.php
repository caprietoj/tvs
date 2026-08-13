<?php

namespace App\Console\Commands;

use App\Models\Holiday;
use App\Models\SchoolCycle;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncHolidays extends Command
{
    protected $signature = 'holidays:sync 
                            {--year= : Año para sincronizar (default: año actual)}
                            {--dry-run : Mostrar lo que se haría sin ejecutar}
                            {--full-sync : Eliminar de la BD los festivos del año que no estén en el config}';

    protected $description = 'Sincroniza los días festivos desde el archivo de configuración a la base de datos';

    public function handle()
    {
        $year = $this->option('year') ?? Carbon::now()->year;
        $dryRun = $this->option('dry-run');
        $fullSync = $this->option('full-sync');

        $configHolidays = config('school_cycle.holidays.' . $year, []);

        if (empty($configHolidays)) {
            $this->warn("No hay festivos configurados para el año {$year} en config/school_cycle.php");
            return 1;
        }

        $this->info("Sincronizando festivos para el año {$year}...");
        $this->info("Festivos encontrados en configuración: " . count($configHolidays));

        // Festivos en BD para este año
        $dbHolidays = Holiday::whereYear('date', $year)->get()->keyBy(fn($h) => $h->date->format('Y-m-d'));

        if ($dryRun) {
            $this->warn('MODO DRY-RUN: No se realizarán cambios.');
            $this->line('');
            $this->line('Festivos a agregar/actualizar:');
            foreach ($configHolidays as $date => $name) {
                $exists = isset($dbHolidays[$date]);
                $status = $exists ? '(ya existe)' : '[NUEVO]';
                $this->line("  {$date} - {$name} {$status}");
            }
            if ($fullSync) {
                $this->line('');
                $this->line('Festivos en BD que serían ELIMINADOS (no están en config):');
                foreach ($dbHolidays as $date => $holiday) {
                    if (!isset($configHolidays[$date])) {
                        $this->line("  {$date} - {$holiday->name} [ELIMINAR]");
                    }
                }
            }
            return 0;
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $deleted = 0;

        // Agregar o actualizar festivos del config
        foreach ($configHolidays as $date => $name) {
            $holiday = $dbHolidays[$date] ?? null;

            if ($holiday) {
                if ($holiday->name !== $name) {
                    $holiday->update(['name' => $name]);
                    $updated++;
                } else {
                    $skipped++;
                }
            } else {
                Holiday::create([
                    'date' => $date,
                    'name' => $name,
                ]);
                $created++;
            }
        }

        // Eliminar de la BD los festivos del año que no están en el config (solo con --full-sync)
        if ($fullSync) {
            foreach ($dbHolidays as $date => $holiday) {
                if (!isset($configHolidays[$date])) {
                    $this->line("  Eliminando: {$date} - {$holiday->name}");
                    $holiday->delete();
                    $deleted++;
                }
            }
        }

        $this->info("Sincronización completada:");
        $this->line("  Creados:     {$created}");
        $this->line("  Actualizados: {$updated}");
        $this->line("  Sin cambios: {$skipped}");
        if ($fullSync) {
            $this->line("  Eliminados:  {$deleted}");
        }

        if ($created > 0 || $updated > 0 || $deleted > 0) {
            $this->info('Recalculando días del ciclo escolar...');
            $activeCycle = SchoolCycle::where('active', true)->first();
            if ($activeCycle) {
                $activeCycle->cycleDays()->delete();
                $generated = $activeCycle->generateCycleDays();
                $this->info("Regenerados " . count($generated) . " días del ciclo.");
            }
        }

        return 0;
    }
}