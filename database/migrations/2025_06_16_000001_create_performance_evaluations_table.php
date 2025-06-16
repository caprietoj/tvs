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
        Schema::create('performance_evaluations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('evaluator_id')->nullable();
            $table->string('evaluation_type'); // 'periodo_prueba' o 'periodica'
            $table->string('status')->default('draft'); // draft, self_completed, supervisor_review, completed
            $table->date('evaluation_period_start');
            $table->date('evaluation_period_end');
            
            // Sección I: Objetivos del Cargo (30%)
            $table->json('objectives_section')->nullable(); // Fortalecimiento, Vocación, Conocimiento
            $table->decimal('objectives_self_score', 5, 2)->nullable();
            $table->decimal('objectives_supervisor_score', 5, 2)->nullable();
            
            // Sección II: Competencias Organizacionales (70%)
            $table->json('organizational_competencies')->nullable();
            $table->decimal('competencies_self_score', 5, 2)->nullable();
            $table->decimal('competencies_supervisor_score', 5, 2)->nullable();
            
            // Sección III: Competencias Técnicas
            $table->json('technical_competencies')->nullable();
            
            // Sección IV: Seguridad y Salud en el Trabajo
            $table->json('safety_health_section')->nullable();
            
            // Sección V: Observaciones
            $table->text('self_observations')->nullable();
            $table->text('supervisor_observations')->nullable();
            
            // Calificaciones finales
            $table->decimal('final_self_score', 5, 2)->nullable();
            $table->decimal('final_supervisor_score', 5, 2)->nullable();
            $table->decimal('final_average_score', 5, 2)->nullable();
            $table->string('performance_level')->nullable(); // Bajo, Básico, Alto, Superior
            
            // Fechas importantes
            $table->timestamp('self_evaluation_completed_at')->nullable();
            $table->timestamp('supervisor_evaluation_completed_at')->nullable();
            
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('evaluator_id')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['user_id', 'evaluation_period_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_evaluations');
    }
};
