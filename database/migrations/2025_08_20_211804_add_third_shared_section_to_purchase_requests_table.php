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
            // Agregar campos para la tercera sección compartida
            $table->string('third_shared_section')->nullable()->after('shared_percentage');
            $table->integer('third_shared_percentage')->default(0)->after('third_shared_section');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn(['third_shared_section', 'third_shared_percentage']);
        });
    }
};
