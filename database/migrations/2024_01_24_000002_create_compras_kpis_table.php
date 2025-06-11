<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('compras_kpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('threshold_id')->constrained('compras_thresholds')->onDelete('cascade');
            $table->string('name');
            $table->string('type');
            $table->text('methodology');
            $table->string('frequency');
            $table->date('measurement_date');
            $table->decimal('percentage', 5, 2);
            $table->text('analysis')->nullable();
            // Removida la línea de url ya que la columna ya existe
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('compras_kpis');
    }
};