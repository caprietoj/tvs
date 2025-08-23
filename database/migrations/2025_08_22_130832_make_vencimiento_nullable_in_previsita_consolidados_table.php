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
        Schema::table('previsita_consolidados', function (Blueprint $table) {
            $table->date('vencimiento')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('previsita_consolidados', function (Blueprint $table) {
            $table->date('vencimiento')->nullable(false)->change();
        });
    }
};
