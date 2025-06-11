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
        Schema::table('loan_requests', function (Blueprint $table) {
            $table->string('department')->nullable()->after('position');
            $table->string('phone')->nullable()->after('department');
            $table->string('email')->nullable()->after('phone');
            $table->integer('employment_years')->nullable()->after('email');
            $table->string('contract_type')->nullable()->after('employment_years');
            $table->string('bank_name')->nullable()->after('contract_type');
            $table->string('account_type')->nullable()->after('bank_name');
            $table->string('account_number')->nullable()->after('account_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_requests', function (Blueprint $table) {
            $table->dropColumn([
                'department',
                'phone',
                'email',
                'employment_years',
                'contract_type',
                'bank_name',
                'account_type',
                'account_number'
            ]);
        });
    }
};
