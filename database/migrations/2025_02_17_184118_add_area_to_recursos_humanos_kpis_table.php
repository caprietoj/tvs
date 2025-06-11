<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAreaToRecursosHumanosKpisTable extends Migration
{
    public function up()
    {
        Schema::table('recursos_humanos_kpis', function (Blueprint $table) {
            $table->string('area')->default('rrhh')->after('percentage');
        });
    }

    public function down()
    {
        Schema::table('recursos_humanos_kpis', function (Blueprint $table) {
            $table->dropColumn('area');
        });
    }
}