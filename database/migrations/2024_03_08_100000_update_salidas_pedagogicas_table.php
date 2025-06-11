<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSalidasPedagogicasTable extends Migration
{
    public function up()
    {
        // Only add new columns or modify existing ones if needed
        Schema::table('salidas_pedagogicas', function (Blueprint $table) {
            // Add any new columns here
            // Example:
            // $table->string('new_column')->after('existing_column')->nullable();
            
            // Or modify existing columns
            // Example:
            // $table->string('existing_column', 500)->change();
        });
    }

    public function down()
    {
        Schema::table('salidas_pedagogicas', function (Blueprint $table) {
            // Remove any added columns here
            // Example:
            // $table->dropColumn('new_column');
        });
    }
}
