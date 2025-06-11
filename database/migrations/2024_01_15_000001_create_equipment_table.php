<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'laptop' o 'ipad'
            $table->string('section'); // 'bachillerato' o 'preescolar_primaria'
            $table->integer('total_units');
            $table->integer('available_units');
            $table->timestamps();
        });

        Schema::create('equipment_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('equipment_id')->constrained();
            $table->string('section');
            $table->string('grade');
            $table->date('loan_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->integer('units_requested');
            $table->enum('status', ['pending', 'active', 'completed'])->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('equipment_loans');
        Schema::dropIfExists('equipment');
    }
};
