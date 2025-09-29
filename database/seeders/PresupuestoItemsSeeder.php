<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PresupuestoItem;
use Carbon\Carbon;

class PresupuestoItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = date('Y');
        
        // Crear algunos datos de prueba básicos para el sistema de presupuesto
        $datosEjemplo = [
            // Datos para PREESCOLAR Y PRIMARIA
            [
                'fuente' => '68',
                'documento' => 'DOC001',
                'fecha' => Carbon::now()->subMonths(3),
                'cuenta' => '616005959501',
                'seccion' => 'PREESCOLAR Y PRIMARIA',
                'rubro' => 'Materiales Educativos',
                'descripcion' => 'Compra de material didáctico para preescolar',
                'valor' => -2500000,
                'valor_moneda' => -2500000,
                'cliente_proveedor' => 'PROV001',
                'nombre_cliente_proveedor' => 'MATERIALES EDUCATIVOS SAS',
                'tercero' => 'TER001',
                'nombre_tercero' => 'MATERIALES EDUCATIVOS SAS',
                'auxiliar' => 'AUX001',
                'centro_costo' => '130301',
                'is_valid_center' => true,
                'es_total' => false
            ],
            [
                'fuente' => '68',
                'documento' => 'DOC002',
                'fecha' => Carbon::now()->subMonths(2),
                'cuenta' => '616005959503',
                'seccion' => 'PREESCOLAR Y PRIMARIA',
                'rubro' => 'Recursos Tecnológicos',
                'descripcion' => 'Tabletas educativas para aulas',
                'valor' => -3200000,
                'valor_moneda' => -3200000,
                'cliente_proveedor' => 'PROV002',
                'nombre_cliente_proveedor' => 'TECNOLOGIA EDUCATIVA LTDA',
                'tercero' => 'TER002',
                'nombre_tercero' => 'TECNOLOGIA EDUCATIVA LTDA',
                'auxiliar' => 'AUX002',
                'centro_costo' => '130301',
                'is_valid_center' => true,
                'es_total' => false
            ],
            
            // Datos para ESCUELA MEDIA
            [
                'fuente' => '69',
                'documento' => 'DOC003',
                'fecha' => Carbon::now()->subMonths(4),
                'cuenta' => '616005959601',
                'seccion' => 'ESCUELA MEDIA',
                'rubro' => 'Material Deportivo',
                'descripcion' => 'Equipos deportivos para educación física',
                'valor' => -1800000,
                'valor_moneda' => -1800000,
                'cliente_proveedor' => 'PROV003',
                'nombre_cliente_proveedor' => 'DEPORTES Y MAS SAS',
                'tercero' => 'TER003',
                'nombre_tercero' => 'DEPORTES Y MAS SAS',
                'auxiliar' => 'AUX003',
                'centro_costo' => '130605',
                'is_valid_center' => true,
                'es_total' => false
            ],
            [
                'fuente' => '69',
                'documento' => 'DOC004',
                'fecha' => Carbon::now()->subMonths(1),
                'cuenta' => '616005959604',
                'seccion' => 'ESCUELA MEDIA',
                'rubro' => 'Laboratorios',
                'descripcion' => 'Reactivos químicos para laboratorio',
                'valor' => -2800000,
                'valor_moneda' => -2800000,
                'cliente_proveedor' => 'PROV004',
                'nombre_cliente_proveedor' => 'LABORATORIOS CIENTIFICOS SAS',
                'tercero' => 'TER004',
                'nombre_tercero' => 'LABORATORIOS CIENTIFICOS SAS',
                'auxiliar' => 'AUX004',
                'centro_costo' => '130503',
                'is_valid_center' => true,
                'es_total' => false
            ],
            
            // Datos para ALTA
            [
                'fuente' => '02',
                'documento' => 'DOC005',
                'fecha' => Carbon::now()->subMonths(5),
                'cuenta' => '616005356002',
                'seccion' => 'ALTA',
                'rubro' => 'Equipos Científicos',
                'descripcion' => 'Microscopios digitales para biología',
                'valor' => -4500000,
                'valor_moneda' => -4500000,
                'cliente_proveedor' => 'PROV005',
                'nombre_cliente_proveedor' => 'EQUIPOS CIENTIFICOS AVANZADOS SAS',
                'tercero' => 'TER005',
                'nombre_tercero' => 'EQUIPOS CIENTIFICOS AVANZADOS SAS',
                'auxiliar' => 'AUX005',
                'centro_costo' => '130504',
                'is_valid_center' => true,
                'es_total' => false
            ],
            [
                'fuente' => '02',
                'documento' => 'DOC006',
                'fecha' => Carbon::now(),
                'cuenta' => '616005452001',
                'seccion' => 'ALTA',
                'rubro' => 'Material Bibliográfico',
                'descripcion' => 'Libros especializados para biblioteca',
                'valor' => -1200000,
                'valor_moneda' => -1200000,
                'cliente_proveedor' => 'PROV006',
                'nombre_cliente_proveedor' => 'DISTRIBUIDORA DE LIBROS SAS',
                'tercero' => 'TER006',
                'nombre_tercero' => 'DISTRIBUIDORA DE LIBROS SAS',
                'auxiliar' => 'AUX006',
                'centro_costo' => '130405',
                'is_valid_center' => true,
                'es_total' => false
            ],
            
            // Algunos registros de dotación (específicos para el filtro que había problemas)
            [
                'fuente' => '68',
                'documento' => 'DOC007',
                'fecha' => Carbon::now()->subMonths(2),
                'cuenta' => '616005055101',
                'seccion' => 'ALTA',
                'rubro' => 'Dotación',
                'descripcion' => 'Sillas ergonómicas para aulas',
                'valor' => -850000,
                'valor_moneda' => -850000,
                'cliente_proveedor' => 'PROV007',
                'nombre_cliente_proveedor' => 'COMERCIALIZADORA ESAN SAS',
                'tercero' => 'TER007',
                'nombre_tercero' => 'COMERCIALIZADORA ESAN SAS',
                'auxiliar' => 'AUX007',
                'centro_costo' => '130904',
                'is_valid_center' => true,
                'es_total' => false
            ],
            [
                'fuente' => '69',
                'documento' => 'DOC008',
                'fecha' => Carbon::now()->subMonths(1),
                'cuenta' => '616005055102',
                'seccion' => 'PREESCOLAR Y PRIMARIA',
                'rubro' => 'Dotación',
                'descripcion' => 'Escritorios para salones',
                'valor' => -1200000,
                'valor_moneda' => -1200000,
                'cliente_proveedor' => 'PROV008',
                'nombre_cliente_proveedor' => 'LOPEZ AGUDELO CARLOS MARIO',
                'tercero' => 'TER008',
                'nombre_tercero' => 'LOPEZ AGUDELO CARLOS MARIO',
                'auxiliar' => 'AUX008',
                'centro_costo' => '130904',
                'is_valid_center' => true,
                'es_total' => false
            ],
            
            // Algunos totales para mostrar estructura completa
            [
                'fuente' => '68',
                'documento' => 'TOTAL001',
                'fecha' => Carbon::now(),
                'cuenta' => '616005959501',
                'seccion' => 'PREESCOLAR Y PRIMARIA',
                'rubro' => 'TOTAL',
                'descripcion' => 'Total Preescolar y Primaria',
                'valor' => -5700000,
                'valor_moneda' => -5700000,
                'cliente_proveedor' => '',
                'nombre_cliente_proveedor' => '',
                'tercero' => '',
                'nombre_tercero' => '',
                'auxiliar' => '',
                'centro_costo' => '130301',
                'is_valid_center' => true,
                'es_total' => true
            ],
            [
                'fuente' => '69',
                'documento' => 'TOTAL002',
                'fecha' => Carbon::now(),
                'cuenta' => '616005959601',
                'seccion' => 'ESCUELA MEDIA',
                'rubro' => 'TOTAL',
                'descripcion' => 'Total Escuela Media',
                'valor' => -4600000,
                'valor_moneda' => -4600000,
                'cliente_proveedor' => '',
                'nombre_cliente_proveedor' => '',
                'tercero' => '',
                'nombre_tercero' => '',
                'auxiliar' => '',
                'centro_costo' => '130503',
                'is_valid_center' => true,
                'es_total' => true
            ],
            [
                'fuente' => '02',
                'documento' => 'TOTAL003',
                'fecha' => Carbon::now(),
                'cuenta' => '616005356002',
                'seccion' => 'ALTA',
                'rubro' => 'TOTAL',
                'descripcion' => 'Total Escuela Alta',
                'valor' => -7750000,
                'valor_moneda' => -7750000,
                'cliente_proveedor' => '',
                'nombre_cliente_proveedor' => '',
                'tercero' => '',
                'nombre_tercero' => '',
                'auxiliar' => '',
                'centro_costo' => '130504',
                'is_valid_center' => true,
                'es_total' => true
            ]
        ];
        
        foreach ($datosEjemplo as $item) {
            PresupuestoItem::create($item);
        }
        
        $this->command->info('Se crearon ' . count($datosEjemplo) . ' registros de presupuesto de ejemplo');
        $this->command->info('Total de registros en presupuesto_items: ' . PresupuestoItem::count());
    }
}
