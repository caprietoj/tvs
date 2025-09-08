<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $table = 'equipment';
    
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

    /**
     * Obtiene los bloqueos asociados a este equipo
     */
    public function blocks()
    {
        return $this->hasMany(EquipmentBlock::class);
    }

    /**
     * Obtiene las unidades disponibles para una fecha y horario específicos
     * considerando bloqueos y préstamos activos
     * 
     * @param string $date Fecha (Y-m-d)
     * @param string $startTime Hora de inicio (HH:MM)
     * @param string $endTime Hora de fin (HH:MM)
     * @return int Unidades disponibles
     */
    public function getAvailableUnitsForDateTime(string $date, string $startTime, string $endTime): int
    {
        return EquipmentBlock::getAvailableUnits($this, $date, $startTime, $endTime);
    }

    /**
     * Verifica si hay suficientes unidades disponibles para una solicitud
     * 
     * @param string $date Fecha (Y-m-d)
     * @param string $startTime Hora de inicio (HH:MM)
     * @param string $endTime Hora de fin (HH:MM)
     * @param int $requestedUnits Unidades solicitadas
     * @return bool
     */
    public function hasAvailableUnits(string $date, string $startTime, string $endTime, int $requestedUnits): bool
    {
        return $this->getAvailableUnitsForDateTime($date, $startTime, $endTime) >= $requestedUnits;
    }

    public static function resetInventory()
    {
        self::query()->update([
            'available_units' => \DB::raw('total_units')
        ]);
    }
}
