<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeAndAreaToSistemasKpisTable extends Migration
{
    public function up()
    {
        Schema::table('sistemas_kpis', function (Blueprint $table) {
            if (!Schema::hasColumn('sistemas_kpis', 'type')) {
                $table->enum('type', ['measurement', 'informative'])
                      ->default('measurement')
                      ->after('name');
            }
            
            if (!Schema::hasColumn('sistemas_kpis', 'area')) {
                $table->string('area')
                      ->default('sistemas')
                      ->after('percentage');
            }
        });
    }

    public function down()
    {
        Schema::table('sistemas_kpis', function (Blueprint $table) {
            if (Schema::hasColumn('sistemas_kpis', 'type')) {
                $table->dropColumn('type');
            }
            
            if (Schema::hasColumn('sistemas_kpis', 'area')) {
                $table->dropColumn('area');
            }
        });
    }
}