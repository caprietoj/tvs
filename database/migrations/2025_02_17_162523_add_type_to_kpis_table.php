<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToKpisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kpis', function (Blueprint $table) {
            $table->enum('type', ['measurement', 'informative'])
                  ->default('measurement')
                  ->after('name');
        });

        // Actualizar los registros existentes a 'measurement' por defecto
        DB::table('kpis')->update(['type' => 'measurement']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kpis', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}