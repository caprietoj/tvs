<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('budget_executions', function (Blueprint $table) {
            $table->string('month')->after('department');
        });
    }

    public function down()
    {
        Schema::table('budget_executions', function (Blueprint $table) {
            $table->dropColumn('month');
        });
    }
};
