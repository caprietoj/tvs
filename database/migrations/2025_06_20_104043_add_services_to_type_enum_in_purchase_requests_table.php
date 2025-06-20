<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modificar el ENUM para incluir 'services'
        DB::statement("ALTER TABLE purchase_requests MODIFY COLUMN type ENUM('purchase', 'materials', 'services') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir el ENUM a su estado original
        DB::statement("ALTER TABLE purchase_requests MODIFY COLUMN type ENUM('purchase', 'materials') NOT NULL");
    }
};
