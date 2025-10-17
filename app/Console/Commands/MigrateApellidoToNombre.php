<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateApellidoToNombre extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'porteria:migrate-apellido';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migra datos de apellido a nombre en las tablas personas y registro_porteria';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Iniciando migración de apellido a nombre...');
        
        // Verificar y migrar tabla personas
        if (Schema::hasTable('personas') && Schema::hasColumn('personas', 'apellido')) {
            $this->info('📋 Procesando tabla personas...');
            
            $updated = DB::table('personas')
                ->whereNotNull('apellido')
                ->where('apellido', '!=', '')
                ->update([
                    'nombre' => DB::raw("CONCAT(nombre, ' ', apellido)"),
                    'apellido' => null
                ]);
            
            $this->info("✅ {$updated} registros actualizados en personas");
        } else {
            $this->comment('⏭️  Tabla personas no requiere migración (apellido ya eliminado)');
        }
        
        // Verificar y migrar tabla registro_porteria
        if (Schema::hasTable('registro_porteria') && Schema::hasColumn('registro_porteria', 'apellido')) {
            $this->info('📋 Procesando tabla registro_porteria...');
            
            $updated = DB::table('registro_porteria')
                ->whereNotNull('apellido')
                ->where('apellido', '!=', '')
                ->update([
                    'nombre' => DB::raw("CONCAT(nombre, ' ', apellido)"),
                    'apellido' => null
                ]);
            
            $this->info("✅ {$updated} registros actualizados en registro_porteria");
        } else {
            $this->comment('⏭️  Tabla registro_porteria no requiere migración (apellido ya eliminado)');
        }
        
        $this->newLine();
        $this->info('🎉 Migración completada exitosamente!');
        
        return Command::SUCCESS;
    }
}
