<?php

namespace App\Console\Commands;

use Database\Seeders\UpdateSkillSubcategoriesSeeder;
use Illuminate\Console\Command;

class UpdateSkillSubcategories extends Command
{
    /**
     * El nombre y firma del comando.
     *
     * @var string
     */
    protected $signature = 'skills:update-subcategories';

    /**
     * La descripción del comando de consola.
     *
     * @var string
     */
    protected $description = 'Actualiza la estructura de subcategorías de habilidades en la base de datos';

    /**
     * Ejecuta el comando de consola.
     */
    public function handle()
    {
        $this->info('Actualizando estructura de subcategorías de habilidades...');
        
        // Confirmación para continuar
        if (!$this->confirm('¿Estás seguro de que deseas actualizar la estructura de subcategorías? Esta acción puede modificar datos existentes.')) {
            $this->info('Operación cancelada.');
            return 1;
        }
        
        $this->info('Iniciando actualización de subcategorías de habilidades...');
        
        // Llamar al seeder para procesar los cambios
        $seeder = new UpdateSkillSubcategoriesSeeder();
        $seeder->setCommand($this);
        $seeder->run();
        
        $this->info('La estructura de subcategorías de habilidades ha sido actualizada correctamente.');
        
        return 0;
    }
}
