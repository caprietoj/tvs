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
        Schema::create('loan_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            
            // Basic loan information
            $table->decimal('amount', 15, 2);
            $table->string('purpose')->default('Gastos personales');
            $table->integer('installments');
            $table->enum('installment_type', ['monthly', 'biweekly']);
            $table->decimal('installment_value', 15, 2);
            $table->date('deduction_start_date');
            
            // Applicant information
            $table->string('signature');
            $table->string('document_number');
            $table->string('full_name');
            $table->string('position');
            
            // Status
            $table->enum('status', ['pending', 'reviewed', 'approved', 'rejected'])->default('pending');
            
            // HR review information
            $table->decimal('current_salary', 15, 2)->nullable();
            $table->boolean('has_active_loans')->nullable();
            $table->decimal('current_loan_balance', 15, 2)->nullable();
            $table->boolean('has_advances')->nullable();
            $table->decimal('advances_amount', 15, 2)->nullable();
            $table->boolean('hr_approved')->nullable();
            $table->string('hr_signature')->nullable();
            $table->timestamp('review_date')->nullable();
            
            // Admin approval information
            $table->boolean('admin_approved')->nullable();
            $table->string('admin_signature')->nullable();
            $table->timestamp('decision_date')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_requests');
    }
};
