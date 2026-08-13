<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create("sst_documents", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("file_path");
            $table->foreignId("user_id")->constrained();
            $table->string("type")->default("file");
            $table->string("original_filename")->nullable();
            $table->integer("file_count")->default(1);
            $table->bigInteger("total_size")->default(0);
            $table->json("folder_structure")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists("sst_documents");
    }
};
