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
        Schema::create('cycle_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_cycle_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->integer('cycle_day');
            $table->timestamps();
            
            // Índices para optimizar las consultas
            $table->index('date');
            $table->index(['school_cycle_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cycle_days');
    }
};
