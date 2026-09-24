<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_block_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('equipment_block_id')
                ->constrained('equipment_blocks')
                ->cascadeOnDelete();
            $table->date('override_date');
            $table->string('new_reason')->nullable();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['equipment_block_id', 'override_date'], 'block_override_unique');
            $table->index('override_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_block_overrides');
    }
};
