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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_request_id')->constrained('purchase_requests')->onDelete('cascade');
            $table->foreignId('provider_id')->constrained('proveedors')->onDelete('restrict');
            $table->string('order_number');
            $table->decimal('total_amount', 12, 2);
            $table->string('payment_terms');
            $table->date('delivery_date');
            $table->string('file_path');
            $table->text('observations')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->enum('status', ['pending', 'sent_to_accounting', 'paid', 'cancelled'])->default('pending');
            $table->timestamp('sent_to_accounting_at')->nullable();
            $table->foreignId('sent_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->date('payment_date')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->text('cancellation_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
