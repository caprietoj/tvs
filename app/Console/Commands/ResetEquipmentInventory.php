<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Equipment;
use App\Models\EquipmentLoan;
use Carbon\Carbon;

class ResetEquipmentInventory extends Command
{
    protected $signature = 'equipment:reset-inventory';
    protected $description = 'Reset equipment inventory when loans expire';

    public function handle()
    {
        $now = Carbon::now();
        
        // Get expired loans that haven't been processed
        $expiredLoans = EquipmentLoan::where('status', 'active')
            ->where('end_time', '<=', $now)
            ->get();

        foreach ($expiredLoans as $loan) {
            // Get the equipment
            $equipment = Equipment::find($loan->equipment_id);
            
            if ($equipment) {
                // Reset available quantity
                $equipment->available_quantity += $loan->quantity;
                $equipment->save();
            }
            
            // Mark loan as completed
            $loan->status = 'completed';
            $loan->actual_return_time = $now;
            $loan->save();
            
            // Log the automatic return
            \Log::info('Automatic equipment return processed', [
                'loan_id' => $loan->id,
                'equipment_id' => $loan->equipment_id,
                'quantity' => $loan->quantity,
                'return_time' => $now
            ]);
        }
    }
}
