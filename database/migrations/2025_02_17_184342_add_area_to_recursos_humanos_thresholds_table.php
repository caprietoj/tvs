<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAreaToRecursosHumanosThresholdsTable extends Migration
{
    public function up()
    {
        Schema::table('recursos_humanos_thresholds', function (Blueprint $table) {
            $table->string('area')->default('rrhh')->after('value');
        });
    }

    public function down()
    {
        Schema::table('recursos_humanos_thresholds', function (Blueprint $table) {
            $table->dropColumn('area');
        });
    }
}