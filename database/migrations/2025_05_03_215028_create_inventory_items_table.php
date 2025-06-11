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
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->string('producto');
            $table->integer('cantidad_sugerida');
            $table->integer('stock');
            $table->integer('sobre_stock')->virtualAs('stock - cantidad_sugerida');
            $table->integer('cantidad_comprar')->virtualAs('CASE WHEN stock < cantidad_sugerida THEN cantidad_sugerida - stock ELSE 0 END');
            $table->boolean('alerta_enviada')->default(false);
            $table->timestamp('ultima_alerta')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
