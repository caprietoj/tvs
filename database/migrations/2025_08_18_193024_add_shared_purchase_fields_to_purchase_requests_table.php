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
            $table->boolean('is_shared')->default(false)->after('status');
            $table->string('shared_section')->nullable()->after('is_shared');
            $table->integer('my_percentage')->default(100)->after('shared_section');
            $table->integer('shared_percentage')->default(0)->after('my_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_requests', function (Blueprint $table) {
            $table->dropColumn(['is_shared', 'shared_section', 'my_percentage', 'shared_percentage']);
        });
    }
};
