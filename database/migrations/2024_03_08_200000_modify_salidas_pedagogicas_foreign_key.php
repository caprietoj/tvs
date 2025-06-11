<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifySalidasPedagogicasForeignKey extends Migration
{
    public function up()
    {
        Schema::table('salidas_pedagogicas', function (Blueprint $table) {
            // Drop the existing foreign key if it exists
            $table->dropForeign(['responsable_id']);

            // Add the foreign key with onDelete cascade
            $table->foreign('responsable_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('salidas_pedagogicas', function (Blueprint $table) {
            $table->dropForeign(['responsable_id']);
            
            // Restore original foreign key without cascade
            $table->foreign('responsable_id')
                  ->references('id')
                  ->on('users');
        });
    }
}
