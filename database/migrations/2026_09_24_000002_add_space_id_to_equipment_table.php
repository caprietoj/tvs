<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->foreignId('space_id')
                ->nullable()
                ->after('section')
                ->constrained('spaces')
                ->nullOnDelete();
        });

        // Vincular las salas de informática y la sala de computadores de biblioteca
        // con sus registros correspondientes en la tabla spaces.
        $map = [
            'sala_informatica' => '%Segundo Piso%',
            'sala_informatica_primer_piso' => '%Primer Piso%',
            'biblioteca_sala_computadores' => '%Sala de computadores%',
        ];

        foreach ($map as $section => $nameLike) {
            $spaceId = DB::table('spaces')->where('name', 'like', $nameLike)->value('id');

            if ($spaceId) {
                DB::table('equipment')
                    ->where('section', $section)
                    ->whereNull('space_id')
                    ->update(['space_id' => $spaceId]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('equipment', function (Blueprint $table) {
            $table->dropConstrainedForeignId('space_id');
        });
    }
};
