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
        Schema::table('parent_student_surveys', function (Blueprint $table) {
            $table->string('student_name')->nullable()->after('timestamp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('parent_student_surveys', function (Blueprint $table) {
            $table->dropColumn('student_name');
        });
    }
};
