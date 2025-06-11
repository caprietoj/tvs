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
        Schema::table('events', function (Blueprint $table) {
            // Cambiar los campos de type string a text para permitir textos más largos
            $table->text('aldimark_requirement')->nullable()->change();
            $table->text('maintenance_requirement')->nullable()->change();
            $table->text('general_services_requirement')->nullable()->change();
            $table->text('systems_requirement')->nullable()->change();
            $table->text('purchases_requirement')->nullable()->change();
            $table->text('communications_coverage')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Revertir cambios (aunque esto podría provocar truncamiento de datos)
            $table->string('aldimark_requirement')->nullable()->change();
            $table->string('maintenance_requirement')->nullable()->change();
            $table->string('general_services_requirement')->nullable()->change();
            $table->string('systems_requirement')->nullable()->change();
            $table->string('purchases_requirement')->nullable()->change();
            $table->string('communications_coverage')->nullable()->change();
        });
    }
};
