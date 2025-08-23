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
        Schema::create('previsita_consolidados', function (Blueprint $table) {
            $table->id();
            $table->string('lugar');
            $table->date('fecha_visita');
            $table->date('vencimiento');
            $table->string('responsable');
            $table->boolean('aprobacion_sitio')->default(false);
            $table->text('observaciones_recomendaciones')->nullable();
            $table->string('novedades_visita_archivo')->nullable(); // Para almacenar la ruta del archivo PDF
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('previsita_consolidados');
    }
};
