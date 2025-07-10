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
            $table->enum('tax_application_mode', ['global', 'per_item'])
                  ->default('global')
                  ->after('ipoconsumo_4_amount')
                  ->comment('Modo de aplicación de impuestos: global (a toda la cotización) o per_item (por item individual)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn('tax_application_mode');
        });
    }
};
