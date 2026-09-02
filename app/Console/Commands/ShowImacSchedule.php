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
    protected $signature = 'imac:schedule {action=show : Acción a realizar (show|clear|recreate)} {--sala= : Sección de la sala (sala_informatica, sala_informatica_primer_piso, o todas)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gestiona el horario y bloqueos de los IMAC de las salas de informática';

    /**
     * Secciones de salas de informática
     */
    protected array $salas = [
        'sala_informatica' => 'Segundo Piso',
        'sala_informatica_primer_piso' => 'Primer Piso',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $sala = $this->option('sala');

        switch ($action) {
            case 'show':
                $this->showSchedule($sala);
                break;
            case 'clear':
                $this->clearBlocks($sala);
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
     * Obtiene las secciones a procesar según la opción --sala
     */
    protected function getSections(?string $sala): array
    {
        if ($sala && isset($this->salas[$sala])) {
            return [$sala => $this->salas[$sala]];
        }

        if ($sala && $sala !== 'todas') {
            $this->error("Sección no válida. Opciones: " . implode(', ', array_keys($this->salas)) . ", todas");
            return [];
        }

        return $this->salas;
    }

    /**
     * Muestra el horario de bloqueos actual
     */
    protected function showSchedule(?string $sala): void
    {
        $sections = $this->getSections($sala);

        if (empty($sections)) {
            return;
        }

        foreach ($sections as $sectionKey => $sectionName) {
            $equipment = Equipment::where('type', 'imac')->where('section', $sectionKey)->first();

            if (!$equipment) {
                $this->warn("No se encontró equipo IMAC para la sección '{$sectionKey}' ({$sectionName}).");
                continue;
            }

            $blocks = EquipmentBlock::where('equipment_id', $equipment->id)
                ->where('is_weekday_block', false)
                ->orderBy('cycle_day')
                ->orderBy('start_time')
                ->get();

            $this->info("═══════════════════════════════════════════");
            $this->info(" Horario de Bloqueos - IMAC Sala de Informática {$sectionName}");
            $this->info(" Sección: {$sectionKey} | Equipo ID: {$equipment->id} | Unidades: {$equipment->total_units}");
            $this->info("═══════════════════════════════════════════");

            if ($blocks->isEmpty()) {
                $this->warn("No hay bloqueos configurados para esta sala.");
                $this->newLine();
                continue;
            }

            $blocksByDay = $blocks->groupBy('cycle_day');

            foreach ($blocksByDay as $cycleDay => $dayBlocks) {
                $this->newLine();
                $this->info("─── DÍA {$cycleDay} DEL CICLO ───");

                foreach ($dayBlocks as $block) {
                    $startTime = substr($block->start_time, 0, 5);
                    $endTime = substr($block->end_time, 0, 5);
                    $this->line(sprintf(
                        "  %s - %s | %s unidades | %s",
                        $startTime,
                        $endTime,
                        $block->blocked_units,
                        $block->reason
                    ));
                }
            }

            $this->newLine();
            $this->info("Total de bloqueos: " . $blocks->count());
            $this->newLine();
        }
    }

    /**
     * Elimina los bloqueos de IMAC
     */
    protected function clearBlocks(?string $sala): void
    {
        $sections = $this->getSections($sala);

        if (empty($sections)) {
            return;
        }

        if (!$this->confirm('¿Está seguro de eliminar TODOS los bloqueos de IMAC para las salas seleccionadas?', false)) {
            $this->info('Operación cancelada.');
            return;
        }

        foreach ($sections as $sectionKey => $sectionName) {
            $equipment = Equipment::where('type', 'imac')->where('section', $sectionKey)->first();

            if (!$equipment) {
                $this->warn("No se encontró equipo IMAC para la sección '{$sectionKey}' ({$sectionName}).");
                continue;
            }

            $deleted = EquipmentBlock::where('equipment_id', $equipment->id)->delete();
            $this->info("Se eliminaron {$deleted} bloqueos de {$sectionName}.");
        }
    }

    /**
     * Recrea los bloqueos ejecutando el seeder
     */
    protected function recreateBlocks(): void
    {
        $this->info('Recreando bloqueos de IMAC para todas las salas...');
        $this->call('db:seed', ['--class' => 'ImacScheduleBlocksSeeder']);
        $this->info('Bloqueos recreados exitosamente.');
    }
}
