<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Verificar si existe la columna 'documento' en lugar de 'nit'
        if (Schema::hasColumn('proveedors', 'documento') && !Schema::hasColumn('proveedors', 'nit')) {
            // Agregar columna nit si no existe
            Schema::table('proveedors', function (Blueprint $table) {
                $table->string('nit')->nullable()->after('nombre');
            });
            
            // Copiar datos de 'documento' a 'nit' si existe
            DB::statement('UPDATE proveedors SET nit = documento WHERE documento IS NOT NULL');
            
            \Log::info('Migración completada: Se agregó columna nit y se copiaron datos desde documento');
        }
        
        // Verificar si existe la columna 'contacto' en lugar de 'persona_contacto'
        if (Schema::hasColumn('proveedors', 'contacto') && !Schema::hasColumn('proveedors', 'persona_contacto')) {
            // Agregar columna persona_contacto si no existe
            Schema::table('proveedors', function (Blueprint $table) {
                $table->string('persona_contacto')->nullable()->after('email');
            });
            
            // Copiar datos de 'contacto' a 'persona_contacto' si existe
            DB::statement('UPDATE proveedors SET persona_contacto = contacto WHERE contacto IS NOT NULL');
            
            \Log::info('Migración completada: Se agregó columna persona_contacto y se copiaron datos desde contacto');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Esta migración no es reversible de forma segura
        \Log::warning('Reversión de migración no implementada - no es segura');
    }
};
