<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'user_id',
        'tipo_movimiento',
        'cantidad',
        'detalle',
        'solicitante'
    ];

    /**
     * Obtener el ítem de inventario asociado con este movimiento
     */
    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * Obtener el usuario que registró el movimiento
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}