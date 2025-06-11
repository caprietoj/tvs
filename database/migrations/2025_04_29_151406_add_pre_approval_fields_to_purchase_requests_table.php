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
            $table->unsignedBigInteger('selected_quotation_id')->nullable();
            $table->text('pre_approval_comments')->nullable();
            $table->unsignedBigInteger('pre_approved_by')->nullable();
            $table->timestamp('pre_approved_at')->nullable();
            
            // Agregar restricciones de clave foránea
            $table->foreign('selected_quotation_id')->references('id')->on('quotations')->onDelete('set null');
            $table->foreign('pre_approved_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            // Eliminar las restricciones de clave foránea
            $table->dropForeign(['selected_quotation_id']);
            $table->dropForeign(['pre_approved_by']);
            
            // Eliminar las columnas
            $table->dropColumn('selected_quotation_id');
            $table->dropColumn('pre_approval_comments');
            $table->dropColumn('pre_approved_by');
            $table->dropColumn('pre_approved_at');
        });
    }
};
