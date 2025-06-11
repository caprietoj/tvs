<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cartera_recaudos', function (Blueprint $table) {
            $table->id();
            $table->string('mes');
            $table->decimal('valor_recaudado', 15, 2);
            $table->decimal('valor_facturado', 15, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cartera_recaudos');
    }
};
