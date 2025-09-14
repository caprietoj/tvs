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
        Schema::create('salida_pedagogica_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salida_pedagogica_id')->constrained('salidas_pedagogicas')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('action'); // 'created', 'updated', 'deleted', 'status_changed', etc.
            $table->json('changes')->nullable(); // Los cambios específicos (antes/después)
            $table->text('notes')->nullable(); // Notas adicionales
            $table->string('ip_address')->nullable(); // IP del usuario
            $table->string('user_agent')->nullable(); // Navegador del usuario
            $table->timestamps();

            // Índices para consultas rápidas con nombres cortos
            $table->index(['salida_pedagogica_id', 'created_at'], 'sp_hist_sp_id_created_idx');
            $table->index(['user_id', 'created_at'], 'sp_hist_user_created_idx');
            $table->index(['action', 'created_at'], 'sp_hist_action_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salida_pedagogica_histories');
    }
};
