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
        Schema::create('parent_student_surveys', function (Blueprint $table) {
            $table->id();
            $table->string('period', 7); // 2024-05, 2025-06
            $table->integer('year');
            $table->integer('month');
            $table->datetime('timestamp');
            $table->string('student_grade'); // PK, Kinder, Primero, Segundo, ..., Once
            $table->string('survey_type'); // 'complete', 'transport_only', 'cafeteria_only'
            $table->string('route_number')->nullable(); // Para encuestas de transporte
            
            // Campos de Cafetería
            $table->string('uses_cafeteria')->nullable();
            $table->string('food_quality')->nullable();
            $table->string('portion_satisfaction')->nullable();
            $table->string('menu_offered')->nullable();
            $table->string('menu_variety')->nullable();
            $table->string('food_temperature')->nullable();
            $table->string('dining_cleanliness')->nullable();
            $table->string('store_service')->nullable();
            $table->string('staff_treatment_cafeteria')->nullable();
            $table->text('positive_aspects_cafeteria')->nullable();
            $table->text('improvement_opportunities_cafeteria')->nullable();
            $table->text('withdrawal_reason_cafeteria')->nullable();
            
            // Campos de Transporte
            $table->string('uses_transport')->nullable();
            $table->string('punctuality')->nullable();
            $table->string('vehicle_cleanliness')->nullable();
            $table->string('staff_treatment_transport')->nullable();
            $table->string('communication')->nullable();
            $table->text('positive_aspects_transport')->nullable();
            $table->text('improvement_opportunities_transport')->nullable();
            $table->text('withdrawal_reason_transport')->nullable();
            
            // Campos adicionales para análisis
            $table->string('provider')->nullable(); // 'sapore', 'aldimark', 'metro_juniors'
            $table->unsignedBigInteger('uploaded_by');
            $table->timestamp('uploaded_at');
            $table->timestamps();
            
            $table->foreign('uploaded_by')->references('id')->on('users');
            $table->index(['period', 'survey_type']);
            $table->index(['student_grade', 'period']);
            $table->index(['provider', 'period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parent_student_surveys');
    }
};
