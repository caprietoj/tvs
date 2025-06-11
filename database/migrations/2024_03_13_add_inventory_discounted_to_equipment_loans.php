<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('equipment_loans', function (Blueprint $table) {
            $table->boolean('inventory_discounted')->default(false);
        });
    }

    public function down()
    {
        Schema::table('equipment_loans', function (Blueprint $table) {
            $table->dropColumn('inventory_discounted');
        });
    }
};
