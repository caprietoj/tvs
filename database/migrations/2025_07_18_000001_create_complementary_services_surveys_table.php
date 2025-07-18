<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('complementary_services_surveys', function (Blueprint $table) {
            $table->id();
            $table->string('period', 7); // Format: YYYY-MM
            $table->integer('year');
            $table->integer('month');
            $table->timestamp('timestamp');
            $table->string('dependencia')->nullable();
            
            // Cafeteria fields
            $table->string('usa_cafeteria')->nullable();
            $table->string('calidad_sabor')->nullable();
            $table->string('porcion_alimentos')->nullable();
            $table->string('menu_ofrecido')->nullable();
            $table->string('variedad_menu')->nullable();
            $table->string('temperatura_comida')->nullable();
            $table->string('limpieza_comedor')->nullable();
            $table->string('servicio_tienda')->nullable();
            $table->string('trato_personal_cafeteria')->nullable();
            $table->text('aspectos_positivos_cafeteria')->nullable();
            $table->text('oportunidades_mejora_cafeteria')->nullable();
            $table->text('retiro_cafeteria')->nullable();
            
            // Transport fields
            $table->string('usa_transporte')->nullable();
            $table->string('puntualidad_transporte')->nullable();
            $table->string('limpieza_vehiculo')->nullable();
            $table->string('trato_personal_transporte')->nullable();
            $table->string('comunicacion_transporte')->nullable();
            $table->text('aspectos_positivos_transporte')->nullable();
            $table->text('oportunidades_mejora_transporte')->nullable();
            $table->text('retiro_transporte')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['period', 'dependencia']);
            $table->index(['year', 'month']);
            $table->index('usa_cafeteria');
            $table->index('usa_transporte');
        });
    }

    public function down()
    {
        Schema::dropIfExists('complementary_services_surveys');
    }
};
