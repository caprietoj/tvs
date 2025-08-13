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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->boolean('is_viewed')->default(false)->after('status');
            $table->unsignedBigInteger('viewed_by')->nullable()->after('is_viewed');
            $table->timestamp('viewed_at')->nullable()->after('viewed_by');
            
            $table->foreign('viewed_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['viewed_by']);
            $table->dropColumn(['is_viewed', 'viewed_by', 'viewed_at']);
        });
    }
};
