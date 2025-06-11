<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToRecursosHumanosKpisTable extends Migration
{
    public function up()
    {
        Schema::table('recursos_humanos_kpis', function (Blueprint $table) {
            $table->enum('type', ['measurement', 'informative'])->default('measurement')->after('name');
        });
    }

    public function down()
    {
        Schema::table('recursos_humanos_kpis', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}