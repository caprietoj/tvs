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
        // Verificar si la tabla ya existe antes de crearla
        if (!Schema::hasTable('equipment_blocks')) {
            Schema::create('equipment_blocks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('equipment_id')->constrained()->onDelete('cascade');
                $table->foreignId('school_cycle_id')->constrained()->onDelete('cascade');
                $table->integer('cycle_day')->default(0); // 0 para bloqueos semanales, >0 para días específicos
                $table->time('start_time')->nullable();
                $table->time('end_time')->nullable();
                $table->integer('blocked_units'); // Cantidad de unidades bloqueadas
                $table->string('reason')->nullable(); // Razón del bloqueo
                $table->boolean('is_weekday_block')->default(false); // Para bloqueos semanales
                $table->boolean('monday')->default(false);
                $table->boolean('tuesday')->default(false);
                $table->boolean('wednesday')->default(false);
                $table->boolean('thursday')->default(false);
                $table->boolean('friday')->default(false);
                $table->boolean('saturday')->default(false);
                $table->boolean('sunday')->default(false);
                $table->timestamps();

                // Índices para mejorar el rendimiento
                $table->index(['equipment_id', 'school_cycle_id']);
                $table->index(['cycle_day', 'start_time', 'end_time']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_blocks');
    }
};
