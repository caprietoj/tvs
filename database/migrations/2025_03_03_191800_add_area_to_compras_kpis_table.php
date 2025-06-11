<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('compras_kpis', function (Blueprint $table) {
            $table->string('area')->after('threshold_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('compras_kpis', function (Blueprint $table) {
            $table->dropColumn('area');
        });
    }
};
