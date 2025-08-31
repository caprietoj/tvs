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
        Schema::create('presupuesto_aprobado', function (Blueprint $table) {
            $table->id();
            $table->string('seccion', 100);
            $table->string('concepto', 150);
            $table->decimal('monto_aprobado', 15, 2);
            $table->integer('year');
            $table->text('observaciones')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            
            $table->index(['seccion', 'concepto', 'year']);
            $table->unique(['seccion', 'concepto', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presupuesto_aprobado');
    }
};
