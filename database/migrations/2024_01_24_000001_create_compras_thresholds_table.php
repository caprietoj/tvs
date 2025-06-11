<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('compras_thresholds', function (Blueprint $table) {
            $table->id();
            $table->string('area')->default('compras');
            $table->string('kpi_name');
            $table->decimal('value', 5, 2);
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('compras_thresholds');
    }
};
