<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('budget_executions', function (Blueprint $table) {
            $table->id();
            $table->string('department');
            $table->decimal('budget_amount', 15, 2);
            $table->decimal('executed_amount', 15, 2);
            $table->decimal('execution_percentage', 5, 2)->nullable();
            $table->text('analysis')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('budget_executions');
    }
};
