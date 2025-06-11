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
        Schema::create('space_block_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('space_block_id')->constrained('space_blocks')->onDelete('cascade');
            $table->date('exception_date')->comment('Fecha específica para la excepción del bloqueo');
            $table->string('reason')->nullable()->comment('Motivo por el cual se está haciendo una excepción');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Índices para mejorar el rendimiento de las consultas
            $table->index('exception_date');
            
            // Asegurar que no haya duplicados para el mismo bloqueo y fecha
            $table->unique(['space_block_id', 'exception_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('space_block_exceptions');
    }
};
