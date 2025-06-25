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
        Schema::create('quotation_item_selections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('quotation_id')->constrained()->onDelete('cascade');
            $table->integer('item_index'); // Índice del item en el array purchase_items
            $table->string('item_description'); // Descripción del producto
            $table->integer('quantity'); // Cantidad
            $table->decimal('unit_price', 10, 2); // Precio unitario
            $table->decimal('total_price', 10, 2); // Precio total del item
            $table->text('justification')->nullable(); // Justificación de la selección
            $table->foreignId('selected_by')->constrained('users'); // Usuario que hizo la selección
            $table->timestamp('selected_at')->useCurrent();
            $table->timestamps();
            
            // Evitar duplicación: un item de una solicitud solo puede tener una selección activa
            $table->unique(['purchase_request_id', 'item_index'], 'unique_item_selection');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_item_selections');
    }
};
