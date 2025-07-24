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
        Schema::table('proveedors', function (Blueprint $table) {
            // Remover la restricción única del campo email
            $table->dropUnique(['email']);
            // Hacer el campo nullable también
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proveedors', function (Blueprint $table) {
            // Restaurar la restricción única del campo email
            $table->string('email')->nullable(false)->change();
            $table->unique('email');
        });
    }
};
