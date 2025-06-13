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
        // Modificar la columna status para incluir 'approved'
        DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN status ENUM('pending', 'approved', 'sent_to_accounting', 'paid', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir la columna status a su estado original
        DB::statement("ALTER TABLE purchase_orders MODIFY COLUMN status ENUM('pending', 'sent_to_accounting', 'paid', 'cancelled') DEFAULT 'pending'");
    }
};
