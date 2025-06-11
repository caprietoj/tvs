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
        Schema::table('space_blocks', function (Blueprint $table) {
            $table->boolean('is_weekday_block')->default(false)->after('reason');
            $table->boolean('monday')->default(false)->after('is_weekday_block');
            $table->boolean('tuesday')->default(false)->after('monday');
            $table->boolean('wednesday')->default(false)->after('tuesday');
            $table->boolean('thursday')->default(false)->after('wednesday');
            $table->boolean('friday')->default(false)->after('thursday');
            $table->boolean('saturday')->default(false)->after('friday');
            $table->boolean('sunday')->default(false)->after('saturday');
            $table->time('start_time')->nullable()->after('sunday');
            $table->time('end_time')->nullable()->after('start_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('space_blocks', function (Blueprint $table) {
            $table->dropColumn('is_weekday_block');
            $table->dropColumn('monday');
            $table->dropColumn('tuesday');
            $table->dropColumn('wednesday');
            $table->dropColumn('thursday');
            $table->dropColumn('friday');
            $table->dropColumn('saturday');
            $table->dropColumn('sunday');
            $table->dropColumn('start_time');
            $table->dropColumn('end_time');
        });
    }
};
