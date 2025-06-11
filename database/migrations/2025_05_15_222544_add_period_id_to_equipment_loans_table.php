<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPeriodIdToEquipmentLoansTable extends Migration
{
    public function up()
    {
        Schema::table('equipment_loans', function (Blueprint $table) {
            if (!Schema::hasColumn('equipment_loans', 'period_id')) {
                $table->string('period_id')->nullable(); // o puedes usar ->default('period_0') si quieres un valor por defecto
            }
        });
    }

    public function down()
    {
        Schema::table('equipment_loans', function (Blueprint $table) {
            if (Schema::hasColumn('equipment_loans', 'period_id')) {
                $table->dropColumn('period_id');
            }
        });
    }
}