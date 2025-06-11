<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Configuration;
use Illuminate\Support\Facades\Cache;

class ConfigurationController extends Controller
{
    protected $modules = [
        'equipment_loan' => 'Préstamo de Equipos',
        'helpdesk' => 'Help Desk',
        'maintenance' => 'Mantenimiento',
        'rrhh_requests' => 'Solicitudes RRHH',
        'events' => [
            'name' => 'Eventos',
            'areas' => [
                'sistemas' => 'Sistemas',
                'compras' => 'Compras',
                'mantenimiento' => 'Mantenimiento',
                'servicios_generales' => 'Servicios Generales',
                'comunicaciones' => 'Comunicaciones',
                'aldimark' => 'Aldimark',
                'metro_junior' => 'Metro Junior'
            ]
        ]
    ];

    public function __construct()
    {
        $this->middleware(['auth', 'can:manage.configuration']);
    }

    public function index()
    {
        $configurations = [];
        foreach ($this->modules as $key => $value) {
            if ($key === 'events') {
                $configurations[$key] = [
                    'name' => $value['name'],
                    'areas' => []
                ];
                foreach ($value['areas'] as $areaKey => $areaName) {
                    $config = Configuration::where('key', "events_{$areaKey}_emails")->first();
                    $configurations[$key]['areas'][$areaKey] = [
                        'name' => $areaName,
                        'emails' => $config ? explode(',', $config->value) : []
                    ];
                }
            } else {
                $config = Configuration::where('key', $key . '_emails')->first();
                $configurations[$key] = [
                    'name' => $value,
                    'emails' => $config ? explode(',', $config->value) : []
                ];
            }
        }
        
        return view('admin.configuration.index', compact('configurations'));
    }

    public function updateEmails(Request $request)
    {
        // Validación más flexible
        $request->validate([
            'emails.*.*' => 'nullable|email',
        ], [
            'emails.*.*.email' => 'Cada entrada debe ser un correo electrónico válido'
        ]);

        // Manejar módulos regulares
        foreach ($this->modules as $key => $value) {
            if ($key === 'events') {
                // Manejar áreas de eventos
                foreach ($value['areas'] as $areaKey => $areaName) {
                    $emails = isset($request->emails["events_{$areaKey}"]) 
                        ? array_filter($request->emails["events_{$areaKey}"]) 
                        : [];
                    
                    if (!empty($emails)) {
                        Configuration::updateOrCreate(
                            ['key' => "events_{$areaKey}_emails"],
                            ['value' => implode(',', $emails)]
                        );

                        Cache::forget("events_{$areaKey}_emails");
                    }
                }
            } else {
                // Manejar otros módulos
                if (isset($request->emails[$key])) {
                    $emails = array_filter($request->emails[$key]);
                    
                    Configuration::updateOrCreate(
                        ['key' => "{$key}_emails"],
                        ['value' => implode(',', $emails)]
                    );

                    Cache::forget("{$key}_emails");
                }
            }
        }

        return redirect()->route('admin.configuration.index')
            ->with('success', 'Configuración de correos actualizada correctamente');
    }
}
