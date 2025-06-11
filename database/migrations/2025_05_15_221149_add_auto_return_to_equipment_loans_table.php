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
            if (!Schema::hasColumn('equipment_loans', 'auto_return')) {
                $table->boolean('auto_return')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_loans', function (Blueprint $table) {
            if (Schema::hasColumn('equipment_loans', 'auto_return')) {
                $table->dropColumn('auto_return');
            }
        });
    }
};
