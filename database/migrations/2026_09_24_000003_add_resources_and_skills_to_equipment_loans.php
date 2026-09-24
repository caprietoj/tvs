<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment_loans', function (Blueprint $table) {
            $table->boolean('uses_electronic_resources')->default(false)->after('period_id');
            $table->boolean('uses_skills')->default(false)->after('uses_electronic_resources');
            $table->text('selected_electronic_resources')->nullable()->after('uses_skills');
        });

        Schema::create('equipment_loan_skill', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_loan_id')->constrained('equipment_loans')->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['equipment_loan_id', 'skill_id'], 'loan_skill_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_loan_skill');

        Schema::table('equipment_loans', function (Blueprint $table) {
            $table->dropColumn([
                'uses_electronic_resources',
                'uses_skills',
                'selected_electronic_resources',
            ]);
        });
    }
};
