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
            $table->timestamp('preapproval_sent_at')->nullable()->after('status');
            $table->unsignedBigInteger('preapproval_sent_by')->nullable()->after('preapproval_sent_at');
            
            $table->foreign('preapproval_sent_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropForeign(['preapproval_sent_by']);
            $table->dropColumn(['preapproval_sent_at', 'preapproval_sent_by']);
        });
    }
};
