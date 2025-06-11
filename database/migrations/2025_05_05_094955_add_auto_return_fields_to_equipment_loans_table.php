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
        Schema::table('equipment_loans', function (Blueprint $table) {
            $table->boolean('auto_return')->default(false)->after('inventory_returned');
            $table->string('period_id')->nullable()->after('auto_return');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_loans', function (Blueprint $table) {
            $table->dropColumn('auto_return');
            $table->dropColumn('period_id');
        });
    }
};
