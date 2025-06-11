<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuotationApprovalFields extends Migration
{
    public function up()
    {
        Schema::table('quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('quotations', 'status')) {
                $table->string('status')->nullable();
            }
            if (!Schema::hasColumn('quotations', 'pre_approval_date')) {
                $table->timestamp('pre_approval_date')->nullable();
            }
            if (!Schema::hasColumn('quotations', 'pre_approval_comments')) {
                $table->text('pre_approval_comments')->nullable();
            }
            if (!Schema::hasColumn('quotations', 'pre_approved_by')) {
                $table->unsignedBigInteger('pre_approved_by')->nullable();
                $table->foreign('pre_approved_by')->references('id')->on('users');
            }
        });

        Schema::table('purchase_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_requests', 'status')) {
                $table->string('status')->default('pending');
            }
            if (!Schema::hasColumn('purchase_requests', 'pre_approved_quotation_id')) {
                $table->unsignedBigInteger('pre_approved_quotation_id')->nullable();
                $table->foreign('pre_approved_quotation_id')->references('id')->on('quotations');
            }
        });
    }

    public function down()
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['status', 'pre_approval_date', 'pre_approval_comments']);
            if (Schema::hasColumn('quotations', 'pre_approved_by')) {
                $table->dropForeign(['pre_approved_by']);
                $table->dropColumn('pre_approved_by');
            }
        });

        Schema::table('purchase_requests', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_requests', 'pre_approved_quotation_id')) {
                $table->dropForeign(['pre_approved_quotation_id']);
                $table->dropColumn('pre_approved_quotation_id');
            }
            if (Schema::hasColumn('purchase_requests', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
}
