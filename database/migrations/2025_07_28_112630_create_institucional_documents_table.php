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
        Schema::create('institucional_documents', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('file_path');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['file', 'folder'])->default('file');
            $table->string('original_filename')->nullable();
            $table->integer('file_count')->default(1);
            $table->bigInteger('total_size')->default(0);
            $table->json('folder_structure')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institucional_documents');
    }
};
