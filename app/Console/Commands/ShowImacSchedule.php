<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Equipment;
use App\Models\EquipmentBlock;
use App\Models\SchoolCycle;
use App\Models\CycleDay;

class ShowImacSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'imac:schedule {action=show : Acción a realizar (show|clear|recreate)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gestiona el horario y bloqueos de los IMAC de la sala de informática';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'show':
                $this->showSchedule();
                break;
            case 'clear':
                $this->clearBlocks();
                break;
            case 'recreate':
                $this->recreateBlocks();
                break;
            default:
                $this->error("Acción no válida. Use: show, clear o recreate");
                return 1;
        }

        return 0;
    }

    /**
     * Muestra el horario de bloqueos actual
     */
    protected function showSchedule()
    {
        $imac = Equipment::where('type', 'imac')->where('section', 'sala_informatica')->first();
        
        if (!$imac) {
            $this->error('No se encontró el equipo IMAC.');
            return;
        }

        $blocks = EquipmentBlock::where('equipment_id', $imac->id)
            ->where('is_weekday_block', false)
            ->orderBy('cycle_day')
            ->orderBy('start_time')
            ->get();

        if ($blocks->isEmpty()) {
            $this->warn('No hay bloqueos configurados para los IMAC.');
            return;
        }

        $this->info("Horario de Bloqueos - IMAC Sala de Informática");
        $this->info("Total de unidades: {$imac->total_units}");
        $this->newLine();

        $blocksByDay = $blocks->groupBy('cycle_day');

        foreach ($blocksByDay as $cycleDay => $dayBlocks) {
            $this->line("═══ DÍA {$cycleDay} DEL CICLO ═══");
            
            foreach ($dayBlocks as $block) {
                $startTime = substr($block->start_time, 0, 5); // HH:MM
                $endTime = substr($block->end_time, 0, 5); // HH:MM
                $this->line(sprintf(
                    "  %s - %s | %s unidades | %s",
                    $startTime,
                    $endTime,
                    $block->blocked_units,
                    $block->reason
                ));
            }
            $this->newLine();
        }

        $this->info("Total de bloqueos: " . $blocks->count());
    }

    /**
     * Elimina todos los bloqueos de IMAC
     */
    protected function clearBlocks()
    {
        if (!$this->confirm('¿Está seguro de eliminar TODOS los bloqueos de IMAC?', false)) {
            $this->info('Operación cancelada.');
            return;
        }

        $imac = Equipment::where('type', 'imac')->where('section', 'sala_informatica')->first();
        
        if (!$imac) {
            $this->error('No se encontró el equipo IMAC.');
            return;
        }

        $deleted = EquipmentBlock::where('equipment_id', $imac->id)->delete();
        $this->info("Se eliminaron {$deleted} bloqueos.");
    }

    /**
     * Recrea los bloqueos ejecutando el seeder
     */
    protected function recreateBlocks()
    {
        $this->info('Recreando bloqueos de IMAC...');
        $this->call('db:seed', ['--class' => 'ImacScheduleBlocksSeeder']);
        $this->info('Bloqueos recreados exitosamente.');
    }
}
