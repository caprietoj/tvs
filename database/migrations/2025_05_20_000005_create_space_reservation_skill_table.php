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
        // Tabla pivot para relacionar reservas de espacios con habilidades
        Schema::create('space_reservation_skill', function (Blueprint $table) {
            $table->id();
            $table->foreignId('space_reservation_id')->constrained('space_reservations')->onDelete('cascade');
            $table->foreignId('skill_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Aseguramos que no haya duplicados en la relación
            $table->unique(['space_reservation_id', 'skill_id'], 'reservation_skill_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('space_reservation_skill');
    }
};
