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
        if (!Schema::hasColumn('equipment_loans', 'inventory_returned')) {
            Schema::table('equipment_loans', function (Blueprint $table) {
                $table->boolean('inventory_returned')->default(false)->after('inventory_discounted');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('equipment_loans', 'inventory_returned')) {
            Schema::table('equipment_loans', function (Blueprint $table) {
                $table->dropColumn('inventory_returned');
            });
        }
    }
};
