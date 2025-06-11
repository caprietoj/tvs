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
            $table->boolean('includes_iva')->default(false)->after('total_amount');
            $table->json('additional_items')->nullable()->after('observations');
            $table->decimal('subtotal', 12, 2)->nullable()->after('total_amount');
            $table->decimal('iva_amount', 12, 2)->nullable()->after('subtotal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('includes_iva');
            $table->dropColumn('additional_items');
            $table->dropColumn('subtotal');
            $table->dropColumn('iva_amount');
        });
    }
};
