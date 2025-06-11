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
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique(); // Número de solicitud único
            $table->foreignId('user_id')->constrained(); // Usuario que crea la solicitud
            $table->enum('type', ['purchase', 'materials']); // Tipo de solicitud: compra o materiales/fotocopias
            
            // Campos para ambos tipos
            $table->date('request_date'); // Fecha de solicitud
            $table->string('requester'); // Solicitante
            $table->string('section_area'); // Sección/Área
            
            // Campos específicos para solicitud de compra
            $table->json('purchase_items')->nullable(); // Artículos de compra en formato JSON
            $table->text('purchase_justification')->nullable(); // Justificación de la compra
            $table->json('service_items')->nullable(); // Servicios en formato JSON
            $table->decimal('service_budget', 15, 2)->nullable(); // Presupuesto del servicio
            $table->string('service_budget_text')->nullable(); // Presupuesto en letras
            
            // Campos específicos para materiales/fotocopias
            $table->string('code')->nullable(); // Código
            $table->string('grade')->nullable(); // Grado
            $table->string('section')->nullable(); // Sección
            $table->date('delivery_date')->nullable(); // Fecha de entrega
            $table->json('copy_items')->nullable(); // Ítems de fotocopias en formato JSON
            $table->json('material_items')->nullable(); // Ítems de materiales en formato JSON
            
            // Campos de aprobación
            $table->string('status')->default('pending'); // Estado: pendiente, aprobado, rechazado
            $table->foreignId('approved_by')->nullable()->constrained('users'); // Usuario que aprueba
            $table->timestamp('approval_date')->nullable(); // Fecha de aprobación
            
            $table->timestamps();
            $table->softDeletes(); // Para eliminación suave
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
