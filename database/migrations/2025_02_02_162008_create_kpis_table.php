<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKpisTable extends Migration
{
    public function up()
    {
        Schema::create('kpis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('threshold_id')->nullable()->constrained()->onDelete('set null');
            $table->string('area')->nullable();
            $table->string('name');
            $table->string('methodology');
            $table->string('frequency');
            $table->decimal('percentage', 5, 2);
            $table->text('analysis')->nullable(); // Removido el after() de aquí
            $table->date('measurement_date');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('kpis');
    }
}