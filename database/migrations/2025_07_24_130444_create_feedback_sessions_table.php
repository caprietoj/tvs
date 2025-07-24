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
        Schema::create('feedback_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('performance_evaluation_id');
            $table->unsignedBigInteger('supervisor_id');
            $table->unsignedBigInteger('employee_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->datetime('scheduled_datetime');
            $table->string('location')->nullable();
            $table->enum('status', ['programada', 'realizada', 'cancelada'])->default('programada');
            $table->string('google_event_id')->nullable();
            $table->text('meeting_notes')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('performance_evaluation_id')->references('id')->on('performance_evaluations')->onDelete('cascade');
            $table->foreign('supervisor_id')->references('id')->on('users');
            $table->foreign('employee_id')->references('id')->on('users');
            
            $table->index(['performance_evaluation_id', 'status']);
            $table->index('scheduled_datetime');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_sessions');
    }
};
