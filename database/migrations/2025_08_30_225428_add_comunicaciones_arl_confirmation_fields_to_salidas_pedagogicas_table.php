<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('salidas_pedagogicas', function (Blueprint $table) {
            $table->boolean('comunicaciones_confirmada')->default(false);
            $table->unsignedBigInteger('comunicaciones_confirmado_por')->nullable();
            $table->timestamp('comunicaciones_confirmado_at')->nullable();

            $table->boolean('arl_confirmado')->default(false);
            $table->unsignedBigInteger('arl_confirmado_por')->nullable();
            $table->timestamp('arl_confirmado_at')->nullable();

            $table->foreign('comunicaciones_confirmado_por')->references('id')->on('users');
            $table->foreign('arl_confirmado_por')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('salidas_pedagogicas', function (Blueprint $table) {
            $table->dropForeign(['comunicaciones_confirmado_por']);
            $table->dropForeign(['arl_confirmado_por']);
            
            $table->dropColumn([
                'comunicaciones_confirmada',
                'comunicaciones_confirmado_por',
                'comunicaciones_confirmado_at',
                'arl_confirmado',
                'arl_confirmado_por',
                'arl_confirmado_at'
            ]);
        });
    }
};
