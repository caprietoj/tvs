<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment_block_overrides', function (Blueprint $table) {
            $table->foreignId('equipment_loan_id')
                ->nullable()
                ->after('assigned_user_id')
                ->constrained('equipment_loans')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('equipment_block_overrides', function (Blueprint $table) {
            $table->dropConstrainedForeignId('equipment_loan_id');
        });
    }
};
