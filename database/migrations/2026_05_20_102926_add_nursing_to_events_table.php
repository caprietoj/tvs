<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('nursing_required')->default(false)->after('communications_confirmed');
            $table->text('nursing_requirement')->nullable()->after('nursing_required');
            $table->text('nursing_observations')->nullable()->after('nursing_requirement');
            $table->boolean('nursing_confirmed')->default(false)->after('nursing_observations');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'nursing_required',
                'nursing_requirement',
                'nursing_observations',
                'nursing_confirmed',
            ]);
        });
    }
};
