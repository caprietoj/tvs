<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        $exists = DB::table('equipment')
            ->where('type', 'imac')
            ->where('section', 'biblioteca_sala_computadores')
            ->exists();

        if (!$exists) {
            DB::table('equipment')->insert([
                'type' => 'imac',
                'section' => 'biblioteca_sala_computadores',
                'total_units' => 12,
                'available_units' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down()
    {
        DB::table('equipment')
            ->where('type', 'imac')
            ->where('section', 'biblioteca_sala_computadores')
            ->delete();
    }
};
