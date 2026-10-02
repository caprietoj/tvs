<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment_loans', function (Blueprint $table) {
            $table->foreignId('space_reservation_id')
                ->nullable()
                ->after('equipment_id')
                ->constrained('space_reservations')
                ->nullOnDelete();

            $table->unique('space_reservation_id');
        });

        // Backfill: crear préstamos de equipo para las reservas existentes de espacios
        // que tienen un equipo asociado (salas de informática y biblioteca).
        $reservations = DB::table('space_reservations')
            ->whereIn('status', ['pending', 'approved'])
            ->get();

        foreach ($reservations as $reservation) {
            $equipment = DB::table('equipment')
                ->where('space_id', $reservation->space_id)
                ->orderBy('id')
                ->first();

            if (!$equipment) {
                continue;
            }

            $alreadyExists = DB::table('equipment_loans')
                ->where('space_reservation_id', $reservation->id)
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            $spaceName = DB::table('spaces')->where('id', $reservation->space_id)->value('name');

            DB::table('equipment_loans')->insert([
                'user_id' => $reservation->user_id,
                'equipment_id' => $equipment->id,
                'space_reservation_id' => $reservation->id,
                'section' => $equipment->section,
                'grade' => $spaceName ?: $equipment->section,
                'loan_date' => $reservation->date,
                'start_time' => $reservation->start_time,
                'end_time' => $reservation->end_time,
                'units_requested' => $equipment->total_units,
                'status' => 'pending',
                'auto_return' => 1,
                'uses_electronic_resources' => !empty($reservation->selected_electronic_resources) ? 1 : 0,
                'selected_electronic_resources' => $reservation->selected_electronic_resources,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('equipment_loans', function (Blueprint $table) {
            $table->dropUnique(['space_reservation_id']);
            $table->dropConstrainedForeignId('space_reservation_id');
        });
    }
};
