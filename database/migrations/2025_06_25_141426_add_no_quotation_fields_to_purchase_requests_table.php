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
        Schema::table('purchase_requests', function (Blueprint $table) {
            // Campos para servicios sin cotización
            $table->enum('service_type', ['regular', 'no_quotation'])->nullable()->after('service_justification');
            $table->string('provider_name')->nullable()->after('service_type');
            $table->string('provider_nit')->nullable()->after('provider_name');
            $table->string('provider_contact')->nullable()->after('provider_nit');
            $table->string('provider_email')->nullable()->after('provider_contact');
            $table->text('no_quotation_reason')->nullable()->after('provider_email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn([
                'service_type',
                'provider_name',
                'provider_nit',
                'provider_contact',
                'provider_email',
                'no_quotation_reason'
            ]);
        });
    }
};
