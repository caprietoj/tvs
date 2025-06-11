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
        Schema::create('space_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('space_id')->constrained()->onDelete('cascade');
            $table->foreignId('school_cycle_id')->constrained()->onDelete('cascade');
            $table->integer('cycle_day');
            $table->text('reason')->nullable();
            $table->timestamps();
            
            // Índices para optimizar las consultas
            $table->index(['space_id', 'school_cycle_id', 'cycle_day']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('space_blocks');
    }
};
