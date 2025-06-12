<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Proveedor;

class CreateTestProvider extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:test-provider';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crear un proveedor de prueba con todos los datos';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Creando proveedor de prueba...');
        
        $proveedor = Proveedor::create([
            'nombre' => 'IDENTIDAD PÚBLICA DISEÑO Y MEDIOS PUBLICITARIOS SAS',
            'nit' => '900.123.456-7',
            'direccion' => 'Calle 123 #45-67, Bogotá',
            'ciudad' => 'Bogotá',
            'telefono' => '601-234-5678',
            'email' => 'contacto@identidadpublica.com',
            'persona_contacto' => 'Juan Carlos Pérez',
            'servicio_producto' => 'Diseño gráfico y medios publicitarios',
            'forma_pago' => 'Contado',
            'estado' => 'Seleccionado',
        ]);
        
        $this->info("Proveedor creado exitosamente:");
        $this->line("ID: {$proveedor->id}");
        $this->line("Nombre: {$proveedor->nombre}");
        $this->line("NIT: {$proveedor->nit}");
        $this->line("Contacto: {$proveedor->persona_contacto}");
        $this->line("Teléfono: {$proveedor->telefono}");
        
        return 0;
    }
}
