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
        Schema::create('presupuesto_secciones', function (Blueprint $table) {
            $table->id();
            $table->string('seccion', 100);
            $table->decimal('presupuesto_total', 15, 2);
            $table->integer('year');
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index(['seccion', 'year']);
            $table->unique(['seccion', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presupuesto_secciones');
    }
};
