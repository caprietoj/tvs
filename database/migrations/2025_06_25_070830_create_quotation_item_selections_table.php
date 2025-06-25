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
            $table->string('item_description'); // Descripción del producto/item
            $table->integer('item_index'); // Índice del item en la solicitud original
            $table->foreignId('selected_quotation_id')->constrained('quotations')->onDelete('cascade');
            $table->decimal('selected_price', 15, 2)->nullable(); // Precio del item seleccionado
            $table->text('selection_notes')->nullable(); // Notas sobre la selección
            $table->timestamps();
            
            // Índice único para evitar duplicados
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
