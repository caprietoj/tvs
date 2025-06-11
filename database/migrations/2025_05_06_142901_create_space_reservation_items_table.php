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
        Schema::create('space_reservation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('space_reservation_id')->constrained()->onDelete('cascade');
            $table->foreignId('space_item_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->string('status')->default('pending'); // pending, approved, rejected, returned
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Índices para mejorar el rendimiento de las consultas
            $table->index('space_reservation_id');
            $table->index('space_item_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('space_reservation_items');
    }
};
