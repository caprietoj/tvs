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
            $table->json('applied_taxes')->nullable()->after('budget');
            $table->decimal('subtotal_amount', 15, 2)->nullable()->after('applied_taxes');
            $table->decimal('tax_amount', 15, 2)->nullable()->after('subtotal_amount');
            $table->decimal('total_amount', 15, 2)->nullable()->after('tax_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn(['applied_taxes', 'subtotal_amount', 'tax_amount', 'total_amount']);
        });
    }
};
