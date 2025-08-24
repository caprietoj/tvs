<?php

namespace App\Imports;

use App\Models\PresupuestoItem;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class PresupuestoImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Esta clase se usa principalmente para la validación
        // El procesamiento real se hace en PresupuestoProcessorService
        return null;
    }

    public function rules(): array
    {
        return [
            // Reglas de validación básicas
            '*.fuente' => 'nullable|string|max:255',
            '*.documento' => 'nullable|string|max:255',
            '*.fecha' => 'nullable|date',
            '*.cuenta' => 'nullable|string|max:255',
            '*.descripcion' => 'nullable|string',
            '*.valor' => 'nullable|numeric',
            '*.valor_moneda' => 'nullable|numeric',
        ];
    }
}
