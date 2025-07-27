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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->date('order_date')->nullable()->after('order_number');
            $table->decimal('tax_amount', 15, 2)->nullable()->after('subtotal');
            $table->json('pdf_custom_data')->nullable()->after('observations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['order_date', 'tax_amount', 'pdf_custom_data']);
        });
    }
};
