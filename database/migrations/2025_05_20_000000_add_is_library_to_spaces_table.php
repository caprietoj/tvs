<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsLibraryToSpacesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('spaces', 'is_library')) {
            Schema::table('spaces', function (Blueprint $table) {
                $table->boolean('is_library')->default(false)->after('active');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('spaces', 'is_library')) {
            Schema::table('spaces', function (Blueprint $table) {
                $table->dropColumn('is_library');
            });
        }
    }
}
