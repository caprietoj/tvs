<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EquipmentLoan extends Model
{
    protected $fillable = [
        'user_id',
        'equipment_id',
        'section',
        'grade',
        'loan_date',
        'start_time',
        'end_time',
        'units_requested',
        'status',
        'delivery_observations',
        'delivery_signature',
        'delivery_date',
        'return_observations',
        'return_signature',
        'return_date',
        'inventory_discounted',
        'inventory_returned',
        'auto_return',
        'period_id'
    ];

    protected $casts = [
        'loan_date' => 'date',
        'start_time' => 'string', // Cambio a string para manejar solo el tiempo (H:i)
        'end_time' => 'string',   // Cambio a string para manejar solo el tiempo (H:i)
        'delivery_date' => 'datetime',
        'return_date' => 'datetime',
        'inventory_discounted' => 'boolean',
        'inventory_returned' => 'boolean',
        'auto_return' => 'boolean'
    ];

    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired()
    {
        return Carbon::now()->gt($this->end_time);
    }

    public function getStatusLabelAttribute()
    {
        return [
            'pending' => 'Pendiente',
            'delivered' => 'Entregado',
            'returned' => 'Devuelto'
        ][$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute()
    {
        return [
            'pending' => 'warning',
            'delivered' => 'info',
            'returned' => 'success'
        ][$this->status] ?? 'secondary';
    }
}
