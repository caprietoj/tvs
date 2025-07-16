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
        Schema::table('sistemas_documents', function (Blueprint $table) {
            $table->enum('type', ['file', 'folder'])->default('file')->after('file_path');
            $table->string('original_filename')->nullable()->after('type');
            $table->integer('file_count')->default(1)->after('original_filename');
            $table->bigInteger('total_size')->default(0)->after('file_count');
            $table->text('folder_structure')->nullable()->after('total_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sistemas_documents', function (Blueprint $table) {
            $table->dropColumn(['type', 'original_filename', 'file_count', 'total_size', 'folder_structure']);
        });
    }
};
