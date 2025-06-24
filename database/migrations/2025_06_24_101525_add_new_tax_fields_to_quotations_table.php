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
            // Nuevos campos para IVA del 19% y 5%
            $table->boolean('includes_iva_19')->default(false)->after('iva_amount');
            $table->decimal('iva_19_amount', 12, 2)->default(0)->after('includes_iva_19');
            $table->boolean('includes_iva_5')->default(false)->after('iva_19_amount');
            $table->decimal('iva_5_amount', 12, 2)->default(0)->after('includes_iva_5');
            
            // Nuevos campos para Ipoconsumo del 8% y 4%
            $table->boolean('includes_ipoconsumo_8')->default(false)->after('iva_5_amount');
            $table->decimal('ipoconsumo_8_amount', 12, 2)->default(0)->after('includes_ipoconsumo_8');
            $table->boolean('includes_ipoconsumo_4')->default(false)->after('ipoconsumo_8_amount');
            $table->decimal('ipoconsumo_4_amount', 12, 2)->default(0)->after('includes_ipoconsumo_4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn([
                'includes_iva_19',
                'iva_19_amount',
                'includes_iva_5',
                'iva_5_amount',
                'includes_ipoconsumo_8',
                'ipoconsumo_8_amount',
                'includes_ipoconsumo_4',
                'ipoconsumo_4_amount'
            ]);
        });
    }
};
