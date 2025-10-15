<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Seccion;
use App\Models\Rubro;
use App\Models\CentroCosto;
use App\Models\Cuenta;

class BudgetReferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear Secciones
        $secciones = [
            ['codigo' => '11', 'nombre' => 'PREESCOLAR Y PRIMARIA'],
            ['codigo' => '12', 'nombre' => 'ESCUELA MEDIA'],
            ['codigo' => '13', 'nombre' => 'ALTA'],
            ['codigo' => '01', 'nombre' => 'GASTOS INSTITUCIONALES'],
            ['codigo' => '02', 'nombre' => 'GASTOS ADMINISTRATIVOS'],
            ['codigo' => '03', 'nombre' => 'DIRECCION GENERAL'],
            ['codigo' => '04', 'nombre' => 'BIBLIOTECA'],
            ['codigo' => '05', 'nombre' => 'DEPORTES'],
            ['codigo' => '06', 'nombre' => 'CAS'],
            ['codigo' => '07', 'nombre' => 'PAI'],
            ['codigo' => '08', 'nombre' => 'PEP'],
            ['codigo' => '09', 'nombre' => 'PSICOLOGIA INSTITUCIONAL'],
            ['codigo' => '15', 'nombre' => 'TECNOLOGIA INSTITUCIONAL'],
        ];

        foreach ($secciones as $seccionData) {
            Seccion::firstOrCreate(
                ['codigo' => $seccionData['codigo']],
                $seccionData
            );
        }

        // Crear Rubros
        $rubros = [
            ['codigo' => 'CAP', 'nombre' => 'Capacitación'],
            ['codigo' => 'MAT', 'nombre' => 'Material Importado'],
            ['codigo' => 'SER', 'nombre' => 'Servicios Profesionales'],
            ['codigo' => 'BAS', 'nombre' => 'Servicios Básicos'],
            ['codigo' => 'GEN', 'nombre' => 'Servicios Generales'],
            ['codigo' => 'NOM', 'nombre' => 'Nómina'],
            ['codigo' => 'PRE', 'nombre' => 'Prestaciones'],
            ['codigo' => 'PER', 'nombre' => 'Gastos de Personal'],
            ['codigo' => 'OPE', 'nombre' => 'Gastos Operacionales'],
            ['codigo' => 'MAN', 'nombre' => 'Mantenimiento'],
            ['codigo' => 'SEG', 'nombre' => 'Seguros'],
            ['codigo' => 'SRV', 'nombre' => 'Servicios'],
            ['codigo' => 'IMP', 'nombre' => 'Impuestos'],
        ];

        foreach ($rubros as $rubroData) {
            Rubro::firstOrCreate(
                ['codigo' => $rubroData['codigo']],
                $rubroData
            );
        }

        // Crear Centros de Costo con relación a Secciones
        $centrosCosto = [
            ['codigo' => '11001', 'nombre' => 'Preescolar Jornada Mañana', 'seccion_codigo' => '11'],
            ['codigo' => '11002', 'nombre' => 'Primaria Jornada Mañana', 'seccion_codigo' => '11'],
            ['codigo' => '12001', 'nombre' => 'Escuela Media General', 'seccion_codigo' => '12'],
            ['codigo' => '13001', 'nombre' => 'Bachillerato Internacional', 'seccion_codigo' => '13'],
            ['codigo' => '01001', 'nombre' => 'Administración General', 'seccion_codigo' => '01'],
            ['codigo' => '03001', 'nombre' => 'Dirección General', 'seccion_codigo' => '03'],
            ['codigo' => '04001', 'nombre' => 'Biblioteca Central', 'seccion_codigo' => '04'],
            ['codigo' => '05001', 'nombre' => 'Deportes y Recreación', 'seccion_codigo' => '05'],
            ['codigo' => '15001', 'nombre' => 'Tecnología e Informática', 'seccion_codigo' => '15'],
        ];

        foreach ($centrosCosto as $centroCostoData) {
            $seccion = Seccion::where('codigo', $centroCostoData['seccion_codigo'])->first();
            if ($seccion) {
                CentroCosto::firstOrCreate(
                    ['codigo' => $centroCostoData['codigo']],
                    [
                        'codigo' => $centroCostoData['codigo'],
                        'nombre' => $centroCostoData['nombre'],
                        'seccion_id' => $seccion->id
                    ]
                );
            }
        }

        // Crear Cuentas con relación a Rubros
        $cuentas = [
            ['codigo' => '616005109', 'nombre' => 'Capacitación Docente', 'rubro_codigo' => 'CAP'],
            ['codigo' => '616005056', 'nombre' => 'Capacitación Administrativa', 'rubro_codigo' => 'CAP'],
            ['codigo' => '616005356', 'nombre' => 'Material Didáctico Importado', 'rubro_codigo' => 'MAT'],
            ['codigo' => '616005452', 'nombre' => 'Equipos Importados', 'rubro_codigo' => 'MAT'],
            ['codigo' => '616005103', 'nombre' => 'Consultoría Externa', 'rubro_codigo' => 'SER'],
            ['codigo' => '616005202', 'nombre' => 'Servicios Públicos', 'rubro_codigo' => 'BAS'],
            ['codigo' => '51053', 'nombre' => 'Salarios Básicos', 'rubro_codigo' => 'NOM'],
            ['codigo' => '51054', 'nombre' => 'Salarios Variables', 'rubro_codigo' => 'NOM'],
            ['codigo' => '51056', 'nombre' => 'Prestaciones Sociales', 'rubro_codigo' => 'PRE'],
            ['codigo' => '6160', 'nombre' => 'Gastos Administrativos', 'rubro_codigo' => 'OPE'],
            ['codigo' => '6165', 'nombre' => 'Mantenimiento General', 'rubro_codigo' => 'MAN'],
            ['codigo' => '6170', 'nombre' => 'Seguros Institucionales', 'rubro_codigo' => 'SEG'],
            ['codigo' => '6180', 'nombre' => 'Impuestos y Tasas', 'rubro_codigo' => 'IMP'],
        ];

        foreach ($cuentas as $cuentaData) {
            $rubro = Rubro::where('codigo', $cuentaData['rubro_codigo'])->first();
            if ($rubro) {
                Cuenta::firstOrCreate(
                    ['codigo' => $cuentaData['codigo']],
                    [
                        'codigo' => $cuentaData['codigo'],
                        'nombre' => $cuentaData['nombre'],
                        'rubro_id' => $rubro->id
                    ]
                );
            }
        }
    }
}
