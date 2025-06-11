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
        Schema::table('purchase_requests', function (Blueprint $table) {
            // Campo para especificar cuántas cotizaciones se requieren (por defecto 3)
            $table->integer('required_quotations')->default(3)->after('rejection_reason');
            
            // Campo para determinar si se puede proceder con notificaciones antes de alcanzar el número requerido
            $table->boolean('can_proceed_early')->default(false)->after('required_quotations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn(['required_quotations', 'can_proceed_early']);
        });
    }
};
