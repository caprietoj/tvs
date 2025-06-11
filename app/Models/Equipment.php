<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $fillable = [
        'type',
        'section',
        'total_units',
        'available_units'
    ];

    public function loans()
    {
        return $this->hasMany(EquipmentLoan::class);
    }

    public static function resetInventory()
    {
        self::query()->update([
            'available_units' => \DB::raw('total_units')
        ]);
    }
}
