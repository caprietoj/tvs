<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ingreso_estudiantes', function (Blueprint $table) {
            // Agregar campos adicionales del estudiante
            $table->string('codigo_estudiante', 50)->nullable()->after('estudiante');
            $table->string('documento_estudiante', 50)->nullable()->after('codigo_estudiante');
            $table->string('apellidos_estudiante')->nullable()->after('documento_estudiante');
            $table->string('eps_estudiante')->nullable()->after('apellidos_estudiante');
            $table->enum('sexo_estudiante', ['M', 'F'])->nullable()->after('eps_estudiante');
            $table->string('tipo_sangre_estudiante', 10)->nullable()->after('sexo_estudiante');
            $table->unsignedBigInteger('estudiante_id')->nullable()->after('tipo_sangre_estudiante');
            
            // Agregar foreign key si existe el estudiante en la base de datos
            $table->foreign('estudiante_id')->references('id')->on('estudiantes')->onDelete('set null');
            
            // Agregar índices para mejorar la búsqueda
            $table->index(['codigo_estudiante']);
            $table->index(['documento_estudiante']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ingreso_estudiantes', function (Blueprint $table) {
            $table->dropForeign(['estudiante_id']);
            $table->dropIndex(['codigo_estudiante']);
            $table->dropIndex(['documento_estudiante']);
            $table->dropColumn([
                'codigo_estudiante',
                'documento_estudiante',
                'apellidos_estudiante',
                'eps_estudiante',
                'sexo_estudiante',
                'tipo_sangre_estudiante',
                'estudiante_id'
            ]);
        });
    }
};
