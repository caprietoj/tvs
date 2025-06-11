<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWeeklyBiometricTables extends Migration
{
    public function up()
    {
        Schema::create('weekly_biometric_records', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->string('full_name');
            $table->date('record_date');
            $table->time('entry_time')->nullable();
            $table->time('exit_time')->nullable();
            $table->string('department');
            $table->text('raw_marks');
            $table->timestamps();
            
            $table->index(['employee_id', 'record_date']);
        });

        Schema::create('weekly_biometric_stats', function (Blueprint $table) {
            $table->id();
            $table->date('week_start');
            $table->date('week_end');
            $table->string('department');
            $table->integer('total_employees');
            $table->integer('present_count');
            $table->integer('absent_count');
            $table->integer('late_count');
            $table->decimal('avg_entry_time', 4, 2);
            $table->decimal('avg_exit_time', 4, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('weekly_biometric_stats');
        Schema::dropIfExists('weekly_biometric_records');
    }
}
