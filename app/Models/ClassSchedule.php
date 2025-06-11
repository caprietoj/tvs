<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassSchedule extends Model
{
    use HasFactory;

    protected $fillable = ['section', 'day_of_week', 'start_time', 'end_time', 'is_break', 'break_type'];
    
    /**
     * Get class periods for a specific section and day of week
     * 
     * @param string $section preescolar_primaria or bachillerato
     * @param string $dayType regular or friday
     * @param string $subSection optional, preescolar or primaria
     * @return array
     */
    public static function getClassPeriods($section, $dayType = 'regular', $subSection = null)
    {
        // Horarios de clase para preescolar
        $preescolarRegular = [
            ['8:00', '8:45'],
            ['9:15', '10:00'],
            ['10:00', '10:45'],
            ['10:45', '11:30'],
            ['12:15', '13:00'],
            ['13:00', '13:45'],
            ['13:45', '14:40'],
        ];
        
        $preescolarBreaks = [
            ['8:45', '9:15', 'SNACK PREESCOLAR'],
            ['11:30', '12:15', 'LUNCH PREESCOLAR'],
        ];
        
        // Horario para primaria
        $primariaRegular = [
            ['8:00', '8:45'],
            ['8:45', '9:30'],
            ['10:00', '10:45'],
            ['10:45', '11:30'],
            ['11:30', '12:15'],
            ['13:00', '13:45'],
            ['13:45', '14:30'],
        ];
        
        $primariaBreaks = [
            ['9:30', '10:00', 'SNACK PRIMARIA'],
            ['12:15', '13:00', 'LUNCH PRIMARIA'],
        ];
        
        // Horario para bachillerato
        $bachilleratoRegular = [
            ['7:30', '8:20'],
            ['8:20', '9:10'],
            ['9:10', '10:00'],
            ['10:30', '11:20'],
            ['11:20', '12:10'],
            ['12:10', '13:15'],
            ['13:55', '14:40'],
        ];
        
        $bachilleratoRegularBreaks = [
            ['10:00', '10:30', 'SNACK'],
            ['13:15', '13:55', 'ALMUERZO'],
        ];
        
        // Horario para bachillerato los viernes
        $bachilleratoFriday = [
            ['7:30', '8:15'],
            ['8:15', '9:00'],
            ['9:00', '9:45'],
            ['9:45', '10:30'],
            ['11:00', '11:45'],
            ['11:45', '12:30'],
            ['12:30', '13:15'],
        ];
        
        $bachilleratoFridayBreaks = [
            ['10:30', '11:00', 'SNACK'],
            ['13:15', '13:55', 'ALMUERZO'],
        ];
        
        // Devolver el horario correspondiente
        if ($section === 'bachillerato') {
            if ($dayType === 'friday') {
                return [
                    'periods' => $bachilleratoFriday,
                    'breaks' => $bachilleratoFridayBreaks
                ];
            }
            return [
                'periods' => $bachilleratoRegular,
                'breaks' => $bachilleratoRegularBreaks
            ];
        } else {
            if ($subSection === 'preescolar') {
                return [
                    'periods' => $preescolarRegular,
                    'breaks' => $preescolarBreaks
                ];
            } else {
                // Por defecto asumimos primaria
                return [
                    'periods' => $primariaRegular,
                    'breaks' => $primariaBreaks,
                    
                ];
            }
        }
    }
}