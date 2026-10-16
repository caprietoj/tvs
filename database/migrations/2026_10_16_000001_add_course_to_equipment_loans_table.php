<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCourseToEquipmentLoansTable extends Migration
{
    public function up()
    {
        Schema::table('equipment_loans', function (Blueprint $table) {
            if (!Schema::hasColumn('equipment_loans', 'course')) {
                $table->string('course')->nullable()->after('grade');
            }
        });
    }

    public function down()
    {
        Schema::table('equipment_loans', function (Blueprint $table) {
            if (Schema::hasColumn('equipment_loans', 'course')) {
                $table->dropColumn('course');
            }
        });
    }
}
