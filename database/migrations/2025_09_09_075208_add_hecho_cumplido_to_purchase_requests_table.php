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
            $table->boolean('hecho_cumplido')->default(false)->after('delivery_notes');
            $table->timestamp('hecho_cumplido_at')->nullable()->after('hecho_cumplido');
            $table->unsignedBigInteger('hecho_cumplido_by')->nullable()->after('hecho_cumplido_at');
            
            $table->foreign('hecho_cumplido_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropForeign(['hecho_cumplido_by']);
            $table->dropColumn(['hecho_cumplido', 'hecho_cumplido_at', 'hecho_cumplido_by']);
        });
    }
};
