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
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('delivery_time')->nullable()->after('total_amount');
            $table->string('payment_method')->nullable()->after('delivery_time');
            $table->string('validity')->nullable()->after('payment_method');
            $table->string('warranty')->nullable()->after('validity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn('delivery_time');
            $table->dropColumn('payment_method');
            $table->dropColumn('validity');
            $table->dropColumn('warranty');
        });
    }
};
