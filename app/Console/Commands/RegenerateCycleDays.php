<?php

namespace App\Console\Commands;

use App\Models\Holiday;
use App\Models\SchoolCycle;
use Carbon\Carbon;
use Illuminate\Console\Command;

class RegenerateCycleDays extends Command
{
    protected $signature = 'cycle:regenerate 
                            {--cycle-id= : ID del ciclo escolar a regenerar (usa el activo si no se especifica)}
                            {--end-date= : Fecha de fin para la generación (default: 1 año desde hoy)}
                            {--reset : Eliminar días existentes antes de regenerar}
                            {--dry-run : Mostrar lo que se haría sin ejecutar}';

    protected $description = 'Regenera los días del ciclo escolar recalculando desde la fecha de inicio';

    public function handle()
    {
        $cycleId = $this->option('cycle-id');
        $endDate = $this->option('end-date') ? Carbon::parse($this->option('end-date')) : Carbon::now()->addYear();
        $reset = $this->option('reset');
        $dryRun = $this->option('dry-run');

        if ($cycleId) {
            $cycle = SchoolCycle::find($cycleId);
            if (!$cycle) {
                $this->error("No se encontró un ciclo escolar con ID {$cycleId}.");
                return 1;
            }
        } else {
            $cycle = SchoolCycle::where('active', true)->first();
            if (!$cycle) {
                $this->error('No hay un ciclo escolar activo. Especifica uno con --cycle-id.');
                return 1;
            }
        }

        $this->info("Ciclo escolar: {$cycle->name}");
        $this->info("Fecha de inicio: {$cycle->start_date->format('d/m/Y')}");
        $this->info("Longitud del ciclo: {$cycle->cycle_length} días");
        $this->info("Fecha de fin de generación: {$endDate->format('d/m/Y')}");

        if ($dryRun) {
            $this->warn('MODO DRY-RUN: No se realizarán cambios.');
            $count = 0;
            $current = Carbon::parse($cycle->start_date);
            $dayCounter = max(1, (int) ($cycle->start_cycle_day ?? 1));

            while ($current->lte($endDate)) {
                if (!$current->isWeekend() && !Holiday::where('date', $current->format('Y-m-d'))->exists()) {
                    $this->line("  {$current->format('d/m/Y')} ({$current->dayName}) → Día {$dayCounter}");
                    $dayCounter++;
                    if ($dayCounter > $cycle->cycle_length) {
                        $dayCounter = 1;
                    }
                    $count++;
                }
                $current->addDay();
            }

            $this->info("Total de días lectivos que se generarían: {$count}");
            return 0;
        }

        if ($reset) {
            $deleted = $cycle->cycleDays()->delete();
            $this->info("Eliminados {$deleted} días de ciclo existentes.");
        }

        $this->info('Generando días del ciclo...');
        $generated = $cycle->generateCycleDays($endDate);
        $this->info("Generados " . count($generated) . " días de ciclo exitosamente.");

        $today = Carbon::today();
        $todayCycleDay = $cycle->calculateCycleDayForDate($today);
        if ($todayCycleDay) {
            $this->info("Hoy ({$today->format('d/m/Y')}) corresponde al Día {$todayCycleDay} del ciclo.");
        } else {
            $this->warn("Hoy ({$today->format('d/m/Y')}) no es un día lectivo.");
        }

        return 0;
    }
}