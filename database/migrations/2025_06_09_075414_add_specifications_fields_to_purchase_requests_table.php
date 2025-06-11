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
            // Campos de especificaciones para fotocopias
            $table->string('paper_size')->nullable()->after('copy_items');
            $table->string('paper_type')->nullable()->after('paper_size');
            $table->string('paper_color')->nullable()->after('paper_type');
            $table->boolean('requires_binding')->default(false)->after('paper_color');
            $table->boolean('requires_lamination')->default(false)->after('requires_binding');
            $table->boolean('requires_cutting')->default(false)->after('requires_lamination');
            $table->text('special_details')->nullable()->after('requires_cutting');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            // Eliminar campos de especificaciones en el rollback
            $table->dropColumn([
                'paper_size',
                'paper_type',
                'paper_color',
                'requires_binding',
                'requires_lamination',
                'requires_cutting',
                'special_details'
            ]);
        });
    }
};
